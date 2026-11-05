<?php 
    $api_url = URL_API."Traducciones_web";
    $data = json_encode(['program' => "nav"]);
    $Traducciones = json_decode(API($jwt,$api_url,$data,'GET'), true);
?>        
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow pekez-navbar">
    <div class="container">
        <button class="navbar-toggler order-0" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-2 order-lg-0" id="menuPrincipal">
            <ul class="navbar-nav pekez-nav-links">
                <li class="nav-item"><a class="nav-link" href="<?php echo URL_BASE?>/categories"><?= Trd(1) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo URL_BASE?>/events"><?= Trd(2) ?></a></li>
                <?php if ($account['Couppon'] == 1) {?>
                    <li class="nav-item d-lg-none"><a class="nav-link" href="<?php echo URL_BASE?>/coupon/get-coupon"><?= Trd(3) ?></a></li>
                <?php }?>
                <li class="nav-item d-lg-none"><a class="nav-link" href="<?php echo URL_BASE?>/us"><?= Trd(5) ?></a></li>
                <li class="nav-item d-lg-none"><a class="nav-link" href="<?php echo URL_BASE?>/contact"><?= Trd(6) ?></a></li>
            </ul>
        </div>

        <a class="navbar-brand pekez-brand mx-auto order-1 order-lg-1" href="<?php echo URL_BASE?>">
            <img src="<?php echo COMPANY_LOGO?>" alt="<?php echo COMPANY_NAME?>">
            <span><?php echo COMPANY_NAME?></span>
        </a>

        <div class="pekez-nav-right order-3">
            <ul class="navbar-nav d-none d-lg-flex">
                <?php if ($account['Couppon'] == 1) {?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo URL_BASE?>/coupon/get-coupon"><?= Trd(3) ?></a></li>
                <?php }?>
                <li class="nav-item"><a class="nav-link" href="<?php echo URL_BASE?>/us"><?= Trd(5) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo URL_BASE?>/contact"><?= Trd(6) ?></a></li>
            </ul>
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="pekezLanguage" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-globe"></i> <?php echo strtoupper($_SESSION['Idioma'])?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="pekezLanguage">
                    <li><a class="dropdown-item lang-option" href="#" data-lang="es"><?= Trd(7) ?></a></li>
                    <li><a class="dropdown-item lang-option" href="#" data-lang="en"><?= Trd(8) ?></a></li>
                </ul>
            </div>
            <a class="nav-link position-relative pekez-cart" href="#" id="cartIcon" aria-label="Carrito" onclick="$('#modalReserva').modal('show'); return false;">
                <i class="fas fa-shopping-cart fa-lg"></i>
                <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
            </a>
        </div>
    </div>
</nav>