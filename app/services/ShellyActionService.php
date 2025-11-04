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
                // Interpretar open/close como ESTADOS (una sola llamada), no como secuencias.
                // Mapeo por defecto (invertido=0): open=ON, close=OFF
                // Mapeo invertido (invertido=1):  open=OFF, close=ON
                if ($mode === 'open') {
                    if ($invert) {
                        error_log("ShellyActionService::execute() - TOGGLE OPEN (invertido) => OFF");
                        return $api->relayTurnOff($channel);
                    } else {
                        error_log("ShellyActionService::execute() - TOGGLE OPEN (normal) => ON");
                        return $api->relayTurnOn($channel);
                    }
                } else { // close
                    if ($invert) {
                        error_log("ShellyActionService::execute() - TOGGLE CLOSE (invertido) => ON");
                        return $api->relayTurnOn($channel);
                    } else {
                        error_log("ShellyActionService::execute() - TOGGLE CLOSE (normal) => OFF");
                        return $api->relayTurnOff($channel);
                    }
                }

            case 'on':
                // Acción unitaria: encender (sin pre-pasos)
                error_log("ShellyActionService::execute() - ON");
                return $api->relayTurnOn($channel);
                
            case 'off':
                // Acción unitaria: apagar (sin pre-pasos)
                error_log("ShellyActionService::execute() - OFF");
                return $api->relayTurnOff($channel);
                
            case 'pulse':
                // Si algún flujo usa 'pulse', aquí sí hay dos pasos por definición (on y off o viceversa).
                // Mantenerlo como pulso "final hacia ON" si invert=0, o "final hacia OFF" si invert=1.
                $durationMs = (int)($cfg['duration_ms'] ?? 500);
                $waitTime = max(10000, $durationMs * 1000);
                
                if ($invert) {
                    // Final hacia OFF
                    error_log("ShellyActionService::execute() - PULSE invertido: ON→OFF ({$durationMs}ms)");
                    $api->relayTurnOn($channel);
                    usleep($waitTime);
                    return $api->relayTurnOff($channel);
                } else {
                    // Final hacia ON
                    error_log("ShellyActionService::execute() - PULSE normal: OFF→ON ({$durationMs}ms)");
                    $api->relayTurnOff($channel);
                    usleep($waitTime);
                    return $api->relayTurnOn($channel);
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
