<section class="hero-logo-section extreme-hero d-flex align-items-center justify-content-center">
    <div class="extreme-hero-grid" aria-hidden="true"></div>
    <div class="container text-center extreme-hero-content">
        <p class="extreme-eyebrow">
            <span class="extreme-status-dot"></span>
            <?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? 'SUBE EL NIVEL' : 'PUSH YOUR LIMITS'; ?>
        </p>
        <div class="dance-container">
            <div class="spinner-hover">
                <div id="dancingLogo" class="main-logo-content">
                    <img src="<?php echo COMPANY_LOGO?>" alt="<?php echo htmlspecialchars(COMPANY_NAME, ENT_QUOTES, 'UTF-8'); ?>" width="50%">
                </div>
            </div>
        </div>
        
        <div class="hero-text mt-4">
            <h1 class="display-3 fw-bold text-dark extreme-title"><?php echo COMPANY_NAME?></h1>
            <p class="lead text-secondary extreme-subtitle">
            <?php
            if (strtoupper($_SESSION['Idioma']) == 'ES') {
                echo 'Siente la adrenalina. Vive cada momento al límite.';
            } else {
                echo 'Feel the rush. Live every moment full throttle.';
            }
            ?>    
            </p>
        </div>
        <div class="extreme-readouts" aria-label="<?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? 'Indicadores de experiencia' : 'Experience indicators'; ?>">
            <div class="extreme-readout">
                <span class="extreme-readout-icon"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i></span>
                <span class="extreme-readout-label"><?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? 'INTENSIDAD' : 'INTENSITY'; ?></span>
                <strong><?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? 'A TOPE' : 'FULL SEND'; ?></strong>
                <span class="extreme-meter" aria-hidden="true"><span></span></span>
            </div>
            <div class="extreme-readout">
                <span class="extreme-readout-icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
                <span class="extreme-readout-label"><?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? 'ACTITUD' : 'ATTITUDE'; ?></span>
                <strong><?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? 'SIN LÍMITES' : 'NO LIMITS'; ?></strong>
                <span class="extreme-meter extreme-meter-lime" aria-hidden="true"><span></span></span>
            </div>
            <div class="extreme-readout">
                <span class="extreme-readout-icon"><i class="fa-solid fa-mountain-sun" aria-hidden="true"></i></span>
                <span class="extreme-readout-label"><?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? 'TU PRÓXIMO RETO' : 'YOUR NEXT RIDE'; ?></span>
                <strong><?php echo strtoupper($_SESSION['Idioma']) == 'ES' ? 'EMPIEZA AQUÍ' : 'START HERE'; ?></strong>
                <span class="extreme-meter extreme-meter-orange" aria-hidden="true"><span></span></span>
            </div>
        </div>
    </div>
</section>