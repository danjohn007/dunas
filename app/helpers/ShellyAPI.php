<?php
/**
 * Clase ShellyAPI - Integración con Shelly Relay SHELLPRO4PM via Cloud API
 */
require_once APP_PATH . '/models/Settings.php';

class ShellyAPI {
    
    // Timeouts extendidos para manejar latencia de red
    const TIMEOUT_EXTENDED = 30;
    const CONNECT_TIMEOUT = 20;
    const RETRY_DELAY_MICROSECONDS = 1000000; // 1 segundo
    const MAX_RETRIES = 3;
    
    private $token;
    private $deviceId;
    private $server;
    
    /**
     * Constructor - permite crear instancia con configuración específica
     * @param string $authToken Token de autenticación (opcional, usa settings si no se provee)
     * @param string $deviceId ID del dispositivo (opcional, usa settings si no se provee)
     * @param string $serverHost Servidor Cloud (opcional, usa settings si no se provee)
     */
    public function __construct($authToken = null, $deviceId = null, $serverHost = null) {
        if ($authToken && $deviceId && $serverHost) {
            // Usar parámetros proporcionados
            $this->token = $authToken;
            $this->deviceId = $deviceId;
            $this->server = $serverHost;
        } else {
            // Usar configuración de settings
            $settings = self::getSettings();
            $this->token = $settings['auth_token'];
            $this->deviceId = $settings['device_id'];
            $this->server = $settings['server'];
        }
    }
    
    private static function getSettings() {
        static $settings = null;
        if ($settings === null) {
            $settingsModel = new Settings();
            $allSettings = $settingsModel->getAll();
            
            $settings = [
                'auth_token' => $allSettings['shelly_auth_token'] ?? SHELLY_AUTH_TOKEN,
                'device_id' => $allSettings['shelly_device_id'] ?? SHELLY_DEVICE_ID,
                'server' => $allSettings['shelly_server'] ?? SHELLY_SERVER,
            ];
        }
        return $settings;
    }
    
    /**
     * Realiza una llamada al Shelly Cloud API
     * @param array $params Parámetros del método (debe incluir 'channel' y 'turn')
     * @return array Resultado de la operación
     */
    private function makeCloudRequest($params = []) {
        // Construir URL del endpoint - usando el endpoint de control de relay
        $url = 'https://' . $this->server . '/device/relay/control';
        
        // Preparar datos para enviar (form-urlencoded según especificación del Cloud API)
        $postData = [
            'auth_key' => $this->token,
            'id' => $this->deviceId,
            'channel' => isset($params['channel']) ? $params['channel'] : 0,
            'turn' => isset($params['turn']) ? $params['turn'] : 'on'
        ];
        
        // Log de debugging
        error_log("Shelly Cloud API - Channel: " . $postData['channel'] . ", Turn: " . $postData['turn'] . ", Device: " . $this->deviceId);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_TIMEOUT, SHELLY_API_TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyAPI/2.0 (Dunas Control System - Cloud)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);
        
        // Log detallado para debugging
        error_log("Shelly Cloud Response - HTTP: $httpCode, Time: {$totalTime}s");
        
        if ($error) {
            error_log("Shelly Cloud API Error: " . $error);
            return ['success' => false, 'error' => $error, 'http_code' => $httpCode];
        }
        
        if ($httpCode !== 200) {
            error_log("Shelly Cloud API HTTP Error: " . $httpCode . " - Response: " . $response);
            return ['success' => false, 'error' => 'HTTP ' . $httpCode, 'http_code' => $httpCode, 'response' => $response];
        }
        
        $decoded = json_decode($response, true);
        
        // Verificar si hubo error en la respuesta
        if (isset($decoded['isok'])) {
            if (!$decoded['isok']) {
                $errorMsg = $decoded['errors'] ?? 'Error en la respuesta del dispositivo';
                error_log("Shelly Cloud API Error Response: " . json_encode($errorMsg));
                return ['success' => false, 'error' => $errorMsg, 'data' => $decoded];
            }
            // Si isok es true, la operación fue exitosa
            return ['success' => true, 'data' => $decoded, 'response_time' => $totalTime];
        }
        
