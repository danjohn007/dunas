<?php
/**
 * compare_plate.php (robusto)
 * Devuelve última placa detectada y compara con unit_id o unit_plate.
 * Respuesta JSON **siempre**, sin HTML.
 */
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0'); // no imprimir warnings/notice al output
error_reporting(E_ALL);

ob_start(); // buffer por si algo escribe antes

try {
    // Configuración de rutas
    define('APP_PATH', dirname(__FILE__) . '/../../app');
    define('ROOT_PATH', dirname(__FILE__) . '/../..');

    require_once ROOT_PATH . '/config/config.php';
    require_once APP_PATH . '/helpers/TextUtils.php';
    require_once APP_PATH . '/helpers/Session.php';
    require_once APP_PATH . '/helpers/Auth.php';

    // Iniciar sesión
    Session::start();

    // Verificar autenticación
    if (!Auth::isLoggedIn()) {
        ob_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No autenticado']);
        exit;
    }
    
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Última detectada
    $stmt = $db->query("SELECT id, plate_text, captured_at FROM detected_plates ORDER BY captured_at DESC, id DESC LIMIT 1");
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$last) {
        ob_clean();
        echo json_encode(['success'=>true,'detected'=>null,'message'=>'No hay detecciones']);
        exit;
    }

    $detId      = (int)$last['id'];
    $detText    = $last['plate_text'];
    $detNorm    = TextUtils::normalizePlate($detText);
    $capturedAt = $last['captured_at'];

    // Unidad: ID o texto
    $unitId    = isset($_POST['unit_id']) ? (int)$_POST['unit_id'] : null;
    $unitPlate = null;

    if ($unitId) {
        $q = $db->prepare("SELECT plate_number FROM units WHERE id = ?");
        $q->execute([$unitId]);
        $unitPlate = $q->fetchColumn() ?: null;
    } elseif (!empty($_POST['unit_plate'])) {
        $unitPlate = (string)$_POST['unit_plate'];
    }

    if ($unitPlate === null) {
        ob_clean();
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

    // Actualiza la última fila
    $upd = $db->prepare("UPDATE detected_plates SET is_match = ?, unit_id = ? WHERE id = ?");
    $upd->execute([$isMatch ? 1 : 0, $isMatch ? $unitId : null, $detId]);

    ob_clean();
    echo json_encode([
        'success'     => true,
        'detected'    => $detText,
        'unit_plate'  => $unitPlate,
        'is_match'    => $isMatch,
        'captured_at' => $capturedAt
    ]);

} catch (Throwable $e) {
    error_log("compare_plate error: ".$e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Compare failed']);
}
