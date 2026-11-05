<?php
    $api_url = URL_API."Traducciones_web";
    $data = json_encode(['program' => "ref"]);
    $Traducciones = json_decode(API($jwt,$api_url,$data,'GET'), true);

    if (!function_exists('comic_ref_html')) {
        function comic_ref_html($value)
        {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        }
    }

    if (!function_exists('comic_ref_stars')) {
        function comic_ref_stars($rating)
        {
            $rating = max(0, min(5, (int) round((float) $rating)));
            $stars = '';
            for ($index = 1; $index <= 5; $index++) {
                $stars .= $index <= $rating
                    ? "<span style='color: #f1c40f;'>★</span>"
                    : "<span style='color: #ccc;'>☆</span>";
            }
            return $stars;
        }
    }

    if (!function_exists('comic_ref_get_reviews')) {
        function comic_ref_get_reviews($apiKey, $placeId)
        {
            if (trim((string) $apiKey) === '' || trim((string) $placeId) === '') {
                return array();
            }

            $language = isset($_SESSION['Idioma']) ? strtolower(trim((string) $_SESSION['Idioma'])) : 'es';
            $language = in_array($language, array('es', 'en'), true) ? $language : 'es';
            $url = 'https://places.googleapis.com/v1/places/' . rawurlencode($placeId)
                . '?languageCode=' . rawurlencode($language);
            $headers = array(
                'X-Goog-Api-Key: ' . $apiKey,
                'X-Goog-FieldMask: displayName,reviews',
            );

            $curl = curl_init($url);
            if ($curl === false) {
                return array();
            }

            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
            ));

            $response = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($response === false || $httpCode < 200 || $httpCode >= 300) {
                return array();
            }

            $result = json_decode($response, true);
            return is_array($result) && !empty($result['reviews']) ? $result['reviews'] : array();
        }
    }

    $reviews = comic_ref_get_reviews(GOOGLE_API_KEY, PLACE_ID);
?>
<div class="comic-divider" data-label="⚡ LA GENTE HABLA ⚡"></div>

    <section class="mb-5">
        <div class="caption-box">📖 CAPÍTULO 2 — TESTIMONIOS HEROICOS</div>
        <h2 class="section-title" style="color:var(--blue);"><?= Trd(1) ?></h2>
        <p class="section-sub">— Ciudadanos que ya fueron salvados —</p>
        <div class="swiper swiperReviews">
            <div class="swiper-wrapper">
                <?php foreach ($reviews as $review):
                    $author = isset($review['authorAttribution']) ? $review['authorAttribution'] : array();
                    $authorName = isset($author['displayName']) ? $author['displayName'] : 'Anónimo';
                    $authorUrl = isset($author['uri']) ? $author['uri'] : '';
                    $photoUrl = isset($author['photoUri']) ? $author['photoUri'] : '';
                    $rating = isset($review['rating']) ? $review['rating'] : 0;
                    $reviewText = isset($review['text']['text']) ? $review['text']['text'] : '';
                    $reviewDate = isset($review['relativePublishTimeDescription']) ? $review['relativePublishTimeDescription'] : '';
                ?>
                    <div class="swiper-slide">
                        <div class="review-card">
                            <div class="stars"><?= comic_ref_stars($rating) ?></div>
                            <?php if ($reviewText !== ''): ?>
                                <p class="fst-italic"><?= nl2br(comic_ref_html($reviewText)) ?></p>
                            <?php endif; ?>
                            <?php if ($reviewDate !== ''): ?>
                                <small class="text-muted d-block mb-2"><?= comic_ref_html($reviewDate) ?></small>
                            <?php endif; ?>
                            <h6 class="mb-0">
                                <?php if ($photoUrl !== ''): ?>
                                    <img src="<?= comic_ref_html($photoUrl) ?>" alt="" loading="lazy" width="32" height="32" class="rounded-circle me-2">
                                <?php endif; ?>
                                <strong>
                                    <?php if ($authorUrl !== ''): ?>
                                        <a href="<?= comic_ref_html($authorUrl) ?>" target="_blank" rel="noopener noreferrer" style="color: inherit;">
                                            <?= comic_ref_html($authorName) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= comic_ref_html($authorName) ?>
                                    <?php endif; ?>
                                </strong>
                            </h6>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>