<?php 
    ob_start();
    session_start();
    require 'vendor/autoload.php';
    require_once 'config.php';
    require_once 'functions.php';
    require_once TEMPLATE.'head.php'; 

    $api_url = URL_API."products";
    
    $Product = str_replace("-"," ",$_GET['Id']);
    $IdP = str_replace("-"," ",$_GET['Idp']);
    if (!isset($_GET['SD']))
        $data = json_encode(["Product" => $Product ,"IdP" => $IdP ,"SD" => date("Y-m-d"),"ED" => date("Y-m-d"),"SH" => '08:00',"EH" => '16:00']);
    else
        $data = json_encode(["Product" => $Product ,"IdP" => $IdP ,"SD" => $_GET['SD'],"ED" => $_GET['ED'],"SH" => $_GET['SH'],"EH" => $_GET['EH']]);
    
    $data = json_decode(API($jwt,$api_url,$data,'POST'), true);
//print_r($data);  
//echo $data["Resultadosp"][0]["JsonPrice"];

    $datar = $data;
    if ($data['status'] === 'success') {
        $productoMeta = $data['data'][0] ?? [];
        $metaTitle = trim($productoMeta['Name'] ?? $Product);
        $metaDescription = trim(preg_replace('/\s+/', ' ', strip_tags($productoMeta['Description'] ?? '')));
        $metaImage = !empty($data['Image'][0]['Image'])
            ? URL_IMAGES.'/products_images/thumbnails/'.$data['Image'][0]['Image']
            : '';
        $metaUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
            .'://'.($_SERVER['HTTP_HOST'] ?? '').($_SERVER['REQUEST_URI'] ?? '');
        ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:title" content="<?= htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:type" content="product">
        <meta property="og:url" content="<?= htmlspecialchars($metaUrl, ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($metaImage !== ''): ?>
            <meta property="og:image" content="<?= htmlspecialchars($metaImage, ENT_QUOTES, 'UTF-8') ?>">
            <meta name="twitter:image" content="<?= htmlspecialchars($metaImage, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8') ?>">
        <meta name="twitter:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
        <?php
        $schemaImages = [];
        foreach ($data['Images'] ?? [] as $image) {
            if (!empty($image['Image'])) {
                $schemaImages[] = URL_IMAGES.'/products_images/thumbnails/'.$image['Image'];
            }
        }
        if ($metaImage !== '' && !in_array($metaImage, $schemaImages, true)) {
            array_unshift($schemaImages, $metaImage);
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $metaTitle,
            'description' => $metaDescription,
            'url' => $metaUrl,
            'image' => $schemaImages
        ];

        $schemaPrice = $data['Resultadosp'][0]['Price'] ?? null;
        if ($schemaPrice !== null) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => 'USD',
                'price' => (float) $schemaPrice,
                'availability' => ((int) ($data['Resultadosp'][0]['Quantity'] ?? 0) > 0)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => $metaUrl
            ];
        }
        ?>
        <script type="application/ld+json">
            <?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
        </script>
        <?php

    }
    else{
        echo "<h1>Error en la API</h1>";
        echo "Mensaje: " . ($data['message'] ?? 'Error desconocido');
    }
?>


    <link rel="stylesheet" href="<?php echo URL_BASE."/";?>css/general.css">
</head>
<body>

