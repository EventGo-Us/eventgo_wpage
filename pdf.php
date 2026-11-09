<?php
ob_start();
session_start();
require 'vendor/autoload.php';
require_once 'config.php';
require_once 'functions.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$json = file_get_contents('php://input');
$data = json_decode($json);

function convertAvifImagesToJpeg(string $html, string $imageBaseUrl): string
{
    $base = parse_url($imageBaseUrl);
    if (!$base || empty($base['host']) || empty($base['path'])) {
        throw new RuntimeException('La URL base de imágenes no es válida.');
    }

    $allowedHost = strtolower($base['host']);
    $allowedPath = rtrim($base['path'], '/') . '/';

    return preg_replace_callback(
        '~<img\b[^>]*>~i',
        function ($tagMatch) use ($allowedHost, $allowedPath) {
            if (!preg_match('~\bsrc\s*=\s*(["\'])(.*?)\1~is', $tagMatch[0], $srcMatch)) {
                return $tagMatch[0];
            }

            $src = html_entity_decode($srcMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $parts = parse_url($src);
            if (!$parts || empty($parts['path']) || !preg_match('~\.avif$~i', $parts['path'])) {
                return $tagMatch[0];
            }

            if (
                strtolower($parts['scheme'] ?? '') !== 'https'
                || strtolower($parts['host'] ?? '') !== $allowedHost
                || !str_starts_with($parts['path'], $allowedPath)
                || isset($parts['user'])
                || isset($parts['pass'])
                || (isset($parts['port']) && $parts['port'] !== 443)
            ) {
                throw new RuntimeException('La URL de imagen AVIF no pertenece al almacenamiento permitido.');
            }

            $imageUrl = $src;
            $imageData = '';
            $curl = curl_init($imageUrl);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_WRITEFUNCTION => function ($curl, string $chunk) use (&$imageData): int {
                    if (strlen($imageData) + strlen($chunk) > 10 * 1024 * 1024) {
                        return 0;
                    }
                    $imageData .= $chunk;
                    return strlen($chunk);
                },
            ]);

            $downloaded = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($downloaded === false || $status !== 200) {
                throw new RuntimeException('No se pudo cargar una imagen AVIF del contrato: ' . $curlError);
            }

            $imageInfo = getimagesizefromstring($imageData);
            if (
                $imageInfo === false
                || $imageInfo[0] * $imageInfo[1] > 20000000
                || !function_exists('imagecreatefromavif')
            ) {
                throw new RuntimeException('No se pudo procesar una imagen AVIF del contrato.');
            }

            $image = imagecreatefromstring($imageData);
            if ($image === false) {
                throw new RuntimeException('No se pudo decodificar una imagen AVIF del contrato.');
            }

            ob_start();
            $converted = imagejpeg($image, null, 90);
            $jpegData = ob_get_clean();
            imagedestroy($image);

            if (!$converted || $jpegData === false) {
                throw new RuntimeException('No se pudo convertir una imagen AVIF del contrato.');
            }

            $replacement = 'data:image/jpeg;base64,' . base64_encode($jpegData);
            $srcOffset = strpos($tagMatch[0], $srcMatch[0]) + strpos($srcMatch[0], $srcMatch[1]) + 1;
            return substr_replace($tagMatch[0], $replacement, $srcOffset, strlen($srcMatch[2]));
        },
        $html
    );
}

$contenidoHtml = convertAvifImagesToJpeg($data->contrato, URL_IMAGES);
$contenido ="
<style>
  body {
    width: 100%;
    margin: 0;
    padding: 0;
    font-size: 10pt; /* Los puntos son más precisos para impresión que los px */
  }
  
  h1 {
    font-size: 10pt;
  }
</style>
".$contenidoHtml;

// https://pub-546a2d9d368f4b87a457962de1d77955.r2.dev/1001/products_images/thumbnails/file_69eae008ab6b9.avif

$token = $data->token;

$options = new Options();
    $options->set('isRemoteEnabled', true); // Vital para cargar el LOGO desde la URL
    $options->set('isHtml5ParserEnabled', true);    
    $options->set('isPhpEnabled', true);
    $options->set('chroot', __DIR__);
    $options->set('defaultFont', 'Helvetica');
    $options->set('defaultDpi', 200); //

$dompdf = new Dompdf($options);

$dompdf->loadHtml($contenido);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfOutput = $dompdf->output();
file_put_contents($token.".pdf", $pdfOutput);

$data = json_encode([
    "UUID" => $token,
    "PDF" => base64_encode($pdfOutput)
]);

$api_url = URL_API."sendbook";
$data = json_decode(API($jwt,$api_url,$data,'POST'), true);  

http_response_code(200);
echo json_encode(array(
    "data" => 'Ok',
    "document" => $token.".pdf",
    "UUID" => $token
));

?>