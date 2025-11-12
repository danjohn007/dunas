<?php
/**
 * Endpoint para obtener unidades disponibles
 */
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/config.php';
    
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->query("SELECT id, plate_number, brand, model FROM units WHERE status = 'active' ORDER BY plate_number");
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($units);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>