<?php
$isSpanish = strtoupper($_SESSION['Idioma']) === 'ES';
?>
<section class="hero-logo-section border-bottom">
    <div class="container">
        <div class="hero-copy hero-text">
            <span class="hero-eyebrow">
                <?php echo $isSpanish ? 'Eventos bien organizados' : 'Thoughtfully planned events'; ?>
            </span>
            <h1><?php echo htmlspecialchars(COMPANY_NAME, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>
                <?php
                echo $isSpanish
                    ? 'Todo lo que necesitas para crear una experiencia memorable, con un servicio en el que puedes confiar.'
                    : 'Everything you need to create a memorable experience, with service you can count on.';
                ?>
            </p>
            <div class="hero-actions">
                <a class="btn btn-primary px-4 py-2" href="<?php echo URL_BASE; ?>/categories">
                    <?php echo $isSpanish ? 'Explorar catálogo' : 'Explore our catalog'; ?>
                </a>
                <a class="btn btn-outline-primary px-4 py-2" href="<?php echo URL_BASE; ?>/contact">
                    <?php echo $isSpanish ? 'Hablemos' : 'Get in touch'; ?>
                </a>
            </div>
        </div>
        <div class="hero-mark" aria-label="<?php echo htmlspecialchars(COMPANY_NAME, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="dance-container">
                <div class="spinner-hover">
                    <div id="dancingLogo" class="main-logo-content">
                        <img src="<?php echo COMPANY_LOGO; ?>" alt="<?php echo htmlspecialchars(COMPANY_NAME, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
