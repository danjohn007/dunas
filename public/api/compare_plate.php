<?php
/**
 * compare_plate.php - API para comparar placas detectadas con unidades registradas
 * 
 * Compara la placa de una unidad seleccionada con las placas detectadas 
 * en la tabla detected_plates para encontrar coincidencias.
 */

// Headers para asegurar respuesta JSON limpia
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Configuración de errores - log pero no mostrar
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Limpiar buffer de salida
ob_start();

try {
    // Incluir archivos necesarios con manejo de errores
    $configPath = __DIR__ . '/../../config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception('Archivo de configuración no encontrado en: ' . $configPath);
    }
    require_once $configPath;
    
    // Incluir helper de base de datos
    $dbHelperPath = __DIR__ . '/../../app/helpers/Database.php';
    if (!file_exists($dbHelperPath)) {
        throw new Exception('Helper Database no encontrado en: ' . $dbHelperPath);
    }
    require_once $dbHelperPath;

    
    // Verificar método de solicitud
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Solo se permiten solicitudes POST');
    }
    
    // Obtener instancia de base de datos
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    // Función para normalizar placas
    $normalizePlate = function ($plate) {
        if (empty($plate)) return '';
        $plate = strtoupper(trim($plate));
        return preg_replace('/[^A-Z0-9]/', '', $plate);
    };

    // 1. Obtener la placa objetivo (desde unit_id o directamente por unit_plate)
    $unitId = isset($_POST['unit_id']) ? (int)$_POST['unit_id'] : null;
    $unitPlate = isset($_POST['unit_plate']) ? trim($_POST['unit_plate']) : null;

    // Si se proporciona unit_id, buscar la placa en la base de datos
    if ($unitId && !$unitPlate) {
        $stmt = $db->prepare("SELECT plate_number FROM units WHERE id = ? AND status = 'active'");
        $stmt->execute([$unitId]);
        $unit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$unit) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => 'Unidad no encontrada'
            ]);
            exit;
        }
        
        $unitPlate = $unit['plate_number'];
    }
    
    // Si no hay placa objetivo en ningún formato
    if (!$unitPlate) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => 'Placa de unidad requerida (unit_id o unit_plate)'
        ]);
        exit;
    }
    $normalizedUnitPlate = $normalizePlate($unitPlate);

    // 2. Primero: buscar detección que coincida con la placa de la unidad
    $matchingDetection = null;
    
    // Buscar todas las detecciones recientes (últimas 100) para encontrar coincidencia
    $stmt = $db->prepare("
        SELECT id, plate_text, captured_at, confidence 
        FROM detected_plates 
        ORDER BY captured_at DESC, id DESC 
        LIMIT 100
    ");
    $stmt->execute();
    $recentDetections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar la detección más reciente que coincida con la unidad
    foreach ($recentDetections as $detection) {
        $normalizedDetected = $normalizePlate($detection['plate_text']);
        if ($normalizedDetected === $normalizedUnitPlate && !empty($normalizedUnitPlate)) {
            $matchingDetection = $detection;
            break; // Tomar la más reciente que coincida
        }
    }
    
    // 3. Si encontramos coincidencia, marcar y devolver resultado positivo
    if ($matchingDetection) {
        // Marcar como procesada en la base de datos (solo si tenemos unit_id)
        if ($unitId) {
            $updateStmt = $db->prepare("
                UPDATE detected_plates 
                SET is_match = 1, unit_id = ?, status = 'processed' 
                WHERE id = ?
            ");
            $updateStmt->execute([$unitId, $matchingDetection['id']]);
        } else {
            // Si no tenemos unit_id, solo marcar como match sin asignar unidad específica
            $updateStmt = $db->prepare("
                UPDATE detected_plates 
                SET is_match = 1, status = 'processed' 
                WHERE id = ?
            ");
            $updateStmt->execute([$matchingDetection['id']]);
        }
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'detected' => $matchingDetection['plate_text'],
            'unit_plate' => $unitPlate,
            'is_match' => true,
            'captured_at' => $matchingDetection['captured_at'],
            'confidence' => $matchingDetection['confidence'],
            'normalized_detected' => $normalizePlate($matchingDetection['plate_text']),
            'normalized_unit' => $normalizedUnitPlate,
            'message' => 'Placas coinciden'
        ]);
        exit;
    }
    
    // 4. Si no hay coincidencia, mostrar mensaje "Placa no encontrada"
    $latestDetection = $recentDetections[0] ?? null;
    
    if (!$latestDetection) {
        ob_clean();
        echo json_encode([
            'success' => true,
            'detected' => 'Placa no encontrada',
            'unit_plate' => $unitPlate,
            'is_match' => false,
            'message' => 'No hay detecciones de placas recientes',
            'captured_at' => null
        ]);
        exit;
    }

    // 5. Limpiar buffer y enviar respuesta (no coincide)
    ob_clean();
    echo json_encode([
        'success' => true,
        'detected' => 'Placa no encontrada',
        'unit_plate' => $unitPlate,
        'is_match' => false,
        'captured_at' => $latestDetection['captured_at'],
        'confidence' => null,
        'normalized_detected' => 'N/A',
        'normalized_unit' => $normalizedUnitPlate,
        'message' => 'Placa de la unidad no encontrada en detecciones recientes',
        'last_detected_plate' => $latestDetection['plate_text'], // Para debugging si es necesario
        'last_detected_at' => $latestDetection['captured_at']
    ]);

} catch (PDOException $e) {
    // Error de base de datos
    error_log("compare_plate.php - Database error: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos'
    ]);
    
} catch (Exception $e) {
    // Error general
    error_log("compare_plate.php - General error: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    
} catch (Throwable $e) {
    // Error crítico
    error_log("compare_plate.php - Critical error: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor'
    ]);
}

// Finalizar buffer
exit;
?>

