<?php
/**
 * API Endpoint: Latest ANPR Detection
 * Retorna la última detección de placa vehicular
 */

// Configuración
define('APP_PATH', dirname(__FILE__) . '/../../../app');
define('BASE_URL', '');

require_once APP_PATH . '/helpers/Auth.php';
require_once APP_PATH . '/helpers/Session.php';
require_once APP_PATH . '/services/HikvisionAnprService.php';

// Iniciar sesión
Session::start();

// Establecer headers JSON
header('Content-Type: application/json');

try {
    // Verificar autenticación (admin u operador)
    if (!Auth::isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'No autenticado'
        ]);
        exit;
    }
    
    // Verificar rol
    $userRole = $_SESSION['user']['role'] ?? null;
    if (!in_array($userRole, ['admin', 'supervisor', 'operator'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Acceso denegado'
        ]);
        exit;
    }
    
    // Crear instancia del servicio
    $anprService = new HikvisionAnprService();
    
    // Obtener última detección
    $result = $anprService->getLatestDetection();
    
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $result['error']
        ]);
        exit;
    }
    
    // Formatear respuesta
    $response = [
        'success' => true,
        'detection' => null
    ];
    
    if ($result['detection']) {
        $detection = $result['detection'];
        $response['detection'] = [
            'plate_text' => $detection['plate_text'],
            'original_plate' => $detection['original_plate'] ?? $detection['plate_text'],
            'confidence' => $detection['confidence'],
            'is_match' => $detection['is_match'],
            'unit' => $detection['unit'] ? [
                'id' => $detection['unit']['id'],
                'plate_number' => $detection['unit']['plate_number'],
                'brand' => $detection['unit']['brand'],
                'model' => $detection['unit']['model'],
                'capacity_liters' => $detection['unit']['capacity_liters']
            ] : null
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en API ANPR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor'
    ]);
}
