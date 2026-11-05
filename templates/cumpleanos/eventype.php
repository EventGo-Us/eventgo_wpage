<?php 
    $SecTitle = "";
    if ($_SESSION['Idioma'] == 'es') {
        $SecTitle = "SOMOS SU MEJOR OPCION PARA";
        $sec1 = "Bodas";
        $sec2 = "Cumpleaños";
        $sec3 = "Corporativos";
        $sec4 = "Graduaciones";
        $sec5 = "Conciertos";
    } else {
        $SecTitle = "WE ARE THE PLACE FOR";    
        $sec1 = "Weddings";
        $sec2 = "Birthdays";
        $sec3 = "Corporate";
        $sec4 = "Graduations";
        $sec5 = "Concerts";
    }

?>

<section class="mb-5 py-4" id="eventos">
        <div class="section-center">
            <h2 class="section-title"><?= $SecTitle ?>
                <span class="title-tag">¡Para cada ocasión!</span>
            </h2>
        </div>
        <div class="swiper swiperEvents">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="event-card"><i class="fas fa-glass-cheers"></i><h5>💍 <?= $sec1 ?></h5></div>
                </div>
                <div class="swiper-slide">
                    <div class="event-card"><i class="fas fa-birthday-cake"></i><h5>🎂 <?= $sec2 ?></h5></div>
                </div>
                <div class="swiper-slide">
                    <div class="event-card"><i class="fas fa-briefcase"></i><h5>🏢 <?= $sec3 ?></h5></div>
                </div>
                <div class="swiper-slide">
                    <div class="event-card"><i class="fas fa-graduation-cap"></i><h5>🎓 <?= $sec4 ?></h5></div>
                </div>
                <div class="swiper-slide">
                    <div class="event-card"><i class="fas fa-music"></i><h5>🎶 <?= $sec5 ?></h5></div>
                </div>
            </div>
            <div class="swiper-pagination mt-4"></div>
        </div>
    </section>