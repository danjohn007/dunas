<?php
/**
 * Helper para integración con API de Hikvision
 * Modelo: IDS-2CD7A46G0/P-IZHS(C)
 * 
 * Documentación: https://www.hikvision.com/en/support/tools/hitools/?type=IP
 */
class HikvisionAPI {
    
    /**
     * Asegurar que el dispositivo tiene un token válido para API Cloud
     * @param Database $db Instancia de base de datos
     * @param array $device Datos del dispositivo
     * @return array ['success' => bool, 'token' => string|null, 'error' => string|null]
     */
    public static function ensureToken($db, $device) {
        require_once APP_PATH . '/models/HikvisionDevice.php';
        
        // Verificar si el dispositivo usa Cloud API
        if (empty($device['api_key']) || empty($device['api_secret']) || empty($device['token_endpoint'])) {
            return [
                'success' => false,
                'token' => null,
                'error' => 'Dispositivo no configurado para Cloud API'
            ];
        }
        
        // Verificar si el token actual es válido
        if (!HikvisionDevice::needsTokenRefresh($device)) {
            return [
                'success' => true,
                'token' => $device['access_token'],
                'error' => null
            ];
        }
        
        // Solicitar nuevo token
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $device['token_endpoint']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            
            // Configurar verificación SSL
            $verifySSL = (bool)($device['verify_ssl'] ?? 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySSL ? 2 : 0);
            
            // Payload para obtener token
            $payload = json_encode([
                'appKey' => $device['api_key'],
                'secretKey' => $device['api_secret']
            ]);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'token' => null,
                    'error' => 'Error de conexión: ' . $curlError
                ];
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'token' => null,
                    'error' => 'Error HTTP: ' . $httpCode
                ];
            }
            
            // Parsear respuesta
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['accessToken'])) {
                return [
                    'success' => false,
                    'token' => null,
                    'error' => 'Respuesta inválida del servidor'
                ];
            }
            
            $accessToken = $data['accessToken'];
            $expireTime = $data['expireTime'] ?? (time() * 1000 + 7200000); // Default 2 horas
            $areaDomain = $data['areaDomain'] ?? $device['area_domain'] ?? null;
            
            // Guardar token en base de datos
            HikvisionDevice::saveAccessToken($db, $device['id'], $accessToken, $expireTime, $areaDomain);
            
            return [
                'success' => true,
                'token' => $accessToken,
                'error' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'token' => null,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener el último evento ANPR desde API Cloud
     * @param Database $db Instancia de base de datos
     * @param array $device Datos del dispositivo
     * @return array ['success' => bool, 'plate' => string|null, 'confidence' => float|null, 'payload' => string|null, 'error' => string|null]
     */
    public static function fetchLatestAnprEvent($db, $device) {
        // 1. Asegurar que tenemos un token válido
        $tokenResult = self::ensureToken($db, $device);
        if (!$tokenResult['success']) {
            return [
                'success' => false,
                'plate' => null,
                'confidence' => null,
                'payload' => null,
                'error' => $tokenResult['error']
            ];
        }
        
        $accessToken = $tokenResult['token'];
        
        // Recargar device para obtener area_domain actualizado
        require_once APP_PATH . '/models/HikvisionDevice.php';
        $device = HikvisionDevice::getById($db, $device['id']);
        
        if (empty($device['area_domain']) || empty($device['device_index_code'])) {
            return [
                'success' => false,
                'plate' => null,
                'confidence' => null,
                'payload' => null,
                'error' => 'area_domain o device_index_code no configurados'
            ];
        }
        
        // 2. Solicitar eventos ANPR
        try {
            $url = rtrim($device['area_domain'], '/') . '/api/hpcgw/v1/anpr/vehicleEvents';
            
            // Calcular ventana de tiempo (últimos 10 segundos)
            $endTime = time() * 1000; // Milisegundos
            $startTime = $endTime - 10000; // 10 segundos atrás
            
            $payload = json_encode([
                'deviceIndexCode' => $device['device_index_code'],
                'pageNo' => 1,
                'pageSize' => 1,
                'order' => 'desc',
                'startTime' => $startTime,
                'endTime' => $endTime
            ]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            
            // Configurar verificación SSL
            $verifySSL = (bool)($device['verify_ssl'] ?? 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySSL ? 2 : 0);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'plate' => null,
                    'confidence' => null,
                    'payload' => null,
                    'error' => 'Error de conexión: ' . $curlError
                ];
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'plate' => null,
                    'confidence' => null,
                    'payload' => null,
                    'error' => 'Error HTTP: ' . $httpCode
                ];
            }
            
            // 3. Parsear respuesta
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['list']) || empty($data['list'])) {
                // No hay eventos recientes, pero no es un error
                return [
                    'success' => true,
                    'plate' => null,
                    'confidence' => null,
                    'payload' => json_encode($data),
                    'error' => null
                ];
            }
            
            // Obtener el primer evento (más reciente)
            $event = $data['list'][0];
            $plate = $event['vehicleInfo']['plateNo'] ?? null;
            $confidence = isset($event['confidence']) ? (float)$event['confidence'] : null;
            
            return [
                'success' => true,
                'plate' => $plate,
                'confidence' => $confidence,
                'payload' => json_encode($event),
                'error' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'plate' => null,
                'confidence' => null,
                'payload' => null,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener lectura de placa desde cámara Hikvision
     * Intenta leer de los dispositivos configurados en orden de prioridad
     * 
     * @param int|null $deviceId ID específico del dispositivo (opcional)
     * @return array ['success' => bool, 'plate' => string, 'error' => string, 'device_id' => int|null]
     */
    public static function readLicensePlate($deviceId = null) {
        try {
            require_once APP_PATH . '/models/HikvisionDevice.php';
            $db = Database::getInstance();
            
            // Si se especifica un dispositivo, usar solo ese
            if ($deviceId) {
                $device = HikvisionDevice::getById($db, $deviceId);
                if ($device && $device['device_type'] === 'camera_lpr') {
                    return self::readFromDevice($device);
                }
                return [
                    'success' => false,
                    'plate' => null,
                    'error' => 'Dispositivo no encontrado o no es una cámara LPR',
                    'device_id' => null
                ];
            }
            
            // Obtener todas las cámaras LPR habilitadas
            $cameras = HikvisionDevice::allEnabled($db, 'camera_lpr');
            
            // Si no hay cámaras configuradas, verificar configuración legacy
            if (empty($cameras)) {
                return self::readLicensePlateLegacy();
            }
            
            // Intentar leer de cada cámara en orden
            foreach ($cameras as $camera) {
                $result = self::readFromDevice($camera);
                if ($result['success'] && !empty($result['plate'])) {
                    return $result;
                }
            }
            
            // Si ninguna cámara pudo leer, retornar sin error pero sin lectura
            return [
                'success' => true,
                'plate' => null,
                'error' => null,
                'device_id' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'plate' => null,
                'error' => $e->getMessage(),
                'device_id' => null
            ];
        }
    }
    
    /**
     * Lee placa desde un dispositivo específico
     * Soporta tanto ISAPI (local) como Cloud API
     * 
     * @param array $device Datos del dispositivo
     * @return array ['success' => bool, 'plate' => string, 'error' => string, 'device_id' => int]
     */
    private static function readFromDevice($device) {
        try {
            // Determinar si es Cloud API o ISAPI
            $isCloudAPI = !empty($device['api_key']) && !empty($device['device_index_code']);
            
            if ($isCloudAPI) {
                // Usar Cloud API
                $db = Database::getInstance();
                $result = self::fetchLatestAnprEvent($db, $device);
                
                if (!$result['success']) {
                    return [
                        'success' => false,
                        'plate' => null,
                        'error' => $result['error'],
                        'device_id' => $device['id']
                    ];
                }
                
                if ($result['plate']) {
                    return [
                        'success' => true,
                        'plate' => strtoupper(trim($result['plate'])),
                        'error' => null,
                        'device_id' => $device['id']
                    ];
                } else {
                    return [
                        'success' => true,
                        'plate' => null,
                        'error' => 'No se detectó placa',
                        'device_id' => $device['id']
                    ];
                }
            }
            
            // Modo ISAPI (legacy)
            $apiUrl = $device['api_url'];
            $username = $device['username'];
            $password = $device['password'];
            
            // Si no hay configuración, retornar sin error pero sin lectura
            if (empty($apiUrl)) {
                return [
                    'success' => true,
                    'plate' => null,
                    'error' => null,
                    'device_id' => $device['id']
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
            
            // Configurar verificación SSL
            $verifySSL = (bool)$device['verify_ssl'];
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySSL ? 2 : 0);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'plate' => null,
                    'error' => 'Error de conexión: ' . $curlError,
                    'device_id' => $device['id']
                ];
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'plate' => null,
                    'error' => 'Error HTTP: ' . $httpCode,
                    'device_id' => $device['id']
                ];
            }
            
            // Parsear respuesta XML de Hikvision
            $plate = self::parseHikvisionResponse($response);
            
            if ($plate) {
                return [
                    'success' => true,
                    'plate' => strtoupper(trim($plate)),
                    'error' => null,
                    'device_id' => $device['id']
                ];
            } else {
                return [
                    'success' => true,
                    'plate' => null,
                    'error' => 'No se detectó placa',
                    'device_id' => $device['id']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'plate' => null,
                'error' => $e->getMessage(),
                'device_id' => isset($device['id']) ? $device['id'] : null
            ];
        }
    }
    
    /**
     * Método legacy para leer placa desde configuración antigua
     * Mantiene compatibilidad con sistemas que aún usan settings
     * 
     * @return array ['success' => bool, 'plate' => string, 'error' => string, 'device_id' => null]
     */
    private static function readLicensePlateLegacy() {
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
                    'error' => null,
                    'device_id' => null
                ];
            }
            
            // Simular estructura de dispositivo para reutilizar readFromDevice
            $device = [
                'id' => null,
                'api_url' => $apiUrl,
                'username' => $username,
                'password' => $password,
                'verify_ssl' => $settings->get('hikvision_verify_ssl', 'false') === 'true' ? 1 : 0
            ];
            
            return self::readFromDevice($device);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'plate' => null,
                'error' => $e->getMessage(),
                'device_id' => null
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
     * Lee código de barras desde lector HikVision
     * 
     * @param int|null $deviceId ID específico del dispositivo (opcional)
     * @return array ['success' => bool, 'barcode' => string, 'error' => string, 'device_id' => int|null]
     */
    public static function readBarcode($deviceId = null) {
        try {
            require_once APP_PATH . '/models/HikvisionDevice.php';
            $db = Database::getInstance();
            
            // Si se especifica un dispositivo, usar solo ese
            if ($deviceId) {
                $device = HikvisionDevice::getById($db, $deviceId);
                if ($device && $device['device_type'] === 'barcode_reader') {
                    return self::readBarcodeFromDevice($device);
                }
                return [
                    'success' => false,
                    'barcode' => null,
                    'error' => 'Dispositivo no encontrado o no es un lector de código de barras',
                    'device_id' => null
                ];
            }
            
            // Obtener todos los lectores de código de barras habilitados
            $readers = HikvisionDevice::allEnabled($db, 'barcode_reader');
            
            if (empty($readers)) {
                return [
                    'success' => true,
                    'barcode' => null,
                    'error' => 'No hay lectores de código de barras configurados',
                    'device_id' => null
                ];
            }
            
            // Intentar leer de cada lector en orden
            foreach ($readers as $reader) {
                $result = self::readBarcodeFromDevice($reader);
                if ($result['success'] && !empty($result['barcode'])) {
                    return $result;
                }
            }
            
            // Si ningún lector pudo leer, retornar sin error pero sin lectura
            return [
                'success' => true,
                'barcode' => null,
                'error' => null,
                'device_id' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'barcode' => null,
                'error' => $e->getMessage(),
                'device_id' => null
            ];
        }
    }
    
    /**
     * Lee código de barras desde un dispositivo específico
     * 
     * @param array $device Datos del dispositivo
     * @return array ['success' => bool, 'barcode' => string, 'error' => string, 'device_id' => int]
     */
    private static function readBarcodeFromDevice($device) {
        try {
            $apiUrl = $device['api_url'];
            $username = $device['username'];
            $password = $device['password'];
            
            if (empty($apiUrl)) {
                return [
                    'success' => true,
                    'barcode' => null,
                    'error' => null,
                    'device_id' => $device['id']
                ];
            }
            
            // Endpoint para obtener la última lectura de código de barras
            $endpoint = '/ISAPI/AccessControl/AcsEvent?format=json';
            $url = rtrim($apiUrl, '/') . $endpoint;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout más largo para lectores de código de barras
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 segundos para conectar
            
            if (!empty($username) && !empty($password)) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            }
            
            $verifySSL = (bool)$device['verify_ssl'];
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySSL ? 2 : 0);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'barcode' => null,
                    'error' => 'Error de conexión: ' . $curlError,
                    'device_id' => $device['id']
                ];
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'barcode' => null,
                    'error' => 'Error HTTP: ' . $httpCode,
                    'device_id' => $device['id']
                ];
            }
            
            // Parsear respuesta JSON/XML
            $barcode = self::parseBarcodeResponse($response);
            
            if ($barcode) {
                return [
                    'success' => true,
                    'barcode' => trim($barcode),
                    'error' => null,
                    'device_id' => $device['id']
                ];
            } else {
                return [
                    'success' => true,
                    'barcode' => null,
                    'error' => 'No se detectó código de barras',
                    'device_id' => $device['id']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'barcode' => null,
                'error' => $e->getMessage(),
                'device_id' => isset($device['id']) ? $device['id'] : null
            ];
        }
    }
    
    /**
     * Parsear respuesta de código de barras desde HikVision
     * 
     * @param string $response Respuesta del dispositivo
     * @return string|null Código de barras o null
     */
    private static function parseBarcodeResponse($response) {
        try {
            // Intentar primero como JSON
            $json = json_decode($response, true);
            if ($json && isset($json['AcsEvent']['cardNo'])) {
                return (string)$json['AcsEvent']['cardNo'];
            }
            
            // Si no es JSON, intentar como XML
            $previousValue = libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);
            
            if ($xml !== false) {
                if (isset($xml->cardNo)) {
                    libxml_use_internal_errors($previousValue);
                    return (string)$xml->cardNo;
                }
                if (isset($xml->AcsEvent->cardNo)) {
                    libxml_use_internal_errors($previousValue);
                    return (string)$xml->AcsEvent->cardNo;
                }
            }
            
            libxml_use_internal_errors($previousValue);
            return null;
            
        } catch (Exception $e) {
            return null;
        }
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
            
            // Configurar verificación SSL
            $verifySSL = $settings->get('hikvision_verify_ssl', 'false') === 'true';
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySSL ? 2 : 0);
            
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
