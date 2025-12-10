<?php
/**
 * API endpoint para mover imágenes desde carpeta FTP a carpeta pública
 * Este script sirve como puente público para ejecutar la lógica de movimiento de imágenes
 */

// Headers para evitar caché
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');

// Cargar configuración y conexión a base de datos
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/Database.php';

// Valores por defecto
$ftp_dir = '/home2/residencial/placas/';
$public_dir = '/home2/residencial/public_html/dunas/imagenes/';

// Obtener instancia de base de datos e intentar leer configuración
try {
    $db = Database::getInstance();
    
    // Leer configuración de rutas desde la base de datos
    $ftpSourceSetting = $db->fetchOne(
        "SELECT `setting_value` FROM settings WHERE `setting_key` = 'ftp_source_dir' LIMIT 1"
    );
    $ftpDestinationSetting = $db->fetchOne(
        "SELECT `setting_value` FROM settings WHERE `setting_key` = 'ftp_destination_dir' LIMIT 1"
    );
    
    // Usar configuración de BD si existe, sino mantener valores por defecto
    if ($ftpSourceSetting && !empty($ftpSourceSetting['setting_value'])) {
        $ftp_dir = $ftpSourceSetting['setting_value'];
        error_log("FTP Config: Usando carpeta origen desde BD: " . $ftp_dir);
    } else {
        error_log("FTP Config: Usando carpeta origen por defecto: " . $ftp_dir);
    }
    
    if ($ftpDestinationSetting && !empty($ftpDestinationSetting['setting_value'])) {
        $public_dir = $ftpDestinationSetting['setting_value'];
        error_log("FTP Config: Usando carpeta destino desde BD: " . $public_dir);
    } else {
        error_log("FTP Config: Usando carpeta destino por defecto: " . $public_dir);
    }
    
} catch (Exception $e) {
    // Si hay error al conectar a la base de datos, continuar con valores por defecto
    error_log("Error al leer configuración FTP desde BD: " . $e->getMessage());
}

// Verifica que ambas carpetas existan
if (!is_dir($ftp_dir)) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'La carpeta origen no existe: ' . $ftp_dir]));
}

if (!is_dir($public_dir)) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'La carpeta destino no existe: ' . $public_dir]));
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
    'detalles' => $detalles,
    'debug' => [
        'ftp_dir' => $ftp_dir,
        'public_dir' => $public_dir,
        'usando_bd' => isset($ftpSourceSetting) && $ftpSourceSetting ? true : false
    ]
]);
