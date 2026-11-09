<?php 
    ob_start();
    session_start();
    require 'vendor/autoload.php';
    require_once 'config.php';
    require_once 'functions.php';
    require_once TEMPLATE.'head.php'; 
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>        
    <link rel="stylesheet" href="css/general.css">
    <style>

        #pdf-canvas {
            max-height: 240px;
            object-fit: contain;
            background-color: #eee;
        }

        .spinner-border {
            --bs-spinner-width: 1.2rem;
            --bs-spinner-height: 1.2rem;
            vertical-align: middle;
            margin-right: 8px;
        }

    </style>
</head>
<body>

<?php 
    require_once TEMPLATE.'nav.php'; 


    $api_url = URL_API."Traducciones_web";
    $data = json_encode(['program' => "mpayment"]);
    $Traducciones = json_decode(API($jwt,$api_url,$data,'GET'), true);
?>




<?php
        if (!isset($_GET['Id'])){
            echo Trd(1);
            die();
        }

        $token = $_GET['Id']; // El UUID de la URL
        $ahora = date("Y-m-d H:i:s");


        $api_url = URL_API."quotes";
        $data = json_encode(['token' => $token]);
        $cotizacion = json_decode(API($jwt,$api_url,$data,'GET'), true);

        if ($cotizacion) {
            // Verificar si la fecha actual es mayor a la de expiración
            if ($ahora > $cotizacion['ExpDate']) {
                echo Trd(2)." " . $cotizacion['ExpDate']." $ahora";
                die();
            }
        } else {
            echo Trd(1);
            die();
        }

    ob_end_flush(); // Envía el contenido al final        
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm p-4">
                <h4 class="mb-4 fw-bold text-center">Descarga tu contrato firmado</h4>
                    <div class="mb-4">                    
                        <h6 class="fw-bold mb-3"><?= Trd(4) ?></h6>
                        <div class="row g-3 align-items-center bg-light p-3 rounded border">
                            <div class="col-5 col-sm-4">
                                <div class="position-relative border bg-white rounded overflow-hidden shadow-sm" style="min-height: 120px;">
                                    <canvas id="pdf-canvas" class="w-100 d-block"></canvas>
                                    <div id="loader-pdf" class="position-absolute top-50 start-50 translate-middle">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-7 col-sm-8">
                                <p class="mb-1 fw-bold text-truncate" id="pdf-name"><?= Trd(5) ?></p>
                                <p class="mb-2 text-muted small">
                                    <i class="bi bi-file-earmark-pdf"></i> <?= Trd(6) ?>
                                </p>
                                <a href="<?php echo $token;?>.pdf" class="btn btn-outline-primary btn-sm" download>
                                    <i class="bi bi-download"></i><?= Trd(7) ?>
                                </a>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php require_once TEMPLATE.'social.php'; ?>
<?php require_once TEMPLATE.'scripts.php'; ?>
<?php require_once 'scripts.php'; ?>
<script src="<?php echo TEMPLATE;?>js/idx-template.js"></script>
<script src="js/general.php"></script>
<script>
    $(document).ready(function() {
    const url = '<?= $token ?>.pdf'; // Ruta de tu PDF
    const pdfjsLib = window['pdfjs-dist/build/pdf'] || window.pdfjsLib;

        if (pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

            pdfjsLib.getDocument(url).promise.then(pdf => {
                pdf.getPage(1).then(page => {
                    const canvas = document.getElementById('pdf-canvas');
                    const context = canvas.getContext('2d');

                    // Ajustamos la escala para que se vea bien en la columna pequeña
                    const viewport = page.getViewport({ scale: 0.5 });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };

                    page.render(renderContext).promise.then(() => {
                        // Ocultar el spinner cuando termine de renderizar
                        $('#loader-pdf').fadeOut();
                    });
                });
            }).catch(err => {
                console.error("Error al cargar PDF:", err);
                $('#loader-pdf').html('<small class="text-danger">Error</small>');
            });
        }    

    });
</script>



</body>
</html>