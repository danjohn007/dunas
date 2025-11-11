<?php
/**
 * compare_plate.php
 * Devuelve la última placa detectada (detected_plates) y compara con:
 *  - unit_id (si lo envías)  O
 *  - unit_plate (si lo envías)
 * Actualiza detected_plates.is_match y unit_id si hay match.
 * Respuesta JSON: { success, detected, unit_plate, is_match, captured_at }
 */

// Configuración de rutas
define('APP_PATH', dirname(__FILE__) . '/../../app');
define('ROOT_PATH', dirname(__FILE__) . '/../..');

require_once ROOT_PATH . '/config/config.php';
require_once APP_PATH . '/helpers/TextUtils.php';
require_once APP_PATH . '/helpers/Session.php';
require_once APP_PATH . '/helpers/Auth.php';

// Iniciar sesión
Session::start();

header('Content-Type: application/json');

try {
    // Verificar autenticación
    if (!Auth::isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No autenticado']);
        exit;
    }
    
    $database = Database::getInstance();
    $db = $database->getConnection();

    // 1) Última placa detectada
    $stmt = $db->query("SELECT id, plate_text, captured_at FROM detected_plates ORDER BY captured_at DESC, id DESC LIMIT 1");
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$last) {
        echo json_encode(['success' => true, 'detected' => null, 'message' => 'No hay detecciones']);
        exit;
    }

    $detId   = (int)$last['id'];
    $detText = $last['plate_text'];
    $detNorm = TextUtils::normalizePlate($detText);
    $capturedAt = $last['captured_at'];

    // 2) Obtener la placa de la unidad (por unit_id o por texto)
    $unitId = isset($_POST['unit_id']) ? (int)$_POST['unit_id'] : null;
    $unitPlate = null;

    if ($unitId) {
        $q = $db->prepare("SELECT plate_number FROM units WHERE id = ?");
        $q->execute([$unitId]);
        $unitPlate = $q->fetchColumn() ?: null;
    } else if (!empty($_POST['unit_plate'])) {
        $unitPlate = $_POST['unit_plate'];
    }

    if ($unitPlate === null) {
        // No se proporcionó unidad ni placa de unidad: devolvemos solo detected
        echo json_encode([
            'success'     => true,
            'detected'    => $detText,
            'unit_plate'  => null,
            'is_match'    => false,
            'captured_at' => $capturedAt
        ]);
        exit;
    }

    $unitNorm = TextUtils::normalizePlate($unitPlate);
    $isMatch  = ($detNorm !== '' && $detNorm === $unitNorm);

    // 3) Actualizar detected_plates (solo la última fila)
    $upd = $db->prepare("UPDATE detected_plates SET is_match = ?, unit_id = ? WHERE id = ?");
    $upd->execute([$isMatch ? 1 : 0, $isMatch ? $unitId : null, $detId]);

    echo json_encode([
        'success'     => true,
        'detected'    => $detText,
        'unit_plate'  => $unitPlate,
        'is_match'    => $isMatch,
        'captured_at' => $capturedAt
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
