<section class="pekez-hero">
    <div class="container text-center">
        <div class="pekez-hero-logo">
            <img src="<?php echo COMPANY_LOGO?>" alt="<?php echo COMPANY_NAME?>">
        </div>
        <p class="pekez-hero-kicker">
            <?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? '¡La diversión comienza aquí!' : 'Let the fun begin!'?>
        </p>
        <h1><?php echo COMPANY_NAME?></h1>
        <p class="pekez-hero-copy">
            <?php
            if (strtoupper($_SESSION['Idioma']) == 'ES') {
                echo 'Brincolines y experiencias para celebrar en grande.';
            } else {
                echo 'Inflatables and experiences to celebrate in a big way.';
            }
            ?>
        </p>
        <a class="btn pekez-button" href="<?php echo URL_BASE?>/categories">
            <?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? '¡Encuentra tu diversión!' : 'Find your fun!'?>
            <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>