<?php
/**
 * compare_plate.php — Match determinístico por placa normalizada (sin ventana de tiempo).
 * - Si recibe unit_id: toma plate_number de esa unidad.
 * - Si recibe unit_plate: usa esa placa.
 * - Busca en detected_plates la última fila cuyo plate_text normalizado == placa objetivo normalizada.
 * - Si hay, marca is_match=1 y unit_id en ESA fila y la devuelve.
 * - Si no hay, devuelve la última global con is_match=false (no toca otras filas).
 *
 * Depende de MySQL 8+ para REGEXP_REPLACE; si no tienes 8+, ver notas al final.
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0'); // evita HTML en la salida
error_reporting(E_ALL);
ob_start();

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../../app/helpers/TextUtils.php';

    $database = Database::getInstance();
    $db       = $database->getConnection();

    // 0) Normalizador en PHP (por si la BD no es 8.0)
    $phpNormalize = function (?string $s) {
        $s = strtoupper(trim($s ?? ''));
        return preg_replace('/[^A-Z0-9]/', '', $s);
    };

    // 1) Cargar placa objetivo
    $unitId    = isset($_POST['unit_id']) ? (int)$_POST['unit_id'] : null;
    $unitPlate = null;

    if ($unitId) {
        $q = $db->prepare("SELECT plate_number FROM units WHERE id = ?");
        $q->execute([$unitId]);
        $unitPlate = $q->fetchColumn() ?: null;
    } elseif (!empty($_POST['unit_plate'])) {
        $unitPlate = (string)$_POST['unit_plate'];
    }

    // Última global (para fallback)
    $lastGlobal = $db->query("SELECT id, plate_text, captured_at
                              FROM detected_plates
                              ORDER BY captured_at DESC, id DESC
                              LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$lastGlobal) {
        ob_clean();
        echo json_encode(['success'=>true,'detected'=>null,'message'=>'No hay detecciones']);
        exit;
    }

    // Si no hay placa objetivo, solo regresamos la global
    if ($unitPlate === null) {
        ob_clean();
        echo json_encode([
            'success'     => true,
            'detected'    => $lastGlobal['plate_text'],
            'unit_plate'  => null,
            'is_match'    => false,
            'captured_at' => $lastGlobal['captured_at']
        ]);
        exit;
    }

    $targetNorm = $phpNormalize($unitPlate);

    // 2) Buscar última detección cuya placa normalizada == targetNorm
    //    Opción MySQL 8+ con REGEXP_REPLACE (quita todo lo no alfanumérico):
    $sql = "
        SELECT id, plate_text, captured_at
        FROM detected_plates
        WHERE REGEXP_REPLACE(UPPER(plate_text), '[^A-Z0-9]', '') = :targetNorm
        ORDER BY captured_at DESC, id DESC
        LIMIT 1
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':targetNorm' => $targetNorm]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // 3) Hay match → marcar solo ESA fila
        $upd = $db->prepare("UPDATE detected_plates SET is_match = 1, unit_id = :unitId WHERE id = :id");
        $upd->execute([':unitId' => $unitId, ':id' => (int)$row['id']]);

        ob_clean();
        echo json_encode([
            'success'     => true,
            'detected'    => $row['plate_text'],
            'unit_plate'  => $unitPlate,
            'is_match'    => true,
            'captured_at' => $row['captured_at']
        ]);
        exit;
    }

    // 4) No hay match → devolver la global, sin tocar flags
    ob_clean();
    echo json_encode([
        'success'     => true,
        'detected'    => $lastGlobal['plate_text'],
        'unit_plate'  => $unitPlate,
        'is_match'    => false,
        'captured_at' => $lastGlobal['captured_at'],
        'message'     => 'No se encontró coincidencia exacta por placa normalizada'
    ]);

} catch (Throwable $e) {
    error_log("compare_plate error: ".$e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Compare failed']);
}
