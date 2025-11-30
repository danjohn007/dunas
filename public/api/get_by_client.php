<?php
/**
 * Endpoint para obtener unidades y choferes por cliente
 * 
 * GET /api/get_by_client.php?client_id=X&type=units|drivers|both
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    require_once __DIR__ . '/../../config/config.php';
    
    $db = Database::getInstance()->getConnection();
    
    $clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;
    $type = isset($_GET['type']) ? $_GET['type'] : 'both';
    
    // Validate client_id is a positive integer
    if ($clientId <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid client_id parameter. Must be a positive integer.',
            'units' => [],
            'drivers' => []
        ]);
        exit;
    }
    
    // Verify client exists
    $stmt = $db->prepare("SELECT id FROM clients WHERE id = ? AND status = 'active'");
    $stmt->execute([$clientId]);
    $clientExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$clientExists) {
        echo json_encode([
            'success' => false,
            'error' => 'Client not found or inactive.',
            'client_id' => $clientId,
            'units' => [],
            'drivers' => []
        ]);
        exit;
    }
    
    $result = [
        'success' => true,
        'client_id' => $clientId
    ];
    
    // Get units by client
    if ($type === 'units' || $type === 'both') {
        $sql = "SELECT id, plate_number, capacity_liters, brand, model 
                FROM units 
                WHERE client_id = ? AND status = 'active' 
                ORDER BY plate_number";
        $stmt = $db->prepare($sql);
        $stmt->execute([$clientId]);
        $result['units'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get drivers by client
    if ($type === 'drivers' || $type === 'both') {
        $sql = "SELECT id, full_name, license_number, phone 
                FROM drivers 
                WHERE client_id = ? AND status = 'active' 
                ORDER BY full_name";
        $stmt = $db->prepare($sql);
        $stmt->execute([$clientId]);
        $result['drivers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
