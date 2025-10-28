<?php
/**
 * Clase ShellyAPI - Integración con Shelly Relay SHELLPRO4PM
 */
class ShellyAPI {
    
    private static function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = SHELLY_API_URL . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, SHELLY_API_TIMEOUT);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($error) {
            error_log("Shelly API Error: " . $error);
            return ['success' => false, 'error' => $error];
        }
        
        if ($httpCode !== 200) {
            error_log("Shelly API HTTP Error: " . $httpCode);
            return ['success' => false, 'error' => 'HTTP ' . $httpCode];
        }
        
        $decoded = json_decode($response, true);
        return ['success' => true, 'data' => $decoded];
    }
    
    public static function openBarrier() {
        // Activar relay para abrir barrera
        $result = self::makeRequest('/relay/' . SHELLY_RELAY_OPEN . '?turn=on', 'GET');
        
        if ($result['success']) {
            // Apagar después de 2 segundos
            sleep(2);
            self::makeRequest('/relay/' . SHELLY_RELAY_OPEN . '?turn=off', 'GET');
        }
        
        return $result;
    }
    
    public static function closeBarrier() {
        // Activar relay para cerrar barrera
        $result = self::makeRequest('/relay/' . SHELLY_RELAY_CLOSE . '?turn=on', 'GET');
        
        if ($result['success']) {
            // Apagar después de 2 segundos
            sleep(2);
            self::makeRequest('/relay/' . SHELLY_RELAY_CLOSE . '?turn=off', 'GET');
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
