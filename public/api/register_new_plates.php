<?php
/**
 * API Endpoint: Register New Plates
 * Escanea carpeta /public/Imagenes, detecta archivos *_PLATE.jpg,
 * extrae la placa del nombre del archivo y registra en detected_plates.
 * Evita re-procesar usando processed_plate_files.
 */

// Configuración
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

try {
    // 1) Directorio de imágenes públicas
    $dir = __DIR__ . '/../Imagenes';
    if (!is_dir($dir)) {
        // Si el directorio no existe, intentar crearlo
        if (!mkdir($dir, 0755, true)) {
            throw new Exception("No se pudo crear el directorio de imágenes: $dir");
        }
    }

    // 2) Buscar sólo archivos PLATE.jpg (la placa viene en el nombre)
    $files = glob($dir . '/*_VEHICLE_DETECTION_Hik__PLATE.jpg', GLOB_NOSORT);
    if (!$files || count($files) === 0) {
        echo json_encode([
            'success' => true, 
            'inserted' => 0, 
            'message' => 'No hay archivos PLATE nuevos'
        ]);
        exit;
    }

    // 3) Obtener conexión a base de datos
    $database = Database::getInstance();
    $db = $database->getConnection();

    // 3.1) Preparar consultas (dedupe por filename)
    $stmtExists = $db->prepare("SELECT 1 FROM processed_plate_files WHERE filename = ? LIMIT 1");
    $stmtMark = $db->prepare("INSERT INTO processed_plate_files (filename) VALUES (?)");
    $stmtInsert = $db->prepare("INSERT INTO detected_plates (plate_text, captured_at) VALUES (?, ?)");

    $inserted = 0;

    foreach ($files as $path) {
        $filename = basename($path);

        // 3.2) ¿Ya procesado?
        $stmtExists->execute([$filename]);
        if ($stmtExists->fetchColumn()) {
            continue; // ya registrado
        }

        // 3.3) Extraer placa y timestamp del nombre
        // Formato esperado: 20251110154755988_XYA100F_VEHICLE_DETECTION_Hik__PLATE.jpg
        //  - timestamp: primeros dígitos (>=14), usamos los primeros 14 (YYYYMMDDHHMMSS)
        //  - placa: bloque entre '_' inmediatamente después del timestamp
        if (!preg_match('/^(?<ts>\d{14,})_(?<plate>[A-Za-z0-9]+)_VEHICLE_DETECTION_Hik__PLATE\.jpg$/', $filename, $m)) {
            // Si no matchea, lo saltamos para no insertar ruido
            continue;
        }

        $tsRaw = $m['ts'];
        $plate = strtoupper($m['plate']); // normaliza a mayúsculas

        // 3.4) Captured_at: tomar los primeros 14 dígitos como YmdHis
        $ts14 = substr($tsRaw, 0, 14);
        $dt = DateTime::createFromFormat('YmdHis', $ts14);
        $capturedAt = $dt ? $dt->format('Y-m-d H:i:s') : date('Y-m-d H:i:s', filemtime($path)); // fallback mtime

        // 3.5) Insertar en detected_plates (solo plate_text y captured_at)
        $stmtInsert->execute([$plate, $capturedAt]);

        // 3.6) Marcar filename como procesado
        $stmtMark->execute([$filename]);

        $inserted++;
    }

    echo json_encode([
        'success' => true, 
        'inserted' => $inserted,
        'message' => $inserted > 0 ? "Se registraron $inserted placas nuevas" : 'No hay placas nuevas para procesar'
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    error_log("Error en register_new_plates.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}
