<?php
/**
 * ShellyCloudClient - Cliente mejorado para Shelly Cloud API
 * 
 * Implementa comunicación con Shelly Cloud usando el REST API oficial.
 * Proporciona métodos atómicos para control de relays con gestión de pulsos.
 * 
 * Nota: Este cliente usa el Shelly Cloud REST API (form-urlencoded) ya que
 * el Cloud no expone el JSON-RPC directamente. Para JSON-RPC se requeriría
 * conexión directa IP, que no es posible en servidor remoto.
 */
class ShellyCloudClient {
    private string $server;
    private string $authKey;
    private string $deviceId;
    
    const TIMEOUT = 6;
    const CONNECT_TIMEOUT = 3;
    
    /**
     * Constructor
     * @param string $server Servidor Cloud (ej: 'shelly-208-eu.shelly.cloud')
     * @param string $deviceId ID del dispositivo Shelly
     * @param string $authKey Clave de autenticación
     */
    public function __construct(string $server, string $deviceId, string $authKey = '') {
        $this->server = $server;
        $this->authKey = $authKey;
        $this->deviceId = $deviceId;
    }
    
    /**
     * Ejecuta un pulso completo en el relay (enciende y apaga)
     * 
     * Implementación: Dado que Shelly Cloud REST API no expone toggle_after,
     * usamos dos llamadas separadas con sleep entre ellas. Aunque esto es bloqueante,
     * es aceptable para control de hardware IoT donde la precisión es crítica.
     * 
     * @param int $relayId ID del relay (canal 0-3)
     * @param int $durationMs Duración del pulso en milisegundos
     * @param bool $invertSequence Si true: ON→OFF, si false: OFF→ON
     * @return array ['ok' => bool, 'result' => mixed, 'error' => string]
     */
    public function pulse(int $relayId, int $durationMs, bool $invertSequence = false): array {
        error_log("ShellyCloudClient::pulse() - Relay {$relayId}, duration {$durationMs}ms, invert=" . ($invertSequence ? 'true' : 'false'));
        
        // Limitar duración para evitar timeouts excesivos
        $durationMs = min($durationMs, 10000); // Máximo 10 segundos
        
        if ($invertSequence) {
            // Secuencia invertida: ON → esperar → OFF
            $r1 = $this->turnOn($relayId);
            if (!$r1['ok']) {
                return $r1;
            }
            
            usleep($durationMs * 1000);
            
            return $this->turnOff($relayId);
        } else {
            // Secuencia normal: OFF → esperar → ON
            $r1 = $this->turnOff($relayId);
            if (!$r1['ok']) {
                return $r1;
            }
            
            usleep($durationMs * 1000);
            
            return $this->turnOn($relayId);
        }
    }
    
    /**
     * Enciende un relay
     * @param int $relayId ID del relay
     * @return array Resultado de la operación
     */
    public function turnOn(int $relayId): array {
        return $this->controlRelay($relayId, 'on');
    }
    
    /**
     * Apaga un relay
     * @param int $relayId ID del relay
     * @return array Resultado de la operación
     */
    public function turnOff(int $relayId): array {
        return $this->controlRelay($relayId, 'off');
    }
    
    /**
     * Controla un relay del dispositivo Shelly
     * @param int $channel Canal del relay (0-3)
     * @param string $turn 'on' o 'off'
     * @return array ['ok' => bool, 'result' => mixed, 'error' => string]
     */
    private function controlRelay(int $channel, string $turn): array {
        $url = "https://{$this->server}/device/relay/control";
        
        $postData = [
            'auth_key' => $this->authKey,
            'id' => $this->deviceId,
            'channel' => $channel,
            'turn' => $turn
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        
        $startTime = microtime(true);
        $res = curl_exec($ch);
        $elapsed = round((microtime(true) - $startTime) * 1000, 2);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        error_log("ShellyCloudClient - HTTP {$httpCode} in {$elapsed}ms - channel={$channel}, turn={$turn}");
        
        if ($err) {
            error_log("ShellyCloudClient - cURL error: {$err}");
            return ['ok' => false, 'error' => $err, 'http_code' => $httpCode];
        }
        
        if ($httpCode !== 200) {
            error_log("ShellyCloudClient - HTTP error {$httpCode}: {$res}");
            return ['ok' => false, 'error' => "HTTP {$httpCode}", 'http_code' => $httpCode, 'raw' => $res];
        }
        
        $json = json_decode($res, true);
        if (!$json) {
            error_log("ShellyCloudClient - Invalid JSON response: {$res}");
            return ['ok' => false, 'error' => 'Invalid JSON response', 'raw' => $res];
        }
        
        // Shelly Cloud responde con "isok" en el formato REST
        if (isset($json['isok'])) {
            if ($json['isok']) {
                return ['ok' => true, 'result' => $json['data'] ?? $json, 'raw' => $res];
            } else {
                $errorMsg = is_array($json['errors'] ?? null) 
                    ? json_encode($json['errors'])
                    : ($json['errors'] ?? 'Unknown error');
                error_log("ShellyCloudClient - API error: {$errorMsg}");
                return ['ok' => false, 'error' => $errorMsg, 'api_response' => $json];
            }
        }
        
        // Si no hay 'isok', asumir éxito
        return ['ok' => true, 'result' => $json, 'raw' => $res];
    }
}
