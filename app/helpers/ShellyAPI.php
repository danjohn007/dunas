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
     * @param array $params Parámetros del método (debe incluir 'id' y 'on')
     * @return array Resultado de la operación
     */
    private static function makeCloudRequest($params = []) {
        $settings = self::getSettings();
        
        // Construir URL del endpoint - usando el endpoint de control de relay
        $url = 'https://' . $settings['server'] . '/device/relay/control';
        
        // Preparar datos para enviar (form-urlencoded según especificación del Cloud API)
        $postData = [
            'auth_key' => $settings['auth_token'],
            'id' => $settings['device_id'],
            'channel' => isset($params['id']) ? $params['id'] : 0,
            'turn' => isset($params['on']) ? ($params['on'] ? 'on' : 'off') : 'on'
        ];
        
        // Log de debugging
        error_log("Shelly Cloud API - Channel: " . $postData['channel'] . ", Turn: " . $postData['turn'] . ", Device: " . $settings['device_id']);
        
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
     * Abre la barrera vehicular (Switch OFF)
     * @return array Resultado de la operación
     */
    public static function openBarrier() {
        // Verificar si Shelly está habilitado
        if (!SHELLY_ENABLED) {
            error_log("ShellyAPI::openBarrier() - Shelly deshabilitado, retornando éxito simulado");
            return ['success' => true, 'message' => 'Shelly deshabilitado - modo simulación'];
        }
        
        // Log para debug
        error_log("ShellyAPI::openBarrier() - Intentando abrir barrera via Cloud API");
        
        $result = null;
        $lastError = '';
        
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            error_log("ShellyAPI::openBarrier() - Intento " . ($attempt + 1) . " de " . (self::MAX_RETRIES + 1));
            
            // Abrir barrera = Switch OFF (on=false)
            $result = self::makeCloudRequest([
                'id' => SHELLY_SWITCH_ID,
                'on' => false
            ]);
            
            if ($result['success']) {
                error_log("ShellyAPI::openBarrier() - ✅ Éxito en intento " . ($attempt + 1));
                break;
            } else {
                $lastError = $result['error'] ?? 'Error desconocido';
                error_log("ShellyAPI::openBarrier() - ❌ Fallo en intento " . ($attempt + 1) . ": " . $lastError);
            }
            
            // Si falla y aún quedan intentos, esperar un poco antes de reintentar
            if ($attempt < self::MAX_RETRIES) {
                error_log("ShellyAPI::openBarrier() - Esperando antes del siguiente intento...");
                usleep(self::RETRY_DELAY_MICROSECONDS);
            }
        }
        
        // Si falló todos los intentos, asegurar que se devuelve el último error
        if (!$result['success']) {
            $result['error'] = $lastError;
            error_log("ShellyAPI::openBarrier() - ❌ Falló después de todos los intentos. Error final: " . $lastError);
        }
        
        return $result;
    }
    
    /**
     * Cierra la barrera vehicular (Switch ON)
     * @return array Resultado de la operación
     */
    public static function closeBarrier() {
        // Verificar si Shelly está habilitado
        if (!SHELLY_ENABLED) {
            error_log("ShellyAPI::closeBarrier() - Shelly deshabilitado, retornando éxito simulado");
            return ['success' => true, 'message' => 'Shelly deshabilitado - modo simulación'];
        }
        
        // Log para debug
        error_log("ShellyAPI::closeBarrier() - Intentando cerrar barrera via Cloud API");
        
        $result = null;
        $lastError = '';
        
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            error_log("ShellyAPI::closeBarrier() - Intento " . ($attempt + 1) . " de " . (self::MAX_RETRIES + 1));
            
            // Cerrar barrera = Switch ON (on=true)
            $result = self::makeCloudRequest([
                'id' => SHELLY_SWITCH_ID,
                'on' => true
            ]);
            
            if ($result['success']) {
                error_log("ShellyAPI::closeBarrier() - ✅ Éxito en intento " . ($attempt + 1));
                break;
            } else {
                $lastError = $result['error'] ?? 'Error desconocido';
                error_log("ShellyAPI::closeBarrier() - ❌ Fallo en intento " . ($attempt + 1) . ": " . $lastError);
            }
            
            // Si falla y aún quedan intentos, esperar un poco antes de reintentar
            if ($attempt < self::MAX_RETRIES) {
                error_log("ShellyAPI::closeBarrier() - Esperando antes del siguiente intento...");
                usleep(self::RETRY_DELAY_MICROSECONDS);
            }
        }
        
        // Si falló todos los intentos, asegurar que se devuelve el último error
        if (!$result['success']) {
            $result['error'] = $lastError;
            error_log("ShellyAPI::closeBarrier() - ❌ Falló después de todos los intentos. Error final: " . $lastError);
        }
        
        return $result;
    }
    
    /**
     * Obtiene el estado del dispositivo Shelly via Cloud API
     * @return array Resultado de la operación
     */
    public static function getStatus() {
        if (!SHELLY_ENABLED) {
            return ['success' => false, 'error' => 'Shelly deshabilitado'];
        }
        
        $settings = self::getSettings();
        $url = 'https://' . $settings['server'] . '/device/status';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'auth_key' => $settings['auth_token'],
            'id' => $settings['device_id']
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
    public static function getRelayStatus($channel = 0) {
        return self::getStatus();
    }
}
