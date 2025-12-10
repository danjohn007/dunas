<?php
/**
 * API Endpoint: Register New Plates
 * - Escanea carpeta /imagenes (raíz del proyecto)
 * - Detecta archivos con formato de placa
 * - Soporta dos formatos: *_VEHICLE_DETECTION_Hik__PLATE.jpg y *_VEHICLE_DETECTION_PLATE.jpg
 * - Extrae la placa del nombre y registra en detected_plates
 * - Evita reprocesar por filename con processed_plate_files
 */

require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

try {
    // Determinar directorio de imágenes (carpeta /imagenes en la raíz del proyecto)
    $dir = realpath(__DIR__ . '/../../imagenes');
    
    // Fallback: intentar desde DOCUMENT_ROOT
    if (!$dir || !is_dir($dir)) {
        $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : '';
        $dir = $docRoot . '/dunas/imagenes';
    }

    if (!is_dir($dir)) {
        throw new Exception("No existe el directorio de imágenes esperado: {$dir}");
    }

    // Escanear archivos con ambos formatos:
    // Formato antiguo: *_VEHICLE_DETECTION_Hik__PLATE.jpg
    // Formato nuevo: *_VEHICLE_DETECTION_PLATE.jpg
    $filesOldJpg = glob($dir . '/*_VEHICLE_DETECTION_Hik__PLATE.jpg', GLOB_NOSORT) ?: [];
    $filesOldJPG = glob($dir . '/*_VEHICLE_DETECTION_Hik__PLATE.JPG', GLOB_NOSORT) ?: [];
    $filesNewJpg = glob($dir . '/*_VEHICLE_DETECTION_PLATE.jpg', GLOB_NOSORT) ?: [];
    $filesNewJPG = glob($dir . '/*_VEHICLE_DETECTION_PLATE.JPG', GLOB_NOSORT) ?: [];
    
    $files = array_merge($filesOldJpg, $filesOldJPG, $filesNewJpg, $filesNewJPG);

    if (empty($files)) {
        echo json_encode([
            'success'  => true,
            'inserted' => 0,
            'message'  => 'No hay archivos PLATE nuevos'
        ]);
        exit;
    }

    // 2) DB
    $database = Database::getInstance();
    $db = $database->getConnection();

    // 3) Preparar queries
    $stmtExists = $db->prepare("SELECT 1 FROM processed_plate_files WHERE filename = ? LIMIT 1");
    $stmtMark   = $db->prepare("INSERT INTO processed_plate_files (filename) VALUES (?)");
    $stmtInsert = $db->prepare("INSERT INTO detected_plates (plate_text, captured_at) VALUES (?, ?)");

    $inserted = 0;

    foreach ($files as $path) {
        $filename = basename($path);

        // 3.1 ¿Ya procesado este archivo?
        $stmtExists->execute([$filename]);
        if ($stmtExists->fetchColumn()) {
            continue;
        }

        // 3.2 Extraer placa y timestamp del nombre
        //     Formato antiguo: 20251110154755988_XYA100F_VEHICLE_DETECTION_Hik__PLATE.jpg
        //     Formato nuevo:   20251208135059640_XCZ138V_VEHICLE_DETECTION_PLATE.jpg
        //     - ts: primeros dígitos (>=14). Tomamos 14 (YmdHis)
        //     - plate: bloque al medio
        
        // Intentar con formato antiguo (Hik__)
        if (preg_match('/^(?<ts>\d{14,})_(?<plate>[A-Za-z0-9]+)_VEHICLE_DETECTION_Hik__PLATE\.(jpg|JPG)$/i', $filename, $m)) {
            // Formato antiguo encontrado
        } 
        // Intentar con formato nuevo (sin Hik__)
        elseif (preg_match('/^(?<ts>\d{14,})_(?<plate>[A-Za-z0-9]+)_VEHICLE_DETECTION_PLATE\.(jpg|JPG)$/i', $filename, $m)) {
            // Formato nuevo encontrado
        } 
        else {
            // No matchea ningún formato, saltar
            continue;
        }

        $tsRaw = $m['ts'];
        $plate = strtoupper($m['plate']);

        // 3.3 capturado: convertir primeros 14 dígitos a Y-m-d H:i:s; fallback a mtime
        $ts14 = substr($tsRaw, 0, 14);
        $dt   = DateTime::createFromFormat('YmdHis', $ts14);
        $capturedAt = $dt ? $dt->format('Y-m-d H:i:s') : date('Y-m-d H:i:s', @filemtime($path));

        // 3.4 Insertar
        $stmtInsert->execute([$plate, $capturedAt]);

        // 3.5 Marcar como procesado
        $stmtMark->execute([$filename]);

        $inserted++;
    }

    echo json_encode([
        'success'  => true,
        'inserted' => $inserted,
        'message'  => $inserted > 0
            ? "Se registraron {$inserted} placas nuevas"
            : 'No hay placas nuevas para procesar',
        'debug' => [
            'directory' => $dir,
            'total_files_found' => count($files),
            'directory_exists' => is_dir($dir),
            'directory_readable' => is_readable($dir)
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    error_log("Error register_new_plates.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
