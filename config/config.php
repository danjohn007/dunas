<?php
/**
 * Sistema de Control de Acceso con IoT
 * Configuración del Sistema
 */

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores (cambiar a 0 en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'fix360_dunas');
define('DB_USER', 'fix360_dunas');
define('DB_PASS', 'Danjohn007!');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Control de Acceso con IoT');
define('APP_VERSION', '1.0.0');

// Detección automática de URL base
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseUrl = $protocol . $host . str_replace(basename($scriptName), '', $scriptName);
    return rtrim($baseUrl, '/');
}

define('BASE_URL', getBaseUrl());

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si se usa HTTPS

// Configuración de Shelly Relay API - IP Local LAN
define('SHELLY_API_URL', 'http://192.168.1.95/'); // IP local del Shelly en la red
define('SHELLY_API_TIMEOUT', 15); // Timeout aumentado para conexión externa
define('SHELLY_SWITCH_ID', 0);  // ID del switch para abrir/cerrar barrera
define('SHELLY_ENABLED', true); // Habilitado
// URLs completas para las acciones
define('SHELLY_OPEN_URL', 'http://192.168.1.95/rpc/Switch.Set?id=0&on=false');  // Abrir (OFF)
define('SHELLY_CLOSE_URL', 'http://192.168.1.95/rpc/Switch.Set?id=0&on=true');  // Cerrar (ON)

// Configuración de archivos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Constantes del sistema
define('ITEMS_PER_PAGE', 10);
define('SESSION_TIMEOUT', 3600); // 1 hora

// Incluir autoloader
require_once ROOT_PATH . '/app/helpers/Database.php';
require_once ROOT_PATH . '/app/helpers/Auth.php';
require_once ROOT_PATH . '/app/helpers/Session.php';
require_once ROOT_PATH . '/app/helpers/Validator.php';
require_once ROOT_PATH . '/app/helpers/FileUpload.php';
require_once ROOT_PATH . '/app/helpers/ShellyAPI.php';
