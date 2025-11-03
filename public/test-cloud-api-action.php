<?php
/**
 * Backend para el test de Shelly Cloud API
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// Establecer header JSON
header('Content-Type: application/json');

// Obtener acción
$action = $_GET['action'] ?? '';

// Validar acción
if (!in_array($action, ['status', 'open', 'close'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Acción no válida'
    ]);
    exit;
}

try {
    // Ejecutar acción
    switch ($action) {
        case 'status':
            $result = ShellyAPI::getStatus();
            break;
        
        case 'open':
            $result = ShellyAPI::openBarrier();
            break;
        
        case 'close':
            $result = ShellyAPI::closeBarrier();
            break;
    }
    
    // Retornar resultado
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
