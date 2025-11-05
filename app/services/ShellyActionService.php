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
        // Obtener todos los dispositivos que tienen esta acción
        $rows = ShellyAction::resolveAllByAction($db, $code);
        
        if (!$rows || count($rows) === 0) {
            throw new Exception("No hay dispositivos configurados para la acción: $code");
        }
        
        // Separar en simultáneos y normales
        $simul = array_values(array_filter($rows, fn($r) => (int)$r['is_simultaneous'] === 1));
        $targets = !empty($simul) ? $simul : [$rows[0]]; // fallback al primero si no hay simultáneos
        
        $lastResult = null;
        foreach ($targets as $cfg) {
            $channel = isset($cfg['action_channel']) && $cfg['action_channel'] !== null
                ? (int)$cfg['action_channel']
                : (int)$cfg['active_channel'];
            
            $invert = isset($cfg['invert_sequence']) ? (int)$cfg['invert_sequence'] : 1;
            
            $api = new ShellyAPI($cfg['auth_token'], $cfg['device_id'], $cfg['server_host']);
            
            switch ($cfg['action_kind']) {
                case 'toggle':
                    if ($mode === 'open') {
                        $lastResult = $invert ? $api->relayTurnOff($channel) : $api->relayTurnOn($channel);
                    } else {
                        $lastResult = $invert ? $api->relayTurnOn($channel) : $api->relayTurnOff($channel);
                    }
                    break;
                
                case 'on':
                    $lastResult = $api->relayTurnOn($channel);
                    break;
                
                case 'off':
                    $lastResult = $api->relayTurnOff($channel);
                    break;
                
                case 'pulse':
                    $durationMs = (int)($cfg['duration_ms'] ?? 500);
                    $waitTime = $durationMs * 1000; // Convert ms to microseconds
                    if ($invert) {
                        $api->relayTurnOn($channel);
                        usleep($waitTime);
                        $lastResult = $api->relayTurnOff($channel);
                    } else {
                        $api->relayTurnOff($channel);
                        usleep($waitTime);
                        $lastResult = $api->relayTurnOn($channel);
                    }
                    break;
                
                default:
                    throw new Exception("action_kind no soportado: " . $cfg['action_kind']);
            }
            
            error_log("ShellyActionService::execute() - {$code} {$mode} - device={$cfg['device_id']} channel={$channel} simul={$cfg['is_simultaneous']} invert={$invert}");
        }
        
        return $lastResult; // último resultado por conveniencia
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
