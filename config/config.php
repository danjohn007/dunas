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

// Configuración de Shelly Relay API con Port Forwarding
// IMPORTANTE: Asegúrate de que tu router tenga port forwarding configurado:
// Puerto público 80 -> IP local 192.168.1.95:80
function getShellyPublicIP() {
    // Lista de servicios para obtener IP pública
    $services = [
        'https://api.ipify.org',
        'https://icanhazip.com',
        'https://ipecho.net/plain'
    ];
    
    foreach ($services as $service) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $service);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyAPI/1.0');
        
        $ip = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if (!$error && $ip && filter_var(trim($ip), FILTER_VALIDATE_IP)) {
            return trim($ip);
        }
    }
    
    // Fallback a IP local si no se puede obtener IP pública
    return '192.168.1.95';
}

// Configuración de Shelly Pro 4PM con credenciales confirmadas
// SOLUCIÓN JAVASCRIPT: Control desde el navegador del cliente
// El navegador del usuario SÍ está en la misma red que el Shelly

define('SHELLY_API_URL', 'http://192.168.1.95'); // IP local directa del Shelly
define('SHELLY_USERNAME', 'admin'); // Usuario confirmado
define('SHELLY_PASSWORD', '67da6c'); // Contraseña confirmada (últimos 6 dígitos Device ID)
define('SHELLY_API_TIMEOUT', 15); // Timeout para conexión
define('SHELLY_SWITCH_ID', 0);  // ID del switch para abrir/cerrar barrera
define('SHELLY_ENABLED', true); // Habilitado con credenciales

// URLs exactas que ya te funcionan con curl
// curl -u admin:67da6c "http://192.168.1.95/rpc/Switch.Set?id=0&on=false"  # ABRIR
// curl -u admin:67da6c "http://192.168.1.95/rpc/Switch.Set?id=0&on=true"   # CERRAR
define('SHELLY_OPEN_URL', "http://192.168.1.95/rpc/Switch.Set?id=0&on=false");  // Abrir barrera
define('SHELLY_CLOSE_URL', "http://192.168.1.95/rpc/Switch.Set?id=0&on=true");  // Cerrar barrera

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
