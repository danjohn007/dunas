<?php
/**
 * ShellyCloudClient - Cliente JSON-RPC para Shelly Cloud API
 * 
 * Implementa comunicación con Shelly Cloud usando JSON-RPC según la especificación oficial.
 * Proporciona métodos atómicos para control de relays con soporte para toggle_after.
 */
class ShellyCloudClient {
    private string $server;
    private int $port;
    private string $authKey;
    private string $deviceId;
    
    const TIMEOUT = 6;
    const CONNECT_TIMEOUT = 3;
    
    /**
     * Constructor
     * @param string $server Servidor Cloud (ej: 'shelly-208-eu.shelly.cloud')
     * @param string $deviceId ID del dispositivo Shelly
     * @param string $authKey Clave de autenticación (opcional según configuración)
     * @param int $port Puerto del servidor (default: 6022)
     */
    public function __construct(string $server, string $deviceId, string $authKey = '', int $port = 6022) {
        $this->server = $server;
        $this->port = $port;
        $this->authKey = $authKey;
        $this->deviceId = $deviceId;
    }
    
    /**
     * Ejecuta un pulso atómico en el relay usando toggle_after
     * Este método enciende el relay y automáticamente lo apaga después del tiempo especificado.
     * 
     * @param int $relayId ID del relay (canal 0-3)
     * @param int $durationMs Duración del pulso en milisegundos
     * @return array ['ok' => bool, 'result' => mixed, 'error' => string]
     */
    public function pulse(int $relayId, int $durationMs): array {
        // Convertir ms a segundos (Shelly espera segundos en toggle_after)
        $toggleAfterSec = max(1, (int)ceil($durationMs / 1000.0));
        
        $payload = [
            'id' => (int)round(microtime(true) * 1000),
            'src' => 'dunas',
            'method' => 'Switch.Set',
            'params' => [
                'id' => $relayId,
                'on' => true,
                'toggle_after' => $toggleAfterSec
            ]
        ];
        
        error_log("ShellyCloudClient::pulse() - Relay {$relayId}, duration {$durationMs}ms ({$toggleAfterSec}s)");
        
        return $this->sendJsonRpc($payload);
    }
    
    /**
     * Fallback: ejecuta pulso con dos llamadas separadas (ON → sleep → OFF)
     * Usar solo si el dispositivo no soporta toggle_after correctamente.
     * 
     * ADVERTENCIA: Este método bloquea el servidor durante la duración del pulso.
     * 
     * @param int $relayId ID del relay (canal 0-3)
     * @param int $durationMs Duración del pulso en milisegundos
     * @return array Resultado de la operación
     */
    public function pulseWithFallback(int $relayId, int $durationMs): array {
        error_log("ShellyCloudClient::pulseWithFallback() - Relay {$relayId}, duration {$durationMs}ms");
        
        // Encender
        $r1 = $this->sendJsonRpc([
            'id' => 1,
            'src' => 'dunas',
            'method' => 'Switch.Set',
            'params' => ['id' => $relayId, 'on' => true]
        ]);
        
        if (!$r1['ok']) {
            return $r1;
        }
        
        // Esperar (bloqueante)
        usleep($durationMs * 1000);
        
        // Apagar
        $r2 = $this->sendJsonRpc([
            'id' => 2,
            'src' => 'dunas',
            'method' => 'Switch.Set',
            'params' => ['id' => $relayId, 'on' => false]
        ]);
        
        return $r2;
    }
    
    /**
     * Enciende un relay
     * @param int $relayId ID del relay
     * @return array Resultado de la operación
     */
    public function turnOn(int $relayId): array {
        return $this->sendJsonRpc([
            'id' => (int)round(microtime(true) * 1000),
            'src' => 'dunas',
            'method' => 'Switch.Set',
            'params' => ['id' => $relayId, 'on' => true]
        ]);
    }
    
    /**
     * Apaga un relay
     * @param int $relayId ID del relay
     * @return array Resultado de la operación
     */
    public function turnOff(int $relayId): array {
        return $this->sendJsonRpc([
            'id' => (int)round(microtime(true) * 1000),
            'src' => 'dunas',
            'method' => 'Switch.Set',
            'params' => ['id' => $relayId, 'on' => false]
        ]);
    }
    
    /**
     * Envía una solicitud JSON-RPC al servidor Shelly Cloud
     * @param array $payload Payload JSON-RPC
     * @return array ['ok' => bool, 'result' => mixed, 'error' => string, 'raw' => string]
     */
    private function sendJsonRpc(array $payload): array {
        $url = "https://{$this->server}:{$this->port}/device/relay/rpc";
        
        // Agregar auth_key si está configurado
        if (!empty($this->authKey)) {
            $payload['auth'] = ['key' => $this->authKey];
        }
        
        $jsonPayload = json_encode($payload);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonPayload)
            ],
            CURLOPT_POSTFIELDS => $jsonPayload,
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
        
        error_log("ShellyCloudClient - HTTP {$httpCode} in {$elapsed}ms");
        
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
            return ['ok' => false, 'error' => 'Invalid JSON-RPC response', 'raw' => $res];
        }
        
        // Shelly responde con "result" en caso de éxito o "error" en caso de fallo
        if (isset($json['error'])) {
            $errorMsg = is_array($json['error']) 
                ? ($json['error']['message'] ?? json_encode($json['error']))
                : $json['error'];
            error_log("ShellyCloudClient - RPC error: {$errorMsg}");
            return ['ok' => false, 'error' => $errorMsg, 'rpc_error' => $json['error']];
        }
        
        return ['ok' => true, 'result' => $json['result'] ?? $json, 'raw' => $res];
    }
}
