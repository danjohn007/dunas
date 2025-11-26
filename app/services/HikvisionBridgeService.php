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
     * @return array ['success' => bool, 'message' => string]
     */
    public function createTicketUser($deviceUserId, $name, $pin, $hoursValid = 1) {
        try {
            $endpoint = $this->bridgeUrl . '/create-ticket-user';
            
            $data = [
                'device_user_id' => $deviceUserId,
                'name' => $name,
                'pin' => $pin,
                'hours_valid' => $hoursValid
            ];
            
            // Log de la petición
            error_log("=== HIKVISION BRIDGE REQUEST ===");
            error_log("Endpoint: $endpoint");
            error_log("Data: " . json_encode($data));
            
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
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            // Deshabilitar verificación SSL si es necesario
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
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