<?php require_once TEMPLATE.'nav.php'; ?>
<div class="container py-5">
    <?php
    //print_r($datar);
    $data = $datar;
    if ($data['status'] === 'success') {
    ?>
        
    <style>
        /* Estilo para que la columna izquierda sea pegajosa */
        @media (min-width: 992px) {
            .sticky-column {
                position: -webkit-sticky;
                position: sticky;
                top: 20px; /* Distancia desde el borde superior de la pantalla */
                height: fit-content;
            }
        }
        
        .main-image-container {
            height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .main-image-container img {
            max-height: 100%;
            object-fit: contain;
        }

        .extras-panel {
            padding: 1.25rem;
            border: 1px solid #e8edf3;
            border-radius: 1rem;
            background: linear-gradient(145deg, #f8fafc, #fff);
            box-shadow: 0 8px 24px rgba(31, 45, 61, 0.06);
        }

        .extras-heading {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 1rem;
        }

        .extras-heading-icon {
            display: inline-flex;
            width: 2.25rem;
            height: 2.25rem;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e8f1ff;
            color: #0d6efd;
        }

        .extras-carousel {
            max-width: 390px;
            margin: 0 auto;
            padding: 0 2.5rem 0.5rem;
        }

        .extras-carousel .carousel-indicators {
            position: relative;
            right: auto;
            bottom: auto;
            left: auto;
            gap: 0.25rem;
            margin: 0 0 0.75rem;
        }

        .extras-carousel .carousel-indicators button {
            width: 0.45rem;
            height: 0.45rem;
            margin: 0;
            border: 0;
            border-radius: 50%;
            background-color: #aab4c0;
        }

        .extras-carousel .carousel-indicators button.active {
            background-color: #0d6efd;
        }

        .extras-card {
            overflow: hidden;
            border-radius: 0.9rem;
            background: #fff;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .extras-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(31, 45, 61, 0.12) !important;
        }

        .extras-card .extras-image,
        .extras-card .extras-placeholder {
            height: 155px;
            background: linear-gradient(135deg, #f1f5f9, #fff);
        }

        .extras-card .extras-image {
            padding: 0.75rem;
            object-fit: contain;
        }

        .extras-card .extras-placeholder {
            color: #475569;
            font-size: 1.05rem;
        }

        .extras-card .card-body {
            padding: 1rem 1.15rem 1.15rem;
        }

        .extras-card .card-title {
            min-height: 2.5rem;
            margin-bottom: 0.35rem;
        }

        .extras-card .extras-price {
            color: #0d6efd !important;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .extras-card .extras-select {
            border-radius: 0.65rem;
            font-weight: 600;
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .extras-card .btn-check:checked + .extras-select {
            color: #fff;
            background-color: #0d6efd;
        }

        .extras-carousel .carousel-control-prev,
        .extras-carousel .carousel-control-next {
            width: 2.25rem;
            opacity: 1;
        }

        .extras-carousel .carousel-control-prev-icon,
        .extras-carousel .carousel-control-next-icon {
            width: 2rem;
            height: 2rem;
            background-size: 45%;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2);
        }

        @media (max-width: 575.98px) {
            .extras-panel {
                padding: 1rem 0.75rem;
            }

            .extras-carousel {
                padding-right: 2rem;
                padding-left: 2rem;
            }
        }
    </style>
    
    <style>
        /* Contenedor del carrusel */
        .carrusel-contenedor {
            display: flex;
            gap: 24px;
            overflow-x: auto;
            padding: 10px;
            max-width: 1200px;
            margin: 0 auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        /* Ocultar barra de scroll estándar */
        .carrusel-contenedor::-webkit-scrollbar {
            display: none;
        }
        .carrusel-contenedor {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Elemento de video */
        .item-video {
            flex: 0 0 340px;
            transition: opacity 0.3s ease;
            opacity: 0.75;
        }

        .item-video:hover {
            opacity: 1;
        }

        /* Contenedor 16:9 */
        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%; 
            height: 0;
            overflow: hidden;
            border-radius: 6px;
            background-color: #f5f5f7;
        }

        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .titulo-video {
            margin-top: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #222222;
            line-height: 1.4;
        }

        /* Contenedor de los puntos (Dots) */
        .indicadores-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }

        /* Estilo base de cada punto */
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #e0e0e0;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        /* Estilo del punto activo */
        .dot.activo {
            background-color: #111111;
            transform: scale(1.2); /* Se hace ligeramente más grande */
        }
    </style>    

<?php 
    $api_url = URL_API."Traducciones_web";
    $datat = json_encode(['program' => "producto"]);
    $Traducciones = json_decode(API($jwt,$api_url,$datat,'GET'), true);
    foreach ($data['data'] as $producto) {

?>
    <div class="row">
        
        <div class="col-lg-6 mb-4">
            <div class="sticky-column">
                <div class="main-image-container mb-3 border rounded bg-white shadow-sm">
                    <?php 
                        foreach ($data['Image'] as $Image) {
                            $URLImagep = URL_IMAGES.'/products_images/originals/'.$Image['Image'];
                            echo "<img id='mainProductImage' src='$URLImagep' class='img-fluid' alt='Producto'>";
                        }
                    ?>
                </div>

                <div id="productThumbnails" class="p-2 border rounded bg-light shadow-sm">
                    <div class="d-flex justify-content-center overflow-auto">
                        <?php 
                            foreach ($data['Images'] as $Image) {
                                $URLImage = URL_IMAGES.'/products_images/thumbnails/'.$Image['Image'];
                                echo "<img src='$URLImage' class='img-thumbnail me-2' style='width: 70px; cursor: pointer;' onclick='changeImage(this.src)'>";
                            }
                        ?>
                    </div>
                    <p class="text-center small text-muted mt-2 mb-0"><?= Trd(3) ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <h1 class="fw-bold mb-3"><?php echo $producto['Name']?></h1>
            <div class="h3 text-primary mb-4">$<?php echo  number_format($data['Resultadosp'][0]['Price'], 2, '.', ',');  ?></div>
                <?php
                $precios_Extra = calculateNextPeriodPrice($data["Resultadosp"][0]["JsonPrice"]);
                echo "<div class='mb-4 text-muted'>".Trd(9).": $".number_format($precios_Extra['siguiente_periodo'], 2, '.', ',')."</div>";
                ?>
                <div class="d-flex align-items-center gap-2 mb-4" style="max-width: 400px;">
                    
                    <div class="input-group" style="width: 140px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(-1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        
                        <input type="text" id="cart-qty" 
                            class="form-control text-center fw-bold border-secondary" 
                            value="1" readonly 
                            autocomplete="off"
                            style="background-color: #fff;">
                        
                        <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <button class="btn btn-primary flex-grow-1" onclick="add_cart()">
                        <i class="fas fa-shopping-cart me-2"></i><?= Trd(1) ?>
                    </button>
                </div>

            <?php
                $accesoriosOcultos = array_values(array_filter($data['AccesoriesH'] ?? [], function ($accesorio) {
                    return isset($accesorio['Category']) && $accesorio['Category'] != 'Hidden';
                }));
                $carouselId = 'extras-producto-' . (int) $producto['Id'];

                if ($accesoriosOcultos):
            ?>
                <div class="extras-panel mb-4">
                    <div class="extras-heading">
                        <span class="extras-heading-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                        <div>
                            <h5 class="fw-bold mb-0"><?= Trd(10) ?></h5>
                            <small class="text-muted"><?= Trd(11) ?></small>
                        </div>
                    </div>
                    <div id="<?= $carouselId ?>" class="carousel slide extras-carousel" data-bs-interval="false" aria-label="Opciones adicionales">
                        <?php if (count($accesoriosOcultos) > 1): ?>
                            <div class="carousel-indicators">
                                <?php foreach ($accesoriosOcultos as $indice => $accesorio): ?>
                                    <button type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide-to="<?= $indice ?>"
                                        class="<?= $indice === 0 ? 'active' : '' ?>" aria-label="Opción <?= $indice + 1 ?>"
                                        <?= $indice === 0 ? 'aria-current="true"' : '' ?>></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="carousel-inner">
                            <?php foreach ($accesoriosOcultos as $indice => $accesorio):
                                $nombreExtra = (string) ($accesorio['Name'] ?? '');
                                $imagenExtra = !empty($accesorio['Image'])
                                    ? URL_IMAGES . '/products_images/thumbnails/' . $accesorio['Image']
                                    : '';
                                $argumentosExtra = [
                                    $producto['Id'],
                                    $accesorio['Producto_r'],
                                    $nombreExtra,
                                    (string) $accesorio['Price'],
                                    $imagenExtra,
                                    '',
                                    'hidden-accessories'
                                ];
                                $argumentosExtra = implode(',', array_map(function ($valor) {
                                    return htmlspecialchars(
                                        json_encode($valor, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                }, $argumentosExtra));

                                $argumentosExtra = cambiarQuotPorComillaSimple($argumentosExtra);
                                $idOpcion = $carouselId . '-opcion-' . $indice;
                            ?>
                                <div class="carousel-item <?= $indice === 0 ? 'active' : '' ?>">
                                    <div class="card extras-card border-0 shadow-sm mx-auto text-center" style="max-width: 280px;">
                                        <?php if ($imagenExtra !== ''): ?>
                                            <img src="<?= htmlspecialchars($imagenExtra, ENT_QUOTES, 'UTF-8') ?>"
                                                class="card-img-top extras-image" alt="<?= htmlspecialchars($nombreExtra, ENT_QUOTES, 'UTF-8') ?>">
                                        <?php else: ?>
                                            <div class="extras-placeholder d-flex align-items-center justify-content-center fw-bold p-3">
                                                <?= htmlspecialchars($nombreExtra, ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title fw-bold"><?= htmlspecialchars($nombreExtra, ENT_QUOTES, 'UTF-8') ?></h6>
                                            <p class="extras-price mb-3">$<?= htmlspecialchars((string) $accesorio['Price'], ENT_QUOTES, 'UTF-8') ?></p>
                                            <input class="btn-check" type="radio" name="<?= $carouselId ?>-seleccion"
                                                id="<?= $idOpcion ?>" value="<?= htmlspecialchars((string) $accesorio['Producto_r'], ENT_QUOTES, 'UTF-8') ?>"
                                                autocomplete="off" onchange="agregarExtraAItem(<?= $argumentosExtra ?>)">
                                            <label class="btn btn-outline-primary extras-select w-100" for="<?= $idOpcion ?>"><?= Trd(12) ?></label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($accesoriosOcultos) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="prev"
                                aria-label="Opción anterior">
                                <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="next"
                                aria-label="Opción siguiente">
                                <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

                <div class="mb-4">
                    <span class="badge rounded-pill bg-light text-dark border">
                        <i class="fas fa-boxes me-1 text-primary"></i> 
                        <?= Trd(2) ?>: <span id="stock-val">0</span>
                    </span>
                </div>            

            <hr>
                <div class="product-details">                
                    <?php echo $producto['Description']?>

                    <?php if ($producto['ActualSize'] != ''):?>
                    <h5 class="mt-4 fw-bold"><?= Trd(4) ?></h5>
                    <p><?php echo $producto['ActualSize']?></p>
                    <?php endif;?>
                    
                    <?php if ($producto['SpaceRequired'] != ''):?>
                    <h5 class="mt-4 fw-bold"><?= Trd(5) ?></h5>
                    <p><?php echo $producto['SpaceRequired']?></p>
                    <?php endif;?>

                    <?php if ($producto['Weight'] != ''):?>
                    <h5 class="mt-4 fw-bold"><?= Trd(6) ?></h5>
                    <p><?php echo $producto['Weight']?></p>
                    <?php endif;?>
                </div>    
        </div>

    </div>

    <?php if ($data['Videos']):?>
    <div class="seccion-siguiente2">
        <h2 class="font-title" >VIDEOS</h2>
    </div>        

    <div class="col-lg-12 border rounded bg-white shadow-sm" >        
        <br>
        <div class="carrusel-contenedor" id="carrusel">

            <?php 
                foreach ($data['Videos'] as $Video) {
                    echo '
                    <div class="item-video">
                        <div class="video-wrapper">
                            <iframe src="'.$Video['Video'].'" title="'.$Video['Title'].'" allowfullscreen></iframe>
                        </div>
                        <div class="titulo-video">'.$Video['Title'].'</div>
                    </div>
                    ';
                }
            ?>    

        </div>

        <div class="indicadores-dots" id="contenedor-dots">
        </div>  
        <br>
    </div>    
    <?php endif;?>    


        <?php
        }
    } else {
        echo "<h1>Error en la API</h1>";
        echo "Mensaje: " . ($data['message'] ?? 'Error desconocido');
    }
    $dataRsp = $data;
    ?>

    <hr class="my-5">
    <?php 
    $Title=Trd(7);
    $SubTitle="Accesorios";
    $SSubTitle="Accesorios";
    require_once TEMPLATE.'accesories.php'; ?>
    <hr class="my-5">
    <?php 
    
    $Title=Trd(8);
    $SubTitle="Accesorios";
    $SSubTitle="Accesorios";    
    require_once TEMPLATE.'interested.php'; ?>

</div>

<?php require_once TEMPLATE.'social.php'; ?>
<?php require_once TEMPLATE.'cart.php'; ?>
<?php require_once TEMPLATE.'scripts.php'; ?>
<?php require_once 'scripts.php'; ?>

<script src="<?php echo URL_BASE."/".TEMPLATE;?>js/idx-template.js"></script>
<script src="<?php echo URL_BASE."/";?>js/general.php"></script>
<?php
    $data = $dataRsp;
?>
<script>

    var MAX_STOCK = <?php echo $data['Resultadosp'][0]['Quantity']?>; 
    const actualID = <?php echo $producto['Id']; ?>;
    const qtyInput = document.getElementById('cart-qty');

    function changeImage(src) {
        document.getElementById('mainProductImage').src = src.replace('/thumbnails/', '/originals/');
    }

    function add_cart(){
        const miProducto = {
            id: <?php echo $producto['Id']; ?>, // ID único de tu base de datos (MySQL)
            nombre: "<?php echo $producto['Name']; ?>",
            precio: <?php echo $data['Resultadosp'][0]['Price']?>,
            imagen: "<?php echo $URLImagep;?>",
            url: $(location).prop('href'),
            existencia:$('#stock-val').html(),
            adicionales: []
        };

        const qtyInput = document.getElementById('cart-qty');

        if (qtyInput.value <= MAX_STOCK){
            MAX_STOCK = MAX_STOCK - qtyInput.value ;
            $('#stock-val').html(MAX_STOCK);        
            agregarAlCarrito(miProducto,parseInt(qtyInput.value));
        }
    }

    function adjustQty(amount) {
        let current = parseInt(qtyInput.value);
        let newValue = current + amount;

        // Validación estricta: Mínimo 1, Máximo disponible
        if (newValue >= 1 && newValue <= MAX_STOCK) {
            qtyInput.value = newValue;
            $('#stock-val').html(MAX_STOCK - newValue);
        }
        if (newValue == 1)
            $('#stock-val').html(MAX_STOCK);
    }

    $(document).ready(function() {
        let carrito = obtenerCarrito();
        const item = carrito.find(p => p.id == actualID );    
        if (item) {
            MAX_STOCK = MAX_STOCK - item.cantidad ;
            const extraSeleccionado = (item.adicionales || []).find(extra => extra.grupo === 'hidden-accessories');
            if (extraSeleccionado) {
                document.querySelectorAll('input[name="extras-producto-' + actualID + '-seleccion"]').forEach(opcion => {
                    opcion.checked = String(opcion.value) === String(extraSeleccionado.id);
                });
            }
        }
        $('#stock-val').html(MAX_STOCK);
    });    
</script>

<script>
    const carrusel = document.getElementById('carrusel');
    const contenedorDots = document.getElementById('contenedor-dots');
    const videos = document.querySelectorAll('.item-video');

    // 1. Generar los puntos dinámicamente según la cantidad de videos
    videos.forEach((_, indice) => {
        const dot = document.createElement('div');
        dot.classList.add('dot');
        if (indice === 0) dot.classList.add('activo'); // El primero empieza activo
        
        // Hacer que los puntos sean clickeables para mover el carrusel
        dot.addEventListener('click', () => {
            const anchoVideo = videos[0].offsetWidth + 24; // Ancho del video + el espacio (gap)
            carrusel.scrollLeft = anchoVideo * indice;
        });

        contenedorDots.appendChild(dot);
    });

    const dots = document.querySelectorAll('.dot');

    // 2. Función para actualizar qué punto se ilumina al hacer scroll
    function actualizarDots() {
        const anchoVideo = videos[0].offsetWidth + 24;
        // Calculamos cuál es el video más visible en pantalla actualmente
        const indiceActivo = Math.round(carrusel.scrollLeft / anchoVideo);

        dots.forEach((dot, indice) => {
            if (indice === indiceActivo) {
                dot.classList.add('activo');
            } else {
                dot.classList.remove('activo');
            }
        });
    }

    // Escuchar el evento de scroll (sirve para rueda de ratón y scroll táctil en móvil)
    carrusel.addEventListener('scroll', actualizarDots);

    // 3. Mantener el soporte para transformar scroll vertical del ratón en horizontal
    carrusel.addEventListener('wheel', (e) => {
        if (e.deltaY !== 0) {
            e.preventDefault();
            carrusel.scrollLeft += e.deltaY * 1.2;
        }
    });
</script>

</body>
</html>
<?php

function calculateNextPeriodPrice(string $json): array
{
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    if (!isset($data['pasos']) || !is_array($data['pasos'])) {
        throw new InvalidArgumentException('El JSON debe incluir un arreglo "pasos".');
    }

    $horasPorUnidad = [
        'hora' => 1,
        'horas' => 1,
        'dia' => 24,
        'dias' => 24,
        'semana' => 168,
        'semanas' => 168,
    ];

    $convertirHoras = static function ($cantidad, $unidad) use ($horasPorUnidad): float {
        if (
            !isset($horasPorUnidad[$unidad]) ||
            !is_numeric($cantidad) ||
            (float) $cantidad <= 0
        ) {
            throw new InvalidArgumentException('La cantidad o unidad del periodo no es válida.');
        }

        return (float) $cantidad * $horasPorUnidad[$unidad];
    };

    $segmentos = [];
    $inicioSegmento = 0.0;

    foreach ($data['pasos'] as $paso) {
        if (
            !is_array($paso) ||
            !isset($paso['caso'], $paso['tipo'], $paso['precio']) ||
            !is_numeric($paso['precio'])
        ) {
            throw new InvalidArgumentException('Cada paso debe incluir "caso", "tipo" y un "precio" válido.');
        }

        $finSegmento = INF;

        if ($paso['tipo'] === 'hasta') {
            $finSegmento = $convertirHoras(
                $paso['cantidad'] ?? null,
                $paso['unidad'] ?? ''
            );

            if ($finSegmento <= $inicioSegmento) {
                throw new InvalidArgumentException('El límite "hasta" debe ser posterior al inicio del periodo.');
            }
        }

        if ((int) $paso['caso'] === 2) {
            $cada = $paso['cada'] ?? [];

            $segmentos[] = [
                'inicio' => $inicioSegmento,
                'fin' => $finSegmento,
                'precio' => (float) $paso['precio'],
                'periodo' => $convertirHoras(
                    $cada['cantidad'] ?? null,
                    $cada['unidad'] ?? ''
                ),
            ];

            $inicioSegmento = $finSegmento;
        } elseif ($paso['tipo'] !== 'cada') {
            $segmentos[] = [
                'inicio' => $inicioSegmento,
                'fin' => $finSegmento,
                'precio' => (float) $paso['precio'],
                'periodo' => null,
            ];

            $inicioSegmento = $finSegmento;
        }
    }

    if ($segmentos === []) {
        throw new InvalidArgumentException('No hay periodos de precio para calcular.');
    }

    $calcularPrecioHasta = static function (float $horas) use ($segmentos): float {
        $total = 0.0;

        foreach ($segmentos as $segmento) {
            if ($horas <= $segmento['inicio']) {
                break;
            }

            $horasEnSegmento = min($horas, $segmento['fin']);

            if ($segmento['periodo'] !== null) {
                $periodos = ceil(
                    ($horasEnSegmento - $segmento['inicio']) / $segmento['periodo'] - 1e-9
                );
                $total += $segmento['precio'] * $periodos;
            } else {
                $total += $segmento['precio'];
            }
        }

        return $total;
    };

    $primerSegmento = $segmentos[0];
    $finPrimerPeriodo = min(
        $primerSegmento['inicio'] + ($primerSegmento['periodo'] ?? INF),
        $primerSegmento['fin']
    );

    if (!is_finite($finPrimerPeriodo) || $finPrimerPeriodo <= $primerSegmento['inicio']) {
        throw new InvalidArgumentException('El JSON no define un primer periodo válido.');
    }

    $precioPrimerPeriodo = $calcularPrecioHasta($finPrimerPeriodo);
    $siguienteSegmento = null;

    foreach ($segmentos as $indice => $segmento) {
        if (
            $indice > 0 &&
            $segmento['inicio'] >= $finPrimerPeriodo - 1e-9
        ) {
            $siguienteSegmento = $segmento;
            break;
        }
    }

    if ($siguienteSegmento !== null) {
        if ($siguienteSegmento['periodo'] !== null) {
            $finSiguientePeriodo = min(
                $siguienteSegmento['inicio'] + $siguienteSegmento['periodo'],
                $siguienteSegmento['fin']
            );
        } else {
            // Avanza una fracción mínima para incluir el precio fijo del siguiente tramo.
            $finSiguientePeriodo = $siguienteSegmento['inicio'] + (1 / 3600);
        }

        if ($finSiguientePeriodo <= $siguienteSegmento['inicio']) {
            throw new InvalidArgumentException('El JSON no define un siguiente periodo válido.');
        }
    } elseif (
        $primerSegmento['periodo'] !== null &&
        $primerSegmento['fin'] > $finPrimerPeriodo
    ) {
        $finSiguientePeriodo = min(
            $finPrimerPeriodo + $primerSegmento['periodo'],
            $primerSegmento['fin']
        );
    } else {
        throw new InvalidArgumentException('El JSON no define un siguiente periodo con precio.');
    }

    $precioSiguientePeriodo =
        $calcularPrecioHasta($finSiguientePeriodo) - $precioPrimerPeriodo;

    return [
        'primer_periodo' => $precioPrimerPeriodo,
        'siguiente_periodo' => $precioSiguientePeriodo,
    ];
}
?>