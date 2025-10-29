<?php
/**
 * Clase ShellyAPI - Integración con Shelly Relay SHELLPRO4PM
 */
class ShellyAPI {
    
    private static function getSettings() {
        static $settings = null;
        if ($settings === null) {
            require_once APP_PATH . '/models/Settings.php';
            $settingsModel = new Settings();
            $allSettings = $settingsModel->getAll();
            
            $settings = [
                'api_url' => $allSettings['shelly_api_url'] ?? SHELLY_API_URL,
                'relay_open' => $allSettings['shelly_relay_open'] ?? SHELLY_RELAY_OPEN,
                'relay_close' => $allSettings['shelly_relay_close'] ?? SHELLY_RELAY_CLOSE,
            ];
        }
        return $settings;
    }
    
    private static function makeRequest($endpoint, $method = 'GET', $data = null) {
        $settings = self::getSettings();
        $url = rtrim($settings['api_url'], '/') . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, SHELLY_API_TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, SHELLY_API_TIMEOUT);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($error) {
            error_log("Shelly API Error: " . $error . " (URL: " . $url . ")");
            return ['success' => false, 'error' => $error, 'url' => $url];
        }
        
        if ($httpCode !== 200) {
            error_log("Shelly API HTTP Error: " . $httpCode . " (URL: " . $url . ")");
            return ['success' => false, 'error' => 'HTTP ' . $httpCode, 'url' => $url];
        }
        
        $decoded = json_decode($response, true);
        return ['success' => true, 'data' => $decoded];
    }
    
    public static function openBarrier() {
        $settings = self::getSettings();
        // Activar relay para abrir barrera
        $result = self::makeRequest('/relay/' . $settings['relay_open'] . '?turn=on', 'GET');
        
        if ($result['success']) {
            // Apagar después de 2 segundos
            sleep(2);
            self::makeRequest('/relay/' . $settings['relay_open'] . '?turn=off', 'GET');
        }
        
        return $result;
    }
    
    public static function closeBarrier() {
        $settings = self::getSettings();
        // Activar relay para cerrar barrera
        $result = self::makeRequest('/relay/' . $settings['relay_close'] . '?turn=on', 'GET');
        
        if ($result['success']) {
            // Apagar después de 2 segundos
            sleep(2);
            self::makeRequest('/relay/' . $settings['relay_close'] . '?turn=off', 'GET');
        }
        
        return $result;
    }
    
    public static function getStatus() {
        return self::makeRequest('/status', 'GET');
    }
    
    public static function getRelayStatus($channel) {
        return self::makeRequest('/relay/' . $channel, 'GET');
    }
}
