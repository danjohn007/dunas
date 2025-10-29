<?php
/**
 * Clase ShellyAPI - Integración con Shelly Relay SHELLPRO4PM
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
                'api_url' => $allSettings['shelly_api_url'] ?? SHELLY_API_URL,
                'open_url' => $allSettings['shelly_open_url'] ?? SHELLY_OPEN_URL,
                'close_url' => $allSettings['shelly_close_url'] ?? SHELLY_CLOSE_URL,
            ];
        }
        return $settings;
    }
    
    private static function makeRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_EXTENDED);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        
        // Configuraciones específicas para conexiones externas
        curl_setopt($ch, CURLOPT_USERAGENT, 'ShellyAPI/1.0 (Dunas Control System)');
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 300); // Cache DNS por 5 minutos
        curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
        curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 120);
        curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 60);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);
        
        // Log detallado para debugging
        error_log("Shelly Request - URL: $url, HTTP: $httpCode, Time: {$totalTime}s");
        
        if ($error) {
            error_log("Shelly API Error: " . $error . " (URL: " . $url . ")");
            return ['success' => false, 'error' => $error, 'url' => $url, 'http_code' => $httpCode];
        }
        
        if ($httpCode !== 200) {
            error_log("Shelly API HTTP Error: " . $httpCode . " (URL: " . $url . ")");
            return ['success' => false, 'error' => 'HTTP ' . $httpCode, 'url' => $url, 'http_code' => $httpCode];
        }
        
        $decoded = json_decode($response, true);
        return ['success' => true, 'data' => $decoded, 'response_time' => $totalTime];
    }
    
    public static function openBarrier() {
        // Verificar si Shelly está habilitado
        if (!SHELLY_ENABLED) {
            return ['success' => true, 'message' => 'Shelly deshabilitado - modo simulación'];
        }
        
        $settings = self::getSettings();
        // Hacer llamada para abrir barrera (on=false)
        $result = null;
        
        // Log para debug
        error_log("ShellyAPI::openBarrier() - Intentando abrir con URL: " . $settings['open_url']);
        
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            error_log("ShellyAPI::openBarrier() - Intento " . ($attempt + 1) . " de " . (self::MAX_RETRIES + 1));
            $result = self::makeRequest($settings['open_url'], 'GET');
            
            if ($result['success']) {
                error_log("ShellyAPI::openBarrier() - Éxito en intento " . ($attempt + 1));
                break;
            } else {
                error_log("ShellyAPI::openBarrier() - Fallo en intento " . ($attempt + 1) . ": " . ($result['error'] ?? 'Error desconocido'));
            }
            
            // Si falla y aún quedan intentos, esperar un poco antes de reintentar
            if ($attempt < self::MAX_RETRIES) {
                usleep(self::RETRY_DELAY_MICROSECONDS);
            }
        }
        
        return $result;
    }
    
    public static function closeBarrier() {
        // Verificar si Shelly está habilitado
        if (!SHELLY_ENABLED) {
            return ['success' => true, 'message' => 'Shelly deshabilitado - modo simulación'];
        }
        
        $settings = self::getSettings();
        // Hacer llamada para cerrar barrera (on=true)
        $result = null;
        
        // Log para debug
        error_log("ShellyAPI::closeBarrier() - Intentando cerrar con URL: " . $settings['close_url']);
        
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            error_log("ShellyAPI::closeBarrier() - Intento " . ($attempt + 1) . " de " . (self::MAX_RETRIES + 1));
            $result = self::makeRequest($settings['close_url'], 'GET');
            
            if ($result['success']) {
                error_log("ShellyAPI::closeBarrier() - Éxito en intento " . ($attempt + 1));
                break;
            } else {
                error_log("ShellyAPI::closeBarrier() - Fallo en intento " . ($attempt + 1) . ": " . ($result['error'] ?? 'Error desconocido'));
            }
            
            // Si falla y aún quedan intentos, esperar un poco antes de reintentar
            if ($attempt < self::MAX_RETRIES) {
                usleep(self::RETRY_DELAY_MICROSECONDS);
            }
        }
        
        return $result;
    }
    
    public static function getStatus() {
        $settings = self::getSettings();
        $statusUrl = rtrim($settings['api_url'], '/') . '/status';
        return self::makeRequest($statusUrl, 'GET');
    }
    
    public static function getRelayStatus($channel) {
        $settings = self::getSettings();
        $relayUrl = rtrim($settings['api_url'], '/') . '/relay/' . $channel;
        return self::makeRequest($relayUrl, 'GET');
    }
}
