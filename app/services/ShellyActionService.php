<?php
/**
 * Servicio ShellyActionService
 * Ejecuta acciones en dispositivos Shelly según configuración
 */
require_once APP_PATH . '/helpers/ShellyAPI.php';
require_once APP_PATH . '/models/ShellyAction.php';
require_once APP_PATH . '/services/ShellyCloudClient.php';
require_once APP_PATH . '/helpers/ShellyLockHelper.php';

class ShellyActionService {
    
    /**
     * Ejecuta una acción Shelly según el código y modo
     * @param Database $db Instancia de base de datos
     * @param string $code Código de la acción (ej: 'abrir_cerrar')
     * @param string $mode Modo de operación: 'open' | 'close'
     * @param string|null $correlationId ID de correlación para idempotencia (ej: "access:123:entry")
     * @return array Resultado de la operación
     * @throws Exception Si no hay configuración para la acción
     */
    public static function execute($db, $code, $mode, ?string $correlationId = null) {
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
            // Determinar el canal a usar según el modo (entrada o salida)
            $channel = self::getChannelForMode($cfg, $mode);
            
            $invert = isset($cfg['invert_sequence']) ? (int)$cfg['invert_sequence'] : 1;
            $pulseDuration = isset($cfg['pulse_duration_ms']) ? (int)$cfg['pulse_duration_ms'] : 5000;
            
            // Generar ID de correlación si no se proporcionó
            $correlation = $correlationId ?? uniqid("shelly_{$code}_{$mode}_", true);
            $action = $mode === 'open' ? 'entry' : 'exit';
            
            // Verificar si el pulso ya fue ejecutado (idempotencia)
            if (ShellyLockHelper::pulseExists($db, $channel, $correlation)) {
                error_log("ShellyActionService - Pulse already exists: {$correlation}, skipping");
                continue;
            }
            
            // Ejecutar con lock para evitar dobles pulsos
            $lastResult = ShellyLockHelper::withPortLock($db, $channel, function() use ($cfg, $channel, $pulseDuration, $invert, $db, $correlation, $action) {
                // Intentar con el nuevo cliente JSON-RPC primero
                $useJsonRpc = true; // Flag para controlar si usar JSON-RPC
                
                if ($useJsonRpc) {
                    $client = new ShellyCloudClient(
                        $cfg['server_host'],
                        $cfg['device_id'],
                        $cfg['auth_token'] ?? ''
                    );
                    
                    switch ($cfg['action_kind']) {
                        case 'toggle':
                        case 'pulse':
                            $durationMs = (int)($cfg['duration_ms'] ?? $pulseDuration);
                            $result = $client->pulse($channel, $durationMs);
                            
                            // Registrar en log de pulsos
                            ShellyLockHelper::logPulse($db, $action, $channel, $correlation, [
                                'device_id' => $cfg['device_id'],
                                'duration_ms' => $durationMs,
                                'success' => $result['ok'],
                                'error_message' => $result['error'] ?? null
                            ]);
                            
                            // Convertir resultado a formato legacy
                            return ['success' => $result['ok'], 'data' => $result['result'] ?? null, 'error' => $result['error'] ?? null];
                        
                        case 'on':
                            $result = $invert ? $client->turnOn($channel) : $client->turnOff($channel);
                            ShellyLockHelper::logPulse($db, $action, $channel, $correlation, [
                                'device_id' => $cfg['device_id'],
                                'success' => $result['ok'],
                                'error_message' => $result['error'] ?? null
                            ]);
                            return ['success' => $result['ok'], 'data' => $result['result'] ?? null, 'error' => $result['error'] ?? null];
                        
                        case 'off':
                            $result = $invert ? $client->turnOff($channel) : $client->turnOn($channel);
                            ShellyLockHelper::logPulse($db, $action, $channel, $correlation, [
                                'device_id' => $cfg['device_id'],
                                'success' => $result['ok'],
                                'error_message' => $result['error'] ?? null
                            ]);
                            return ['success' => $result['ok'], 'data' => $result['result'] ?? null, 'error' => $result['error'] ?? null];
                        
                        default:
                            throw new Exception("action_kind no soportado: " . $cfg['action_kind']);
                    }
                } else {
                    // Fallback a API legacy
                    $api = new ShellyAPI($cfg['auth_token'], $cfg['device_id'], $cfg['server_host']);
                    
                    switch ($cfg['action_kind']) {
                        case 'toggle':
                        case 'pulse':
                            $durationMs = (int)($cfg['duration_ms'] ?? $pulseDuration);
                            $result = self::executePulse($api, $channel, $durationMs, $invert);
                            ShellyLockHelper::logPulse($db, $action, $channel, $correlation, [
                                'device_id' => $cfg['device_id'],
                                'duration_ms' => $durationMs,
                                'success' => $result['success'] ?? false,
                                'error_message' => $result['error'] ?? null
                            ]);
                            return $result;
                        
                        case 'on':
                            $result = $api->relayTurnOn($channel);
                            ShellyLockHelper::logPulse($db, $action, $channel, $correlation, [
                                'device_id' => $cfg['device_id'],
                                'success' => $result['success'] ?? false,
                                'error_message' => $result['error'] ?? null
                            ]);
                            return $result;
                        
                        case 'off':
                            $result = $api->relayTurnOff($channel);
                            ShellyLockHelper::logPulse($db, $action, $channel, $correlation, [
                                'device_id' => $cfg['device_id'],
                                'success' => $result['success'] ?? false,
                                'error_message' => $result['error'] ?? null
                            ]);
                            return $result;
                        
                        default:
                            throw new Exception("action_kind no soportado: " . $cfg['action_kind']);
                    }
                }
            });
            
            error_log("ShellyActionService::execute() - {$code} {$mode} - device={$cfg['device_id']} channel={$channel} correlation={$correlation}");
        }
        