        // Si no hay campo 'isok', pero llegamos aquí, asumimos éxito
        return ['success' => true, 'data' => $decoded, 'response_time' => $totalTime];
    }
    
    /**
     * Enciende un relay (Switch ON)
     * @param int $channel Canal del relay (0-3)
     * @return array Resultado de la operación
     */
    public function relayTurnOn($channel = 0) {
        // Verificar si Shelly está habilitado
        if (!SHELLY_ENABLED) {
            error_log("ShellyAPI::relayTurnOn() - Shelly deshabilitado, retornando éxito simulado");
            return ['success' => true, 'message' => 'Shelly deshabilitado - modo simulación'];
        }
        
        error_log("ShellyAPI::relayTurnOn() - Intentando encender relay canal $channel via Cloud API");
        
        $result = null;
        $lastError = '';
        
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            error_log("ShellyAPI::relayTurnOn() - Intento " . ($attempt + 1) . " de " . (self::MAX_RETRIES + 1));
            
            $result = $this->makeCloudRequest([
                'channel' => $channel,
                'turn' => 'on'
            ]);
            
            if ($result['success']) {
                error_log("ShellyAPI::relayTurnOn() - ✅ Éxito en intento " . ($attempt + 1));
                break;
            } else {
                $lastError = $result['error'] ?? 'Error desconocido';
                error_log("ShellyAPI::relayTurnOn() - ❌ Fallo en intento " . ($attempt + 1) . ": " . $lastError);
            }
            
            if ($attempt < self::MAX_RETRIES) {
                error_log("ShellyAPI::relayTurnOn() - Esperando antes del siguiente intento...");
                usleep(self::RETRY_DELAY_MICROSECONDS);
            }
        }
        
        if (!$result['success']) {
            $result['error'] = $lastError;
            error_log("ShellyAPI::relayTurnOn() - ❌ Falló después de todos los intentos. Error final: " . $lastError);
        }
        
        return $result;
    }
    
    /**
     * Apaga un relay (Switch OFF)
     * @param int $channel Canal del relay (0-3)
     * @return array Resultado de la operación
     */
    public function relayTurnOff($channel = 0) {
        // Verificar si Shelly está habilitado
        if (!SHELLY_ENABLED) {
            error_log("ShellyAPI::relayTurnOff() - Shelly deshabilitado, retornando éxito simulado");
            return ['success' => true, 'message' => 'Shelly deshabilitado - modo simulación'];
        }
        
        error_log("ShellyAPI::relayTurnOff() - Intentando apagar relay canal $channel via Cloud API");
        
        $result = null;
        $lastError = '';
        
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            error_log("ShellyAPI::relayTurnOff() - Intento " . ($attempt + 1) . " de " . (self::MAX_RETRIES + 1));
            
            $result = $this->makeCloudRequest([
                'channel' => $channel,
                'turn' => 'off'
            ]);
            
            if ($result['success']) {
                error_log("ShellyAPI::relayTurnOff() - ✅ Éxito en intento " . ($attempt + 1));
                break;
            } else {
                $lastError = $result['error'] ?? 'Error desconocido';
                error_log("ShellyAPI::relayTurnOff() - ❌ Fallo en intento " . ($attempt + 1) . ": " . $lastError);
            }
            
            if ($attempt < self::MAX_RETRIES) {
                error_log("ShellyAPI::relayTurnOff() - Esperando antes del siguiente intento...");
                usleep(self::RETRY_DELAY_MICROSECONDS);
            }
        }
        
        if (!$result['success']) {
            $result['error'] = $lastError;
            error_log("ShellyAPI::relayTurnOff() - ❌ Falló después de todos los intentos. Error final: " . $lastError);
        }
        
        return $result;
    }
    
    /**
     * Pulso en relay: apaga, espera, y enciende (invertido por defecto)
     * @param int $channel Canal del relay (0-3)
     * @param int $durationMs Duración del pulso en milisegundos
     * @return array Resultado de la operación
     */
    public function relayPulse($channel = 0, $durationMs = 500) {
        if (!SHELLY_ENABLED) {
            return ['success' => true, 'message' => 'Shelly deshabilitado - modo simulación'];
        }
        
        error_log("ShellyAPI::relayPulse() - Ejecutando pulso invertido (off→on) en canal $channel por {$durationMs}ms");
        
        // Invertido por defecto: off → on
        $result = $this->relayTurnOff($channel);
        if (!$result['success']) {
            return $result;
        }
        
        usleep(max(10000, $durationMs * 1000)); // Convertir ms a microsegundos
        
        return $this->relayTurnOn($channel);
    }
    
    /**
     * Abre la barrera vehicular (Switch OFF) - Método de compatibilidad
     * @return array Resultado de la operación
     */
    public static function openBarrier() {
        $api = new self(); // Usar configuración de settings
        return $api->relayTurnOff(SHELLY_SWITCH_ID);
    }
    
    /**
     * Cierra la barrera vehicular (Switch ON) - Método de compatibilidad
     * @return array Resultado de la operación
     */
    public static function closeBarrier() {
        $api = new self(); // Usar configuración de settings
        return $api->relayTurnOn(SHELLY_SWITCH_ID);
    }
    
    /**
     * Obtiene el estado del dispositivo Shelly via Cloud API
     * @return array Resultado de la operación
     */
    public function getStatus() {
        if (!SHELLY_ENABLED) {
            return ['success' => false, 'error' => 'Shelly deshabilitado'];
        }
        
        $url = 'https://' . $this->server . '/device/status';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'auth_key' => $this->token,
            'id' => $this->deviceId
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, SHELLY_API_TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'HTTP ' . $httpCode];
        }
        
        $decoded = json_decode($response, true);
        return ['success' => true, 'data' => $decoded];
    }
    
    /**
     * Obtiene el estado de un switch específico via Cloud API
     * @param int $channel Canal del switch (por defecto 0)
     * @return array Resultado de la operación
     */
    public function getRelayStatus($channel = 0) {
        return $this->getStatus();
    }
}
