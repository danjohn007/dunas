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
                
                // Prepare common values for both insert and update
                $baseParams = self::prepareDeviceParams($r);
                $actionParams = self::prepareActionParams($r);
                
                if ($id) {
                    $seen[] = $id;
                    // Actualizar dispositivo existente
                    $allParams = array_merge($baseParams, $actionParams, [$id]);
                    $db->execute(
                        "UPDATE shelly_devices SET 
                            name=?, auth_token=?, device_id=?, server_host=?, area=?, 
                            active_channel=?, entry_channel=?, exit_channel=?, pulse_duration_ms=?, 
                            channel_count=?, invert_sequence=?, is_simultaneous=?, is_enabled=?,
                            quick_register_channel=?, quick_register_action=?, quick_register_pulse_enabled=?, quick_register_pulse_ms=?,
                            exit_register_channel=?, exit_register_action=?, exit_register_pulse_enabled=?, exit_register_pulse_ms=?,
                            new_access_channel=?, new_access_action=?, new_access_pulse_enabled=?, new_access_pulse_ms=?,
                            updated_at=NOW() 
                        WHERE id=?",
                        $allParams
                    );
                } else {
                    // Insertar nuevo dispositivo
                    $allParams = array_merge($baseParams, [(int)($r['sort_order'] ?? 0)], $actionParams);
                    $db->execute(
                        "INSERT INTO shelly_devices (
                            name, auth_token, device_id, server_host, area, 
                            active_channel, entry_channel, exit_channel, pulse_duration_ms, 
                            channel_count, invert_sequence, is_simultaneous, is_enabled, sort_order,
                            quick_register_channel, quick_register_action, quick_register_pulse_enabled, quick_register_pulse_ms,
                            exit_register_channel, exit_register_action, exit_register_pulse_enabled, exit_register_pulse_ms,
                            new_access_channel, new_access_action, new_access_pulse_enabled, new_access_pulse_ms
                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                        $allParams
                    );
                    $id = $db->lastInsertId();
                    $seen[] = $id;
                }
            }
            
            // Borrar dispositivos que ya no aparecen en la lista
            // Solo borrar si hay elementos en $seen (para prevenir borrado masivo accidental)
            if (!empty($existingIds) && !empty($rows)) {
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
     * Prepares base device parameters for insert/update
     * @param array $r Device row data
     * @return array Array of parameters for base device fields
     */
    private static function prepareDeviceParams($r) {
        return [
            $r['name'],
            $r['auth_token'],
            $r['device_id'],
            $r['server_host'],
            $r['area'],
            (int)$r['active_channel'],
            (int)($r['entry_channel'] ?? 0),
            (int)($r['exit_channel'] ?? 1),
            (int)($r['pulse_duration_ms'] ?? 5000),
            (int)$r['channel_count'],
            isset($r['invert_sequence']) ? (int)$r['invert_sequence'] : 1,
            isset($r['is_simultaneous']) ? (int)$r['is_simultaneous'] : 0,
            (int)$r['is_enabled']
        ];
    }
    
    /**
     * Prepares action-specific channel parameters
     * @param array $r Device row data
     * @return array Array of parameters for action channel fields
     */
    private static function prepareActionParams($r) {
        $pulseDuration = (int)($r['pulse_duration_ms'] ?? 5000);
        $entryChannel = (int)($r['entry_channel'] ?? 0);
        $exitChannel = (int)($r['exit_channel'] ?? 1);
        
        return [
            // Quick register
            (int)($r['quick_register_channel'] ?? $entryChannel),
            ($r['quick_register_action'] ?? 'open'),
            isset($r['quick_register_pulse_enabled']) ? 1 : 0,
            (int)($r['quick_register_pulse_ms'] ?? $pulseDuration),
            // Exit register  
            (int)($r['exit_register_channel'] ?? $exitChannel),
            ($r['exit_register_action'] ?? 'close'),
            isset($r['exit_register_pulse_enabled']) ? 1 : 0,
            (int)($r['exit_register_pulse_ms'] ?? $pulseDuration),
            // New access
            (int)($r['new_access_channel'] ?? $entryChannel),
            ($r['new_access_action'] ?? 'open'),
            isset($r['new_access_pulse_enabled']) ? 1 : 0,
            (int)($r['new_access_pulse_ms'] ?? $pulseDuration)
        ];
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
    
    /**
     * Obtiene la configuración del dispositivo para un tipo de acción específico
     * @param Database $db Instancia de base de datos
     * @param string $actionType Tipo de acción: 'quick_register', 'exit_register', 'new_access'
     * @return array|null Configuración del dispositivo con campos específicos para la acción
     */
    public static function getForActionType($db, $actionType) {
        $device = $db->fetchOne("
            SELECT * FROM shelly_devices 
            WHERE is_enabled = 1 
            ORDER BY sort_order, id 
            LIMIT 1
        ");
        
        if (!$device) {
            return null;
        }
        
        // Map the action type to the specific channel configuration
        $channelField = null;
        $actionField = null;
        $pulseEnabledField = null;
        $pulseMsField = null;
        
        switch ($actionType) {
            case 'quick_register':
                $channelField = 'quick_register_channel';
                $actionField = 'quick_register_action';
                $pulseEnabledField = 'quick_register_pulse_enabled';
                $pulseMsField = 'quick_register_pulse_ms';
                break;
            case 'exit_register':
                $channelField = 'exit_register_channel';
                $actionField = 'exit_register_action';
                $pulseEnabledField = 'exit_register_pulse_enabled';
                $pulseMsField = 'exit_register_pulse_ms';
                break;
            case 'new_access':
                $channelField = 'new_access_channel';
                $actionField = 'new_access_action';
                $pulseEnabledField = 'new_access_pulse_enabled';
                $pulseMsField = 'new_access_pulse_ms';
                break;
            default:
                // Fallback to legacy entry_channel for 'open' actions
                $channelField = 'entry_channel';
                $actionField = null;
                $pulseEnabledField = null;
                $pulseMsField = 'pulse_duration_ms';
        }
        
        // Build the configuration array
        $config = $device;
        $config['action_channel'] = isset($device[$channelField]) ? (int)$device[$channelField] : 0;
        $config['action_mode'] = isset($device[$actionField]) ? $device[$actionField] : 'open';
        $config['pulse_enabled'] = isset($device[$pulseEnabledField]) ? (bool)$device[$pulseEnabledField] : true;
        $config['pulse_ms'] = isset($device[$pulseMsField]) ? (int)$device[$pulseMsField] : 5000;
        
        return $config;
    }
    
    /**
     * Obtiene todos los dispositivos habilitados configurados para un tipo de acción
     * @param Database $db Instancia de base de datos
     * @param string $actionType Tipo de acción: 'quick_register', 'exit_register', 'new_access'
     * @return array Lista de dispositivos con su configuración para la acción
     */
    public static function getAllForActionType($db, $actionType) {
        $devices = $db->fetchAll("
            SELECT * FROM shelly_devices 
            WHERE is_enabled = 1 
            ORDER BY sort_order, id
        ");
        
        if (!$devices) {
            return [];
        }
        
        $result = [];
        foreach ($devices as $device) {
            $channelField = null;
            $actionField = null;
            $pulseEnabledField = null;
            $pulseMsField = null;
            
            switch ($actionType) {
                case 'quick_register':
                    $channelField = 'quick_register_channel';
                    $actionField = 'quick_register_action';
                    $pulseEnabledField = 'quick_register_pulse_enabled';
                    $pulseMsField = 'quick_register_pulse_ms';
                    break;
                case 'exit_register':
                    $channelField = 'exit_register_channel';
                    $actionField = 'exit_register_action';
                    $pulseEnabledField = 'exit_register_pulse_enabled';
                    $pulseMsField = 'exit_register_pulse_ms';
                    break;
                case 'new_access':
                    $channelField = 'new_access_channel';
                    $actionField = 'new_access_action';
                    $pulseEnabledField = 'new_access_pulse_enabled';
                    $pulseMsField = 'new_access_pulse_ms';
                    break;
                default:
                    $channelField = 'entry_channel';
                    $actionField = null;
                    $pulseEnabledField = null;
                    $pulseMsField = 'pulse_duration_ms';
            }
            
            $config = $device;
            $config['action_channel'] = isset($device[$channelField]) ? (int)$device[$channelField] : 0;
            $config['action_mode'] = isset($device[$actionField]) ? $device[$actionField] : 'open';
            $config['pulse_enabled'] = isset($device[$pulseEnabledField]) ? (bool)$device[$pulseEnabledField] : true;
            $config['pulse_ms'] = isset($device[$pulseMsField]) ? (int)$device[$pulseMsField] : 5000;
            
            $result[] = $config;
        }
        
        return $result;
    }
}
