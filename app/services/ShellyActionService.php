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
        
        // Crear instancia de ShellyAPI con los datos del dispositivo
        $api = new ShellyAPI($cfg['auth_token'], $cfg['device_id'], $cfg['server_host']);
        
        // Ejecutar según el tipo de acción
        switch ($cfg['action_kind']) {
            case 'toggle':
                // Toggle: usar el modo (open/close) para decidir si encender o apagar
                return $mode === 'open'
                    ? $api->relayTurnOn($channel)
                    : $api->relayTurnOff($channel);
                    
            case 'on':
                // Siempre encender
                return $api->relayTurnOn($channel);
                
            case 'off':
                // Siempre apagar
                return $api->relayTurnOff($channel);
                
            case 'pulse':
                // Pulso: encender, esperar, apagar
                return $api->relayPulse($channel, (int)($cfg['duration_ms'] ?? 500));
                
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
