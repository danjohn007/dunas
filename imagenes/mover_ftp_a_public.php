<?php
// Carpeta origen (donde llegan las imágenes de Hikvision)
$ftp_dir = '/home2/residencial/placas/';

// Carpeta destino (fuera de public_html/dunas/public, en dunas/imagenes)
$public_dir = '/home2/residencial/public_html/dunas/imagenes/';

// Verifica que ambas carpetas existan
if (!is_dir($ftp_dir)) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'La carpeta origen no existe']));
}

if (!is_dir($public_dir)) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'La carpeta destino no existe']));
}

// Escanea archivos en la carpeta origen
$archivos = scandir($ftp_dir);
$movidos = 0;
$errores = 0;
$detalles = [];

foreach ($archivos as $archivo) {
    if ($archivo === '.' || $archivo === '..') continue;

    $origen = $ftp_dir . $archivo;
    $destino = $public_dir . $archivo;

    // Solo procesa imágenes
    if (is_file($origen) && preg_match('/\.(jpg|jpeg|png)$/i', $archivo)) {
        if (rename($origen, $destino)) {
            $movidos++;
            $detalles[] = ['archivo' => $archivo, 'status' => 'movido'];
        } else {
            $errores++;
            $detalles[] = ['archivo' => $archivo, 'status' => 'error'];
        }
    }
}

// Retorna código de éxito y resumen en JSON
http_response_code(200);
echo json_encode([
    'success' => true,
    'movidos' => $movidos,
    'errores' => $errores,
    'detalles' => $detalles
]);
?>
