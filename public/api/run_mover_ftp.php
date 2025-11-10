<?php
// public/api/run_mover_ftp.php
// Ejecuta el script que mueve las imágenes del FTP a /public y devuelve JSON.

header('Content-Type: application/json');

// Configuración
define('APP_PATH', dirname(__FILE__) . '/../../app');
define('BASE_URL', '');

require_once APP_PATH . '/helpers/Auth.php';
require_once APP_PATH . '/helpers/Session.php';

// Iniciar sesión
Session::start();

try {
    // Verificar autenticación
    if (!Auth::isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'No autenticado'
        ]);
        exit;
    }
    
    // Verificar rol (admin, supervisor u operador)
    $userRole = $_SESSION['user']['role'] ?? null;
    if (!in_array($userRole, ['admin', 'supervisor', 'operator'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Acceso denegado'
        ]);
        exit;
    }
    
    // Ubicación del script existente
    $script = __DIR__ . '/../mover_ftp_a_public.php';
    if (!file_exists($script)) {
        throw new Exception('No se encontró mover_ftp_a_public.php');
    }

    // Ejecuta el script sin romper la salida JSON
    ob_start();
    include $script;
    ob_end_clean();

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
