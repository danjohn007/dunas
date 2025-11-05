<?php
/**
 * Modelo ShellyAction
 * Gestiona las acciones asociadas a dispositivos Shelly
 */
class ShellyAction {
    
    /**
     * Obtiene todas las acciones de un dispositivo
     * @param Database $db Instancia de base de datos
     * @param int $deviceId ID del dispositivo
     * @return array Lista de acciones del dispositivo
     */
    public static function getByDevice($db, $deviceId) {
        return $db->fetchAll(
            "SELECT * FROM shelly_actions WHERE device_id = ? ORDER BY is_default DESC, id",
            [$deviceId]
        );
    }
    
    /**
     * Actualiza las acciones de un dispositivo (borra y recrea)
     * @param Database $db Instancia de base de datos
     * @param int $deviceId ID del dispositivo
     * @param array $actions Array de acciones
     */
    public static function upsertForDevice($db, $deviceId, $actions) {
        // acciones enviadas desde UI para ese device (por ahora mínimo una: abrir_cerrar/vacio)
        // Estructura: [['id'=>?, 'code'=>'abrir_cerrar','label'=>'Abrir/Cerrar','action_kind'=>'toggle','channel'=>0,'duration_ms'=>null,'is_default'=>1], ...]
        // Simplificar: borrar y reinsertar por ahora
        $db->execute("DELETE FROM shelly_actions WHERE device_id=?", [$deviceId]);
        foreach ($actions as $a) {
            $db->execute(
                "INSERT INTO shelly_actions (device_id, code, label, action_kind, channel, duration_ms, is_default)
                 VALUES (?,?,?,?,?,?,?)",
                [
                    $deviceId,
                    $a['code'],
                    $a['label'],
                    $a['action_kind'],
                    (int)$a['channel'],
                    $a['duration_ms'],
                    (int)$a['is_default']
                ]
            );
        }
    }
    
    /**
     * Resuelve una acción por su código y devuelve el dispositivo y configuración
     * @param Database $db Instancia de base de datos
     * @param string $code Código de la acción (ej: 'abrir_cerrar')
     * @return array|null Configuración completa de la acción o null
     */
    public static function resolve($db, $code = 'abrir_cerrar') {
        return $db->fetchOne("
            SELECT sd.*, sa.code, sa.label, sa.action_kind, sa.channel AS action_channel, sa.duration_ms
            FROM shelly_devices sd
            JOIN shelly_actions sa ON sa.device_id=sd.id
            WHERE sd.is_enabled=1 AND sa.code=? AND sa.is_default=1
            ORDER BY sd.sort_order, sd.id
            LIMIT 1", [$code]);
    }
    
    /**
     * Obtiene todas las acciones por defecto (una por código de acción)
     * @param Database $db Instancia de base de datos
     * @return array Lista de acciones por defecto
     */
    public static function getAllDefaults($db) {
        return $db->fetchAll("
            SELECT sa.*, sd.name as device_name
            FROM shelly_actions sa
            JOIN shelly_devices sd ON sd.id = sa.device_id
            WHERE sa.is_default = 1 AND sd.is_enabled = 1
            ORDER BY sa.code
        ");
    }
    
    /**
     * Resuelve todos los dispositivos que tienen una acción específica
     * @param Database $db Instancia de base de datos
     * @param string $code Código de la acción (ej: 'abrir_cerrar')
     * @return array Lista de dispositivos con la acción
     */
    public static function resolveAllByAction($db, $code) {
        return $db->fetchAll("
            SELECT
                sd.*,
                sa.code, sa.label, sa.action_kind, sa.channel AS action_channel, sa.duration_ms
            FROM shelly_devices sd
            JOIN shelly_actions sa ON sa.device_id = sd.id
            WHERE sd.is_enabled=1
                AND sa.code = ?
            ORDER BY sd.sort_order, sd.id
        ", [$code]);
    }
}
