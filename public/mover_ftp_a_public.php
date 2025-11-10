<?php
/**
 * Script para mover imágenes del FTP a /public
 * Este script busca imágenes nuevas en el servidor FTP y las copia a la carpeta pública
 */

// Configuración
define('APP_PATH', dirname(__FILE__) . '/../app');
require_once APP_PATH . '/helpers/Database.php';
require_once APP_PATH . '/models/Settings.php';

// Directorio de destino para las imágenes
$publicImagesDir = __DIR__ . '/uploads/anpr/';

// Crear directorio si no existe
if (!is_dir($publicImagesDir)) {
    mkdir($publicImagesDir, 0755, true);
}

try {
    // Obtener configuración FTP desde settings
    $settings = new Settings();
    $ftpHost = $settings->get('ftp_host');
    $ftpPort = $settings->get('ftp_port', '21');
    $ftpUser = $settings->get('ftp_user');
    $ftpPass = $settings->get('ftp_pass');
    $ftpPath = $settings->get('ftp_images_path', '/');
    
    // Verificar configuración
    if (empty($ftpHost) || empty($ftpUser)) {
        error_log('Configuración FTP incompleta. Configure los parámetros en Settings.');
        // No es un error crítico si no hay FTP configurado
        exit(0);
    }
    
    // Conectar al servidor FTP
    $ftpConn = ftp_connect($ftpHost, intval($ftpPort), 10);
    if (!$ftpConn) {
        error_log("No se pudo conectar al servidor FTP: {$ftpHost}:{$ftpPort}");
        exit(0);
    }
    
    // Autenticar
    if (!ftp_login($ftpConn, $ftpUser, $ftpPass)) {
        error_log("Error de autenticación FTP para usuario: {$ftpUser}");
        ftp_close($ftpConn);
        exit(0);
    }
    
    // Modo pasivo (útil con firewalls)
    ftp_pasv($ftpConn, true);
    
    // Cambiar al directorio de imágenes
    if (!empty($ftpPath) && !ftp_chdir($ftpConn, $ftpPath)) {
        error_log("No se pudo acceder al directorio FTP: {$ftpPath}");
        ftp_close($ftpConn);
        exit(0);
    }
    
    // Listar archivos en el directorio FTP
    $files = ftp_nlist($ftpConn, '.');
    if ($files === false) {
        error_log("No se pudo listar archivos en el directorio FTP");
        ftp_close($ftpConn);
        exit(0);
    }
    
    $movedCount = 0;
    $pattern = $settings->get('ftp_image_pattern', '_PLACA_.*VEHICLE\.jpg$');
    
    foreach ($files as $file) {
        // Filtrar solo imágenes del patrón esperado (ej: *_PLACA_*VEHICLE.jpg)
        if (preg_match("/{$pattern}/i", $file)) {
            $localFile = $publicImagesDir . basename($file);
            
            // Solo descargar si no existe localmente
            if (!file_exists($localFile)) {
                if (ftp_get($ftpConn, $localFile, $file, FTP_BINARY)) {
                    $movedCount++;
                    error_log("Imagen movida: {$file} -> {$localFile}");
                } else {
                    error_log("Error al descargar: {$file}");
                }
            }
        }
    }
    
    // Cerrar conexión FTP
    ftp_close($ftpConn);
    
    error_log("Script mover_ftp_a_public.php completado. {$movedCount} imágenes movidas.");
    
} catch (Exception $e) {
    error_log("Error en mover_ftp_a_public.php: " . $e->getMessage());
}
