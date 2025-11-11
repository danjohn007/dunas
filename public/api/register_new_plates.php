<?php
/**
 * API Endpoint: Register New Plates (FIX ruta /dunas/Imagenes)
 * - Escanea /dunas/Imagenes (NO /public/Imagenes).
 * - Detecta archivos *_VEHICLE_DETECTION_Hik__PLATE.jpg (jpg/JPG).
 * - Extrae la placa del nombre y registra en detected_plates.
 * - Evita reprocesar por filename con processed_plate_files.
 */

require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

try {
    // 0) Resolver SIEMPRE /dunas/Imagenes como ruta de disco
    //    (coincide con la URL pública https://fix360.app/dunas/Imagenes)
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : '';
    $dir = $docRoot . '/dunas/Imagenes';

    // Fallback por si el host no expone DOCUMENT_ROOT correctamente
    if (!is_dir($dir)) {
        // intenta relativo a este archivo: ../../Imagenes (sube dos niveles desde /dunas/api/)
        $alt = realpath(__DIR__ . '/../../Imagenes');
        if ($alt && is_dir($alt)) {
            $dir = $alt;
        }
    }

    if (!is_dir($dir)) {
        throw new Exception("No existe el directorio de imágenes esperado: {$dir}");
    }

    // 1) Escanear SOLO archivos *_PLATE.jpg / *_PLATE.JPG
    //    glob con brace puede no estar disponible en algunos builds;
    //    por eso, hacemos dos globs y los unimos.
    $filesJpg = glob($dir . '/*_VEHICLE_DETECTION_Hik__PLATE.jpg', GLOB_NOSORT) ?: [];
    $filesJPG = glob($dir . '/*_VEHICLE_DETECTION_Hik__PLATE.JPG', GLOB_NOSORT) ?: [];
    $files = array_merge($filesJpg, $filesJPG);

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
        //     Ej: 20251110154755988_XYA100F_VEHICLE_DETECTION_Hik__PLATE.jpg
        //     - ts: primeros dígitos (>=14). Tomamos 14 (YmdHis)
        //     - plate: bloque al medio
        if (!preg_match('/^(?<ts>\d{14,})_(?<plate>[A-Za-z0-9]+)_VEHICLE_DETECTION_Hik__PLATE\.(jpg|JPG)$/', $filename, $m)) {
            // si no matchea, saltar silenciosamente
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
            : 'No hay placas nuevas para procesar'
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    error_log("Error register_new_plates.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
