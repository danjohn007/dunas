<?php
/**
 * Servicio ShellyActionService
 * Ejecuta acciones en dispositivos Shelly según configuración
 */
require_once APP_PATH . '/helpers/ShellyAPI.php';
require_once APP_PATH . '/models/ShellyAction.php';

class ShellyActionService {
    
    /**
     * Ejecuta una acción Shelly según el código y modo
     * @param Database $db Instancia de base de datos
     * @param string $code Código de la acción (ej: 'abrir_cerrar')
     * @param string $mode Modo de operación: 'open' | 'close'
     * @return array Resultado de la operación
     * @throws Exception Si no hay configuración para la acción
     */
    public static function execute($db, $code, $mode) {
        // code: 'abrir_cerrar', 'abrir_puerta', etc.
        $cfg = ShellyAction::resolve($db, $code);
        
        if (!$cfg) {
            throw new Exception("Acción Shelly no configurada: $code");
        }
        
        // Usar el canal de la acción si está definido, sino el canal activo del dispositivo
        $channel = isset($cfg['action_channel']) && $cfg['action_channel'] !== null
            ? (int)$cfg['action_channel']
            : (int)$cfg['active_channel'];
        
        // Obtener el flag de inversión del dispositivo (default 1 = invertido)
        $invert = isset($cfg['invert_sequence']) ? (int)$cfg['invert_sequence'] : 1;
        
        // Crear instancia de ShellyAPI con los datos del dispositivo
        $api = new ShellyAPI($cfg['auth_token'], $cfg['device_id'], $cfg['server_host']);
        
        // Log de la configuración de inversión
        error_log("ShellyActionService::execute() - Acción: $code, Modo: $mode, Invertido: $invert, Canal: $channel");
        
        // Ejecutar según el tipo de acción
        switch ($cfg['action_kind']) {
            case 'toggle':
                // Toggle: usar el modo (open/close) y el flag de inversión
                if ($mode === 'open') {
                    if ($invert) {
                        // Invertido: off → on
                        error_log("ShellyActionService::execute() - Toggle OPEN con inversión: OFF → ON");
                        $api->relayTurnOff($channel);
                        return $api->relayTurnOn($channel);
                    } else {
                        // Normal: on → off
                        error_log("ShellyActionService::execute() - Toggle OPEN sin inversión: ON → OFF");
                        $api->relayTurnOn($channel);
                        return $api->relayTurnOff($channel);
                    }
                } else {
                    // close
                    if ($invert) {
                        // Invertido: on → off
                        error_log("ShellyActionService::execute() - Toggle CLOSE con inversión: ON → OFF");
                        $api->relayTurnOn($channel);
                        return $api->relayTurnOff($channel);
                    } else {
                        // Normal: off → on
                        error_log("ShellyActionService::execute() - Toggle CLOSE sin inversión: OFF → ON");
                        $api->relayTurnOff($channel);
                        return $api->relayTurnOn($channel);
                    }
                }
                    
            case 'on':
                // Si invertido, 'on' se interpreta como off→on; si no, solo on
                if ($invert) {
                    error_log("ShellyActionService::execute() - ON con inversión: OFF → ON");
                    $api->relayTurnOff($channel);
                }
                return $api->relayTurnOn($channel);
                
            case 'off':
                // Si invertido, 'off' se interpreta como on→off; si no, solo off
                if ($invert) {
                    error_log("ShellyActionService::execute() - OFF con inversión: ON → OFF");
                    $api->relayTurnOn($channel);
                }
                return $api->relayTurnOff($channel);
                
            case 'pulse':
                // Hacer pulse respetando el flag aquí (en vez de ShellyAPI) para tener control fino
                $durationMs = (int)($cfg['duration_ms'] ?? 500);
                $waitTime = max(10000, $durationMs * 1000); // Convertir ms a microsegundos
                
                if ($invert) {
                    error_log("ShellyActionService::execute() - PULSE con inversión: OFF → ON (${durationMs}ms)");
                    $api->relayTurnOff($channel);
                    usleep($waitTime);
                    return $api->relayTurnOn($channel);
                } else {
                    error_log("ShellyActionService::execute() - PULSE sin inversión: ON → OFF (${durationMs}ms)");
                    $api->relayTurnOn($channel);
                    usleep($waitTime);
                    return $api->relayTurnOff($channel);
                }
                
            default:
                throw new Exception("action_kind no soportado: " . $cfg['action_kind']);
        }
    }
    
    /**
     * Verifica si hay al menos un dispositivo configurado y habilitado
     * @param Database $db Instancia de base de datos
     * @return bool True si hay dispositivos configurados
     */
    public static function hasDevices($db) {
        $count = $db->fetchOne("SELECT COUNT(*) as count FROM shelly_devices WHERE is_enabled = 1");
        return $count && $count['count'] > 0;
    }
    
    /**
     * Obtiene la configuración de un dispositivo por defecto para mostrar en UI
     * @param Database $db Instancia de base de datos
     * @return array|null Configuración del dispositivo o null
     */
    public static function getDefaultDevice($db) {
        return $db->fetchOne("SELECT * FROM shelly_devices WHERE is_enabled = 1 ORDER BY sort_order, id LIMIT 1");
    }
}
