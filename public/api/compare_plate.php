<?php
/**
 * compare_plate.php — Match determinístico por placa normalizada (con diagnóstico)
 *
 * POST:
 *  - unit_id    (preferido)
 *  - unit_plate (alternativa si no hay unit_id)
 *
 * GET:
 *  - diag=1     (devuelve info extra para depurar)
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

$diag = isset($_GET['diag']);

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../../app/helpers/TextUtils.php';

    $database = Database::getInstance();
    $db       = $database->getConnection();

    $normalize = function (?string $s): string {
        $s = strtoupper(trim($s ?? ''));
        return preg_replace('/[^A-Z0-9]/', '', $s);
    };

    // 1) Leer unidad objetivo
    $unitId    = isset($_POST['unit_id']) ? (int)$_POST['unit_id'] : null;
    $unitPlate = null;

    if ($unitId) {
        $q = $db->prepare("SELECT plate_number FROM units WHERE id = ?");
        $q->execute([$unitId]);
        $unitPlate = $q->fetchColumn() ?: null;
    } elseif (!empty($_POST['unit_plate'])) {
        $unitPlate = (string)$_POST['unit_plate'];
    }

    // 2) Última global (para fallback)
    $lastGlobal = $db->query("
        SELECT id, plate_text, captured_at
        FROM detected_plates
        ORDER BY captured_at DESC, id DESC
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$lastGlobal) {
        ob_clean();
        echo json_encode(['success'=>true,'detected'=>null,'message'=>'No hay detecciones']);
        exit;
    }

    // 3) Si no hay placa de unidad, devolver global
    if ($unitPlate === null) {
        $out = [
            'success'     => true,
            'detected'    => $lastGlobal['plate_text'],
            'unit_plate'  => null,
            'is_match'    => false,
            'captured_at' => $lastGlobal['captured_at']
        ];
        if ($diag) $out['_diag'] = ['reason' => 'no-unit-plate'];
        ob_clean(); echo json_encode($out); exit;
    }

    $targetNorm = $normalize($unitPlate);

    // 4) ¿Tenemos MySQL 8?
    $ver = $db->query("SELECT VERSION()")->fetchColumn();
    $isMy8 = preg_match('/^8\./', $ver ?? '') === 1;

    // 5) Buscar coincidencia por placa normalizada
    $row = null;

    if ($isMy8) {
        $stmt = $db->prepare("
            SELECT id, plate_text, captured_at
            FROM detected_plates
            WHERE REGEXP_REPLACE(UPPER(plate_text), '[^A-Z0-9]', '') = :t
            ORDER BY captured_at DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([':t' => $targetNorm]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Fallback PHP: traer últimas 500 y filtrar
        $stmt = $db->query("
            SELECT id, plate_text, captured_at
            FROM detected_plates
            ORDER BY captured_at DESC, id DESC
            LIMIT 500
        ");
        $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($all as $r) {
            if ($normalize($r['plate_text']) === $targetNorm) {
                $row = $r; break;
            }
        }
    }

    if ($row) {
        // 6) Match → marcar esa fila
        $upd = $db->prepare("UPDATE detected_plates SET is_match = 1, unit_id = :u WHERE id = :id");
        $upd->execute([':u' => $unitId, ':id' => (int)$row['id']]);

        $out = [
            'success'     => true,
            'detected'    => $row['plate_text'],
            'unit_plate'  => $unitPlate,
            'is_match'    => true,
            'captured_at' => $row['captured_at']
        ];
        if ($diag) $out['_diag'] = [
            'mode'          => $isMy8 ? 'mysql8' : 'php-filter',
            'unit_id'       => $unitId,
            'unit_plate'    => $unitPlate,
            'targetNorm'    => $targetNorm,
            'matched_id'    => (int)$row['id'],
            'matched_norm'  => $normalize($row['plate_text']),
            'db_version'    => $ver,
        ];
        ob_clean(); echo json_encode($out); exit;
    }

    // 7) Sin match → devolver global (no tocar flags)
    $out = [
        'success'     => true,
        'detected'    => $lastGlobal['plate_text'],
        'unit_plate'  => $unitPlate,
        'is_match'    => false,
        'captured_at' => $lastGlobal['captured_at'],
        'message'     => 'No se encontró coincidencia exacta por placa normalizada'
    ];
    if ($diag) $out['_diag'] = [
        'mode'          => $isMy8 ? 'mysql8' : 'php-filter',
        'unit_id'       => $unitId,
        'unit_plate'    => $unitPlate,
        'targetNorm'    => $targetNorm,
        'db_version'    => $ver,
    ];
    ob_clean(); echo json_encode($out); exit;

} catch (Throwable $e) {
    error_log("compare_plate error: ".$e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Compare failed']);
}
