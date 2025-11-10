<?php
/**
 * Script para mover imágenes desde FTP a carpeta pública
 * Este script se ejecuta cuando el usuario presiona "Detectar Placa Nuevamente"
 * 
 * Funcionalidad:
 * - Conecta al servidor FTP configurado
 * - Descarga las imágenes más recientes de detección de placas
 * - Las guarda en la carpeta public/uploads/hikvision/
 */

// Configuración FTP
// Nota: Estos valores deben ser configurados según el servidor FTP de Hikvision
define('FTP_HOST', 'localhost');
define('FTP_PORT', 21);
define('FTP_USER', 'hikvision');
define('FTP_PASS', '');
define('FTP_REMOTE_PATH', '/anpr/');
define('FTP_ENABLED', false); // Cambiar a true cuando el FTP esté configurado

// Directorio local de destino
$targetDir = dirname(__DIR__) . '/uploads/hikvision/';

// Asegurar que el directorio existe
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Si FTP no está habilitado, retornar éxito sin hacer nada
if (!FTP_ENABLED) {
    // En modo deshabilitado, simplemente retornamos éxito
    // Las imágenes se procesarán desde la carpeta local si ya existen
    return true;
}

// Intentar conexión FTP
$ftpConn = @ftp_connect(FTP_HOST, FTP_PORT, 10);

if (!$ftpConn) {
    error_log("Error al conectar al servidor FTP: " . FTP_HOST);
    return false;
}

// Login al FTP
if (!@ftp_login($ftpConn, FTP_USER, FTP_PASS)) {
    error_log("Error al autenticar en el servidor FTP");
    ftp_close($ftpConn);
    return false;
}

// Modo pasivo
ftp_pasv($ftpConn, true);

// Cambiar al directorio remoto
if (!@ftp_chdir($ftpConn, FTP_REMOTE_PATH)) {
    error_log("Error al cambiar al directorio FTP: " . FTP_REMOTE_PATH);
    ftp_close($ftpConn);
    return false;
}

// Listar archivos en el directorio remoto
$fileList = ftp_nlist($ftpConn, '.');

if ($fileList === false) {
    error_log("Error al listar archivos del FTP");
    ftp_close($ftpConn);
    return false;
}

// Filtrar solo archivos de tipo VEHICLE_DETECTION
$vehicleFiles = array_filter($fileList, function($file) {
    return strpos($file, '_VEHICLE_DETECTION_') !== false && 
           (strpos($file, '.jpg') !== false || strpos($file, '.jpeg') !== false);
});

// Ordenar por nombre (que incluye timestamp) de más reciente a más antiguo
rsort($vehicleFiles);

// Descargar hasta 10 archivos más recientes
$downloadedCount = 0;
$maxFiles = 10;

foreach ($vehicleFiles as $file) {
    if ($downloadedCount >= $maxFiles) {
        break;
    }
    
    $localFile = $targetDir . basename($file);
    
    // Solo descargar si no existe localmente
    if (!file_exists($localFile)) {
        if (ftp_get($ftpConn, $localFile, $file, FTP_BINARY)) {
            $downloadedCount++;
        } else {
            error_log("Error al descargar archivo: $file");
        }
    }
}

// Cerrar conexión FTP
ftp_close($ftpConn);

return true;
