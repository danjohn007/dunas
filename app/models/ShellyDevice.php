<?php
/**
 * Modelo ShellyDevice
 * Gestiona múltiples dispositivos Shelly Cloud
 */
class ShellyDevice {
    
    /**
     * Obtiene todos los dispositivos habilitados
     * @param Database $db Instancia de base de datos
     * @return array Lista de dispositivos habilitados
     */
    public static function allEnabled($db) {
        return $db->fetchAll("SELECT * FROM shelly_devices WHERE is_enabled=1 ORDER BY sort_order, id");
    }
    
    /**
     * Obtiene todos los dispositivos (habilitados y deshabilitados)
     * @param Database $db Instancia de base de datos
     * @return array Lista de todos los dispositivos
     */
    public static function getAll($db) {
        return $db->fetchAll("SELECT * FROM shelly_devices ORDER BY sort_order, id");
    }
    
    /**
     * Obtiene un dispositivo por ID
     * @param Database $db Instancia de base de datos
     * @param int $id ID del dispositivo
     * @return array|null Dispositivo o null si no existe
     */
    public static function getById($db, $id) {
        return $db->fetchOne("SELECT * FROM shelly_devices WHERE id = ?", [$id]);
    }
    
    /**
     * Actualiza múltiples dispositivos en batch (insert/update/delete)
     * @param Database $db Instancia de base de datos
     * @param array $rows Array de dispositivos con sus datos
     * @throws Exception Si ocurre un error en la transacción
     */
    public static function upsertBatch($db, $rows) {
        // $rows = [ ['id'=>?, 'name'=>?, 'auth_token'=>?, 'device_id'=>?, 'server_host'=>?, 'active_channel'=>?, 'channel_count'=>?, 'is_enabled'=>?], ... ]
        $db->beginTransaction();
        try {
            $existing = $db->fetchAll("SELECT id FROM shelly_devices");
            $existingIds = array_column($existing, 'id');

            $seen = [];
            foreach ($rows as $r) {
                $id = isset($r['id']) && $r['id'] !== '' && $r['id'] > 0 ? (int)$r['id'] : null;
                
                if ($id) {
                    $seen[] = $id;
                    // Actualizar dispositivo existente
                    $db->execute(
                        "UPDATE shelly_devices SET name=?, auth_token=?, device_id=?, server_host=?, active_channel=?, channel_count=?, is_enabled=?, updated_at=NOW() WHERE id=?",
                        [
                            $r['name'],
                            $r['auth_token'],
                            $r['device_id'],
                            $r['server_host'],
                            (int)$r['active_channel'],
                            (int)$r['channel_count'],
                            (int)$r['is_enabled'],
                            $id
                        ]
                    );
                } else {
                    // Insertar nuevo dispositivo
                    $db->execute(
                        "INSERT INTO shelly_devices (name, auth_token, device_id, server_host, active_channel, channel_count, is_enabled, sort_order) VALUES (?,?,?,?,?,?,?,?)",
                        [
                            $r['name'],
                            $r['auth_token'],
                            $r['device_id'],
                            $r['server_host'],
                            (int)$r['active_channel'],
                            (int)$r['channel_count'],
                            1,
                            (int)($r['sort_order'] ?? 0)
                        ]
                    );
                    $id = $db->lastInsertId();
                    $seen[] = $id;
                }
            }
            
            // Borrar dispositivos que ya no aparecen en la lista
            if (!empty($existingIds)) {
                $toDelete = array_diff($existingIds, $seen);
                if (!empty($toDelete)) {
                    $in = implode(',', array_fill(0, count($toDelete), '?'));
                    $db->execute("DELETE FROM shelly_devices WHERE id IN ($in)", array_values($toDelete));
                }
            }
            
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Obtiene el dispositivo por defecto para una acción específica
     * @param Database $db Instancia de base de datos
     * @param string $code Código de la acción (ej: 'abrir_cerrar')
     * @return array|null Dispositivo con acción o null si no existe
     */
    public static function getDefaultForAction($db, $code = 'abrir_cerrar') {
        // Busca en acciones cuál es default; si no, toma el primer device enabled
        $row = $db->fetchOne("
            SELECT sd.*, sa.channel AS action_channel, sa.action_kind, sa.duration_ms
            FROM shelly_devices sd
            LEFT JOIN shelly_actions sa ON sa.device_id = sd.id AND sa.code = ? AND sa.is_default=1
            WHERE sd.is_enabled=1
            ORDER BY sd.sort_order, sd.id
            LIMIT 1", [$code]);
        return $row ?: null;
    }
}
