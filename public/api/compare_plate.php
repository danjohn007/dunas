<?php
/**
 * compare_plate.php (match por unidad + ventana de tiempo)
 * - Si recibe unit_id: busca la ULTIMA detección cuyo plate normalizado
 *   COINCIDA con la placa de esa unidad en los últimos X segundos.
 * - Si hay match: devuelve esa detección, marca is_match=1 y unit_id en esa fila.
 * - Si NO hay match reciente: devuelve la última detección global, sin marcar is_match.
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

    $WINDOW_SECONDS = 90; // ventana para considerar válida la coincidencia

    $database = Database::getInstance();
    $db = $database->getConnection();

    // 1) Lee unidad objetivo (id o placa directa)
    $unitId    = isset($_POST['unit_id']) ? (int)$_POST['unit_id'] : null;
    $unitPlate = null;

    if ($unitId) {
        $q = $db->prepare("SELECT plate_number FROM units WHERE id = ?");
        $q->execute([$unitId]);
        $unitPlate = $q->fetchColumn() ?: null;
    } elseif (!empty($_POST['unit_plate'])) {
        $unitPlate = (string)$_POST['unit_plate'];
    }

    // 2) Última detección global (para mostrar algo aun si no hay match)
    $lastGlobal = $db->query("SELECT id, plate_text, captured_at 
                              FROM detected_plates 
                              ORDER BY captured_at DESC, id DESC 
                              LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$lastGlobal) {
        ob_clean();
        echo json_encode(['success'=>true,'detected'=>null,'message'=>'No hay detecciones']);
        exit;
    }

    $globalDetId   = (int)$lastGlobal['id'];
    $globalDetText = $lastGlobal['plate_text'];
    $globalAt      = $lastGlobal['captured_at'];

    // Si no tenemos placa de unidad, sólo devolvemos la global
    if ($unitPlate === null) {
        ob_clean();
        echo json_encode([
            'success'     => true,
            'detected'    => $globalDetText,
            'unit_plate'  => null,
            'is_match'    => false,
            'captured_at' => $globalAt
        ]);
        exit;
    }

    $targetNorm = TextUtils::normalizePlate($unitPlate);

    // 3) Cargamos las detecciones recientes dentro de la ventana
    $recentStmt = $db->prepare("
        SELECT id, plate_text, captured_at
        FROM detected_plates
        WHERE captured_at >= (NOW() - INTERVAL ? SECOND)
        ORDER BY captured_at DESC, id DESC
        LIMIT 50
    ");
    $recentStmt->execute([$WINDOW_SECONDS]);
    $recent = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    $matched = null;
    foreach ($recent as $row) {
        $detNorm = TextUtils::normalizePlate($row['plate_text']);
        if ($detNorm !== '' && $detNorm === $targetNorm) {
            $matched = $row; // primera más reciente que coincide
            break;
        }
    }

    if ($matched) {
        // 4) Marcamos sólo la fila matcheada (no tocamos otras filas)
        $upd = $db->prepare("UPDATE detected_plates SET is_match = 1, unit_id = ? WHERE id = ?");
        $upd->execute([$unitId, (int)$matched['id']]);

        ob_clean();
        echo json_encode([
            'success'     => true,
            'detected'    => $matched['plate_text'],
            'unit_plate'  => $unitPlate,
            'is_match'    => true,
            'captured_at' => $matched['captured_at']
        ]);
        exit;
    }

    // 5) Sin match reciente → devolvemos la última global sólo como referencia
    ob_clean();
    echo json_encode([
        'success'     => true,
        'detected'    => $globalDetText,
        'unit_plate'  => $unitPlate,
        'is_match'    => false,
        'captured_at' => $globalAt,
        'message'     => 'No hay coincidencia reciente'
    ]);

} catch (Throwable $e) {
    error_log("compare_plate error: ".$e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Compare failed']);
}
