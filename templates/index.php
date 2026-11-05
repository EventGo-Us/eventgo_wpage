<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../config.php';

$entries = scandir(__DIR__);
if ($entries === false) {
    http_response_code(500);
    exit('No se pudieron listar los templates.');
}

$templates = array();
foreach ($entries as $entry) {
    if ($entry === '.' || $entry === '..') {
        continue;
    }

    $path = __DIR__.'/'.$entry;
    if (is_dir($path) && is_file($path.'/head.php') && is_file($path.'/css/template.css')) {
        $templates[] = $entry;
    }
}
sort($templates, SORT_NATURAL | SORT_FLAG_CASE);

if (!isset($_SESSION['template_selector_csrf'])) {
    $_SESSION['template_selector_csrf'] = bin2hex(random_bytes(32));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
        ? $_POST['csrf_token']
        : '';
    $selectedTemplate = isset($_POST['template']) && is_string($_POST['template'])
        ? $_POST['template']
        : '';

    if (!hash_equals($_SESSION['template_selector_csrf'], $submittedToken)) {
        http_response_code(400);
        $error = 'La solicitud expiró. Recarga la página e inténtalo de nuevo.';
    } elseif (!in_array($selectedTemplate, $templates, true)) {
        http_response_code(400);
        $error = 'El template seleccionado no está disponible.';
    } else {
        $_SESSION['Template'] = $selectedTemplate;
        $_SESSION['template_selector_csrf'] = bin2hex(random_bytes(32));
        header('Location: '.rtrim(URL_BASE, '/').'/');
        exit;
    }
}

$currentTemplate = isset($_SESSION['Template']) && in_array($_SESSION['Template'], $templates, true)
    ? $_SESSION['Template']
    : 'newcomic';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(isset($_SESSION['Idioma']) ? $_SESSION['Idioma'] : 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seleccionar template</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: #f4f6f9; }
        .template-card { border: 1px solid #dee2e6; transition: transform .2s, box-shadow .2s; }
        .template-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.12); }
    </style>
</head>
<body>
    <main class="container py-5">
        <h1 class="mb-2">Selecciona un template</h1>
        <p class="text-secondary mb-4">La selección se guarda solo para esta sesión.</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($templates as $template): ?>
                <?php $isCurrent = $template === $currentTemplate; ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <form method="post" class="card template-card h-100">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 text-capitalize"><?php echo htmlspecialchars(str_replace(array('-', '_'), ' ', $template), ENT_QUOTES, 'UTF-8'); ?></h2>
                            <?php if ($isCurrent): ?>
                                <span class="badge text-bg-success align-self-start mb-3">En uso en esta sesión</span>
                            <?php endif; ?>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['template_selector_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="template" value="<?php echo htmlspecialchars($template, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-primary mt-auto">
                                <?php echo $isCurrent ? 'Aplicar de nuevo' : 'Usar este template'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
