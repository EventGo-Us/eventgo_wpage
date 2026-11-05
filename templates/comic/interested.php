    <section class="mb-5" id="categorias">
        <div class="caption-box">📖 CAPÍTULO 1 — ESCOGE TU PODER</div>
        <h2 class="section-title" style="color:var(--red);">Categorías Destacadas</h2>
        <p class="section-sub">— ¡Cada héroe tiene su especialidad! —</p>
        <div class="swiper swiperCategories">
            <div class="swiper-wrapper">
                <?php
                
                    foreach ($data['UpSelling'] as $UpSelling) {
                        $UpSelling['Image'] = URL_IMAGES.'/products_images/thumbnails/'.$UpSelling['Image'];
                        $URL = str_replace(" ","-",$UpSelling['Name']);
                        $Rup = str_replace(" ","-",$UpSelling['Producto_rup']);
                        $DTS = '';
                        if (isset($_GET['SD'], $_GET['ED'], $_GET['SH'], $_GET['EH'])) {
                            $DTS = '&SD='.rawurlencode($_GET['SD'])
                                .'&ED='.rawurlencode($_GET['ED'])
                                .'&SH='.rawurlencode($_GET['SH'])
                                .'&EH='.rawurlencode($_GET['EH']);
                        }
                        echo "  <div class='swiper-slide'>
                                    <a href='".URL_BASE."/product/{$URL}?Idp={$Rup}{$DTS}' class='cat-card'>

                                    <img height='150px' src='{$UpSelling['Image']}' alt='{$UpSelling['Name']}'> 
                                    <br>
                                    <b>{$UpSelling['Name']}</b>
                                    <br> $ {$UpSelling['Price']}
                                    </a>
                                </div>";
                    }
                
                ?>
            </div>
            <div class="swiper-pagination mt-4"></div>
        </div>
    </section>