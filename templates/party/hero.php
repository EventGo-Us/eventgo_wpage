<section class="hero-logo-section d-flex align-items-center justify-content-center bg-white border-bottom">
    <div class="container text-center">
        <div class="dance-container">
            <div class="spinner-hover">
                <div id="dancingLogo" class="main-logo-content">
                    <img src="<?php echo COMPANY_LOGO?>" alt="dsjumpers" width="50%">
            </div>
        </div>
        
        <div class="hero-text mt-4">
            <h1 class="display-3 fw-bold text-dark"><?php echo COMPANY_NAME?></h1>
            <p class="lead text-secondary">
            <?php
            if (strtoupper($_SESSION['Idioma']) == 'ES') {
                echo 'Llevamos la diversión hasta ti';
            } else {
                echo 'Bringing the Fun to You';
            }
            ?>    
            </p>
        </div>
    </div>
</section>