        return $lastResult; // último resultado por conveniencia
    }
    
    /**
     * Determina el canal a usar según el modo de operación
     * @param array $cfg Configuración del dispositivo
     * @param string $mode Modo de operación ('open' o 'close')
     * @return int Canal a usar
     */
    private static function getChannelForMode($cfg, $mode) {
        if ($mode === 'open') {
            // Para apertura, usar entry_channel si está disponible
            if (isset($cfg['entry_channel']) && $cfg['entry_channel'] !== null) {
                return (int)$cfg['entry_channel'];
            }
        } else {
            // Para cierre, usar exit_channel si está disponible
            if (isset($cfg['exit_channel']) && $cfg['exit_channel'] !== null) {
                return (int)$cfg['exit_channel'];
            }
        }
        
        // Fallback: usar action_channel o active_channel
        if (isset($cfg['action_channel']) && $cfg['action_channel'] !== null) {
            return (int)$cfg['action_channel'];
        }
        
        return (int)$cfg['active_channel'];
    }
    
    /**
     * Ejecuta un pulso en el canal especificado
     * 
     * Nota: Utiliza usleep() que es bloqueante. Esto es aceptable para control de hardware IoT
     * donde la precisión del tiempo es crítica. El pulso típico es de 5 segundos.
     * Para duraciones mayores a 10 segundos, considerar implementación asíncrona.
     * 
     * @param ShellyAPI $api Instancia de la API de Shelly
     * @param int $channel Canal a activar
     * @param int $durationMs Duración del pulso en milisegundos (máximo práctico: 10000ms)
     * @param int $invert Si se debe invertir la secuencia
     * @return array Resultado de la operación
     */
    private static function executePulse($api, $channel, $durationMs, $invert) {
        // Limitar duración práctica para evitar timeouts largos
        // Si se necesitan pulsos más largos, considerar implementación asíncrona
        $durationMs = min($durationMs, 10000); // Máximo 10 segundos
        $waitTime = $durationMs * 1000; // Convert ms to microseconds
        
        if ($invert) {
            $api->relayTurnOn($channel);
            usleep($waitTime);
            return $api->relayTurnOff($channel);
        } else {
            $api->relayTurnOff($channel);
            usleep($waitTime);
            return $api->relayTurnOn($channel);
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
