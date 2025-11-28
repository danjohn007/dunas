<?php
/**
 * API Endpoint: Get Latest Detected Plate
 * Returns the most recently detected license plate from the detected_plates table
 */

// Allow from same origin only
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Load configuration
require_once __DIR__ . '/../../config/config.php';
require_once APP_PATH . '/helpers/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get the most recent detected plate from the last 10 minutes
    $stmt = $db->prepare("
        SELECT plate_text, captured_at, confidence 
        FROM detected_plates 
        WHERE captured_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ORDER BY captured_at DESC, id DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'plate' => $result['plate_text'],
            'captured_at' => $result['captured_at'],
            'confidence' => $result['confidence']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No recent plate detections',
            'plate' => null
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error in get_latest_plate.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor'
    ]);
}
