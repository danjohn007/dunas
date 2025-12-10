<?php
/**
 * Servicio para comunicarse con el PC Puente (Bridge)
 * que gestiona la comunicación con el dispositivo Hikvision
 */
class HikvisionBridgeService {
    
    private $bridgeUrl;
    private $timeout;
    
    public function __construct() {
        // Usar configuración global si está disponible
        if (defined('HIKVISION_BRIDGE_URL')) {
            $this->bridgeUrl = HIKVISION_BRIDGE_URL;
        } else {
            $this->bridgeUrl = 'http://192.168.1.50:8080';
        }
        
        if (defined('HIKVISION_BRIDGE_TIMEOUT')) {
            $this->timeout = HIKVISION_BRIDGE_TIMEOUT;
        } else {
            $this->timeout = 10;
        }
    }
    
    /**
     * Crea un usuario en el dispositivo Hikvision vía el PC puente
     * 
     * @param string $deviceUserId ID único del usuario (ej: TKT-12345)
     * @param string $name Nombre del usuario
     * @param string $pin PIN de 4 dígitos
     * @param int $hoursValid Horas de validez (default: 1)
     * @param bool $async Si es true, envía la petición de forma asíncrona (no espera respuesta)
     * @return array ['success' => bool, 'message' => string]
     */
    public function createTicketUser($deviceUserId, $name, $pin, $hoursValid = 1, $async = true) {
        try {
            $endpoint = $this->bridgeUrl . '/create-ticket-user';
            
            $data = [
                'device_user_id' => $deviceUserId,
                'name' => $name,
                'pin' => $pin,
                'card_number' => $pin, // Usar el mismo número del PIN para la tarjeta
                'hours_valid' => $hoursValid
            ];
            
            // Log de la petición
            error_log("=== HIKVISION BRIDGE REQUEST ===");
            error_log("Endpoint: $endpoint");
            error_log("Data: " . json_encode($data));
            error_log("Async mode: " . ($async ? 'YES' : 'NO'));
            
            // Si es asíncrono, enviar y no esperar respuesta
            if ($async) {
                $this->sendAsyncRequest($endpoint, $data);
                error_log("✅ Petición enviada de forma asíncrona");
                return [
                    'success' => true,
                    'message' => 'Petición enviada al dispositivo (modo asíncrono)',
                    'async' => true
                ];
            }
            
            // Si es síncrono, esperar respuesta
            $response = $this->sendRequest($endpoint, $data);
            
            // Log de la respuesta
            error_log("Response: " . json_encode($response));
            error_log("=== END BRIDGE REQUEST ===");
            
            if ($response && isset($response['ok']) && $response['ok'] === true) {
                return [
                    'success' => true,
                    'message' => 'Usuario creado exitosamente en el dispositivo',
                    'response' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear usuario en el dispositivo',
                    'response' => $response
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en HikvisionBridgeService: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de comunicación con el puente: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Envía una petición POST al PC puente
     * 
     * @param string $url URL completa del endpoint
     * @param array $data Datos a enviar
     * @return array|null Respuesta decodificada o null si falla
     */
    private function sendRequest($url, $data) {
        $ch = curl_init($url);
        
        $jsonData = json_encode($data);
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
                'Accept: application/json',
                'User-Agent: HikvisionBridge/1.0',
                'Cache-Control: no-cache'
            ],
            // Deshabilitar verificación SSL si es necesario
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            // Forzar nueva conexión (no reutilizar)
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
            // No esperar respuesta completa del servidor
            CURLOPT_NOSIGNAL => 1
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Envía una petición POST de forma asíncrona (fire-and-forget)
     * No espera respuesta del servidor, retorna inmediatamente
     * 
     * @param string $url URL completa del endpoint
     * @param array $data Datos a enviar
     * @return bool True si la petición se envió
     */
    private function sendAsyncRequest($url, $data) {
        $ch = curl_init($url);
        
        $jsonData = json_encode($data);
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5, // Tiempo suficiente para enviar datos
            CURLOPT_CONNECTTIMEOUT => 2, // Conexión rápida pero razonable
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
                'Accept: application/json',
                'User-Agent: HikvisionBridge/1.0',
                'Cache-Control: no-cache'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_NOSIGNAL => 1
        ]);
        
        // Ejecutar y capturar cualquier error
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        // Log para debug
        if ($error) {
            error_log("⚠️ Async request warning: " . $error);
        }
        
        if ($httpCode > 0 && $httpCode != 200) {
            error_log("⚠️ Async request HTTP code: " . $httpCode);
        }
        
        return true;
    }
    
    /**
     * Elimina un usuario del dispositivo (si el endpoint está disponible)
     * 
     * @param string $deviceUserId ID del usuario a eliminar
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteUser($deviceUserId) {
        try {
            $endpoint = $this->bridgeUrl . '/delete-user';
            
            $data = [
                'device_user_id' => $deviceUserId
            ];
            
            $response = $this->sendRequest($endpoint, $data);
            
            if ($response && isset($response['ok']) && $response['ok'] === true) {
                return [
                    'success' => true,
                    'message' => 'Usuario eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar usuario'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verifica la conexión con el PC puente
     * 
     * @return bool True si el puente está accesible
     */
    public function testConnection() {
        try {
            $ch = curl_init($this->bridgeUrl);
            
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_NOBODY => true, // HEAD request
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode > 0; // Cualquier respuesta HTTP es válida
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Configura la URL del puente (útil para diferentes entornos)
     * 
     * @param string $url URL completa del puente
     */
    public function setBridgeUrl($url) {
        $this->bridgeUrl = rtrim($url, '/');
    }
    
    /**
     * Obtiene la URL actual del puente
     * 
     * @return string URL del puente
     */
    public function getBridgeUrl() {
        return $this->bridgeUrl;
    }
}
?>
