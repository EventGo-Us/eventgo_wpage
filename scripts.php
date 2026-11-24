<?php
// Obtener traducciones para el programa 'general' si no están cargadas previamente
if (!isset($TraduccionesGeneral)) {
    $api_url_gen = URL_API . "Traducciones_web";
    $datat_gen = json_encode(['program' => "general"]);
    $TraduccionesGeneral = json_decode(API($jwt, $api_url_gen, $datat_gen, 'GET'), true) ?? [];
}

// Obtener fechas no operativas (blackout dates) si no están cargadas previamente
if (!isset($blackout_dates)) {
    $api_url_bo = URL_API . "get_blackout_dates";
    $blackout_dates_response = json_decode(API($jwt, $api_url_bo, '', 'GET'), true);
    $blackout_dates = [];
    if (
        isset($blackout_dates_response['status']) &&
        $blackout_dates_response['status'] === 'success' &&
        isset($blackout_dates_response['data']) &&
        is_array($blackout_dates_response['data'])
    ) {
        foreach ($blackout_dates_response['data'] as $b_date) {
            $f_inicio = $b_date['fecha_inicio'] ?? null;
            $f_fin = $b_date['fecha_fin'] ?? null;
            $inicio = is_string($f_inicio) ? DateTime::createFromFormat('!Y-m-d', $f_inicio) : false;
            $fin = is_string($f_fin) ? DateTime::createFromFormat('!Y-m-d', $f_fin) : false;

            if (
                !$inicio || $inicio->format('Y-m-d') !== $f_inicio ||
                !$fin || $fin->format('Y-m-d') !== $f_fin ||
                $f_inicio > $f_fin
            ) {
                continue;
            }

            $blackout_dates[] = [
                'from' => $f_inicio,
                'to' => $f_fin
            ];
        }
    }
}
?>
<script>
    const url_base = "<?php echo URL_BASE; ?>/";
    const token = "<?php echo $jwt; ?>"; 
    const url_api = "<?php echo URL_API; ?>";
    const url_images = "<?php echo URL_IMAGES; ?>";
    const id_client = "<?= ID_CLIENT ?>"; 
    const CURRENCY_ = "<?= $account['Currency'] ?? '$' ?>"; 
    const TRADUCCIONES_GENERAL = <?= json_encode($TraduccionesGeneral ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const BLACKOUT_DATES = <?= json_encode($blackout_dates ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function trd(id, fallback = '') {
        return (typeof TRADUCCIONES_GENERAL !== 'undefined' && TRADUCCIONES_GENERAL && TRADUCCIONES_GENERAL[id]) 
            ? TRADUCCIONES_GENERAL[id] 
            : fallback;
    }
</script>
