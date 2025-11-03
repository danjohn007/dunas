<?php
/**
 * Helper para integración con API de Hikvision
 * Modelo: IDS-2CD7A46G0/P-IZHS(C)
 * 
 * Documentación: https://www.hikvision.com/en/support/tools/hitools/?type=IP
 */
class HikvisionAPI {
    
    /**
     * Obtener lectura de placa desde cámara Hikvision
     * 
     * @return array ['success' => bool, 'plate' => string, 'error' => string]
     */
    public static function readLicensePlate() {
        try {
            $settings = new Settings();
            $apiUrl = $settings->get('hikvision_api_url');
            $username = $settings->get('hikvision_username');
            $password = $settings->get('hikvision_password');
            
            // Si no hay configuración, retornar sin error pero sin lectura
            if (empty($apiUrl)) {
                return [
                    'success' => true,
                    'plate' => null,
                    'error' => null
                ];
            }
            
            // Endpoint para obtener la última detección de placa
            // Según documentación de Hikvision ISAPI
            $endpoint = '/ISAPI/Traffic/channels/1/vehicleDetect/plates';
            $url = rtrim($apiUrl, '/') . $endpoint;
            
            // Configurar solicitud HTTP
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 segundos de timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // 3 segundos para conectar
            
            // Autenticación digest si está configurada
            if (!empty($username) && !empty($password)) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            }
            
            // Deshabilitar verificación SSL si es entorno de desarrollo
            // En producción, configurar certificados apropiados
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'plate' => null,
                    'error' => 'Error de conexión: ' . $curlError
                ];
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'plate' => null,
                    'error' => 'Error HTTP: ' . $httpCode
                ];
            }
            
            // Parsear respuesta XML de Hikvision
            $plate = self::parseHikvisionResponse($response);
            
            if ($plate) {
                return [
                    'success' => true,
                    'plate' => strtoupper(trim($plate)),
                    'error' => null
                ];
            } else {
                return [
                    'success' => true,
                    'plate' => null,
                    'error' => 'No se detectó placa'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'plate' => null,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Parsear respuesta XML de Hikvision
     * 
     * @param string $xmlResponse
     * @return string|null
     */
    private static function parseHikvisionResponse($xmlResponse) {
        try {
            // Deshabilitar errores de XML temporalmente
            $previousValue = libxml_use_internal_errors(true);
            
            $xml = simplexml_load_string($xmlResponse);
            
            if ($xml === false) {
                libxml_use_internal_errors($previousValue);
                return null;
            }
            
            // Estructura típica de respuesta Hikvision para detección de placas:
            // <VehicleDetectList>
            //   <VehicleDetect>
            //     <plateNumber>ABC123</plateNumber>
            //   </VehicleDetect>
            // </VehicleDetectList>
            
            // Intentar diferentes estructuras de XML según modelo
            if (isset($xml->VehicleDetect->plateNumber)) {
                $plate = (string)$xml->VehicleDetect->plateNumber;
            } elseif (isset($xml->plateNumber)) {
                $plate = (string)$xml->plateNumber;
            } elseif (isset($xml->plate)) {
                $plate = (string)$xml->plate;
            } else {
                // Buscar recursivamente el primer elemento que contenga "plate"
                $plate = self::findPlateInXML($xml);
            }
            
            libxml_use_internal_errors($previousValue);
            
            return !empty($plate) ? $plate : null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Buscar recursivamente información de placa en XML
     * 
     * @param SimpleXMLElement $xml
     * @return string|null
     */
    private static function findPlateInXML($xml) {
        if (!$xml) return null;
        
        foreach ($xml->children() as $child) {
            $name = $child->getName();
            
            // Buscar nodos que contengan "plate" en el nombre
            if (stripos($name, 'plate') !== false) {
                return (string)$child;
            }
            
            // Buscar recursivamente
            $result = self::findPlateInXML($child);
            if ($result) return $result;
        }
        
        return null;
    }
    
    /**
     * Comparar dos placas y determinar si hay discrepancia
     * 
     * @param string $plate1
     * @param string $plate2
     * @return bool
     */
    public static function hasDiscrepancy($plate1, $plate2) {
        if (empty($plate1) || empty($plate2)) {
            return false;
        }
        
        // Normalizar placas (mayúsculas, sin espacios, sin guiones)
        $plate1 = strtoupper(preg_replace('/[\s\-]/', '', $plate1));
        $plate2 = strtoupper(preg_replace('/[\s\-]/', '', $plate2));
        
        return $plate1 !== $plate2;
    }
    
    /**
     * Obtener configuración de cámara desde settings
     * 
     * @return array
     */
    public static function getCameraSettings() {
        $settings = new Settings();
        
        return [
            'api_url' => $settings->get('hikvision_api_url') ?? '',
            'username' => $settings->get('hikvision_username') ?? '',
            'enabled' => !empty($settings->get('hikvision_api_url'))
        ];
    }
    
    /**
     * Probar conexión con cámara Hikvision
     * 
     * @return array ['success' => bool, 'message' => string]
     */
    public static function testConnection() {
        try {
            $settings = new Settings();
            $apiUrl = $settings->get('hikvision_api_url');
            $username = $settings->get('hikvision_username');
            $password = $settings->get('hikvision_password');
            
            if (empty($apiUrl)) {
                return [
                    'success' => false,
                    'message' => 'No se ha configurado la URL de la cámara Hikvision'
                ];
            }
            
            // Probar endpoint de información del dispositivo
            $endpoint = '/ISAPI/System/deviceInfo';
            $url = rtrim($apiUrl, '/') . $endpoint;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            
            if (!empty($username) && !empty($password)) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            }
            
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'message' => 'Error de conexión: ' . $curlError
                ];
            }
            
            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con cámara Hikvision'
                ];
            } elseif ($httpCode === 401) {
                return [
                    'success' => false,
                    'message' => 'Error de autenticación. Verifique usuario y contraseña'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error HTTP: ' . $httpCode
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
