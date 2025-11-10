<?php
/**
 * API Endpoint: Detect Plate
 * Ejecuta el script FTP, lee imágenes, extrae placas y las registra
 */

// Configuración
define('APP_PATH', dirname(__FILE__) . '/../../app');
define('BASE_URL', '');

require_once APP_PATH . '/helpers/Auth.php';
require_once APP_PATH . '/helpers/Session.php';
require_once APP_PATH . '/helpers/Database.php';
require_once APP_PATH . '/helpers/TextUtils.php';
require_once APP_PATH . '/models/DetectedPlates.php';

// Iniciar sesión
Session::start();

// Establecer headers JSON
header('Content-Type: application/json');

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
    
    // 1️⃣ Ejecutar el script mover_ftp_a_public.php
    $scriptPath = __DIR__ . '/mover_ftp_a_public.php';
    if (!file_exists($scriptPath)) {
        throw new Exception("No se encontró el script mover_ftp_a_public.php");
    }
    
    // Ejecutar el script en modo silencioso
    ob_start();
    $moveResult = include $scriptPath;
    ob_end_clean();
    
    // 2️⃣ Leer carpeta de destino
    $targetDir = dirname(__DIR__) . '/uploads/hikvision/';
    
    // Asegurar que el directorio existe
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Buscar archivos de tipo VEHICLE_DETECTION
    $pattern = $targetDir . '*_VEHICLE_DETECTION_*.jpg';
    $files = glob($pattern);
    
    // También buscar archivos con extensión jpeg
    $patternJpeg = $targetDir . '*_VEHICLE_DETECTION_*.jpeg';
    $filesJpeg = glob($patternJpeg);
    
    // Combinar ambos resultados
    $files = array_merge($files ?: [], $filesJpeg ?: []);
    
    if (!$files || count($files) === 0) {
        throw new Exception("No se encontraron imágenes recientes en la carpeta de detección");
    }
    
    // 3️⃣ Obtener la imagen más reciente (ordenar por tiempo de modificación)
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    $latestFile = basename($files[0]);
    
    // 4️⃣ Extraer placa del nombre del archivo
    // Formato esperado: 20251110134110161_SIS987P_VEHICLE_DETECTION_Hik__VEHICLE.jpg
    // Patrón: buscar texto entre guiones bajos antes de _VEHICLE_DETECTION
    if (!preg_match('/[_\/]([A-Z0-9]+)_VEHICLE_DETECTION/i', $latestFile, $matches)) {
        throw new Exception("No se pudo extraer la placa del nombre de archivo: $latestFile");
    }
    
    $plate = strtoupper($matches[1]);
    
    // 5️⃣ Normalizar placa y buscar coincidencia con units.plate_number
    $db = Database::getInstance();
    $normalized = TextUtils::normalizePlate($plate);
    
    // Buscar unidad coincidente
    $matchedUnit = DetectedPlates::findUnitByPlate($db, $normalized);
    
    // 6️⃣ Insertar en detected_plates
    $detectionId = DetectedPlates::insert(
        $db,
        $plate,
        $matchedUnit ? $matchedUnit['id'] : null,
        null, // confidence - no disponible desde nombre de archivo
        null  // device_id - no disponible en este contexto
    );
    
    // 7️⃣ Respuesta JSON
    echo json_encode([
        'success' => true,
        'plate_detected' => $plate,
        'is_match' => $matchedUnit ? true : false,
        'matched_unit_id' => $matchedUnit ? $matchedUnit['id'] : null,
        'matched_unit' => $matchedUnit ? [
            'id' => $matchedUnit['id'],
            'plate_number' => $matchedUnit['plate_number'],
            'brand' => $matchedUnit['brand'],
            'model' => $matchedUnit['model']
        ] : null,
        'detection_id' => $detectionId,
        'image_file' => $latestFile
    ]);
    
} catch (Throwable $e) {
    error_log("Error en detect_plate.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
