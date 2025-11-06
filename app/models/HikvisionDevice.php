<?php
/**
 * Modelo HikvisionDevice
 * Gestiona múltiples dispositivos HikVision (cámaras LPR y lectores de código de barras)
 */
class HikvisionDevice {
    
    /**
     * Obtiene todos los dispositivos habilitados
     * @param Database $db Instancia de base de datos
     * @param string $deviceType Filtro por tipo de dispositivo (opcional)
     * @return array Lista de dispositivos habilitados
     */
    public static function allEnabled($db, $deviceType = null) {
        $sql = "SELECT * FROM hikvision_devices WHERE is_enabled=1";
        $params = [];
        
        if ($deviceType !== null) {
            $sql .= " AND device_type = ?";
            $params[] = $deviceType;
        }
        
        $sql .= " ORDER BY sort_order, id";
        return $db->fetchAll($sql, $params);
    }
    
    /**
     * Obtiene todos los dispositivos (habilitados y deshabilitados)
     * @param Database $db Instancia de base de datos
     * @return array Lista de todos los dispositivos
     */
    public static function getAll($db) {
        return $db->fetchAll("SELECT * FROM hikvision_devices ORDER BY sort_order, id");
    }
    
    /**
     * Obtiene un dispositivo por ID
     * @param Database $db Instancia de base de datos
     * @param int $id ID del dispositivo
     * @return array|null Dispositivo o null si no existe
     */
    public static function getById($db, $id) {
        return $db->fetchOne("SELECT * FROM hikvision_devices WHERE id = ?", [$id]);
    }
    
    /**
     * Obtiene el primer dispositivo habilitado de un tipo específico
     * @param Database $db Instancia de base de datos
     * @param string $deviceType Tipo de dispositivo ('camera_lpr' o 'barcode_reader')
     * @return array|null Dispositivo o null si no existe
     */
    public static function getFirstByType($db, $deviceType) {
        return $db->fetchOne(
            "SELECT * FROM hikvision_devices WHERE device_type = ? AND is_enabled = 1 ORDER BY sort_order, id LIMIT 1",
            [$deviceType]
        );
    }
    
    /**
     * Actualiza múltiples dispositivos en batch (insert/update/delete)
     * @param Database $db Instancia de base de datos
     * @param array $rows Array de dispositivos con sus datos
     * @throws Exception Si ocurre un error en la transacción
     */
    public static function upsertBatch($db, $rows) {
        $db->beginTransaction();
        try {
            $existing = $db->fetchAll("SELECT id FROM hikvision_devices");
            $existingIds = array_column($existing, 'id');

            $seen = [];
            foreach ($rows as $r) {
                $id = isset($r['id']) && $r['id'] !== '' && $r['id'] > 0 ? (int)$r['id'] : null;
                
                if ($id) {
                    $seen[] = $id;
                    // Actualizar dispositivo existente
                    $db->execute(
                        "UPDATE hikvision_devices SET name=?, device_type=?, api_url=?, username=?, password=?, verify_ssl=?, area=?, is_enabled=?, updated_at=NOW() WHERE id=?",
                        [
                            $r['name'],
                            $r['device_type'],
                            $r['api_url'],
                            $r['username'] ?? null,
                            $r['password'] ?? null,
                            (int)($r['verify_ssl'] ?? 0),
                            $r['area'] ?? null,
                            (int)($r['is_enabled'] ?? 1),
                            $id
                        ]
                    );
                } else {
                    // Insertar nuevo dispositivo
                    $db->execute(
                        "INSERT INTO hikvision_devices (name, device_type, api_url, username, password, verify_ssl, area, is_enabled, sort_order) VALUES (?,?,?,?,?,?,?,?,?)",
                        [
                            $r['name'],
                            $r['device_type'],
                            $r['api_url'],
                            $r['username'] ?? null,
                            $r['password'] ?? null,
                            (int)($r['verify_ssl'] ?? 0),
                            $r['area'] ?? null,
                            (int)($r['is_enabled'] ?? 1),
                            (int)($r['sort_order'] ?? 0)
                        ]
                    );
                    $id = $db->lastInsertId();
                    $seen[] = $id;
                }
            }
            
            // Borrar dispositivos que ya no aparecen en la lista
            if (!empty($existingIds) && !empty($rows)) {
                $toDelete = array_diff($existingIds, $seen);
                if (!empty($toDelete)) {
                    $in = implode(',', array_fill(0, count($toDelete), '?'));
                    $db->execute("DELETE FROM hikvision_devices WHERE id IN ($in)", array_values($toDelete));
                }
            }
            
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Prueba la conexión con un dispositivo HikVision
     * @param array $device Datos del dispositivo
     * @return array Resultado de la prueba ['success' => bool, 'message' => string]
     */
    public static function testConnection($device) {
        try {
            if (empty($device['api_url'])) {
                return [
                    'success' => false,
                    'message' => 'No se ha configurado la URL del dispositivo HikVision'
                ];
            }
            
            // Probar endpoint de información del dispositivo
            $endpoint = '/ISAPI/System/deviceInfo';
            $url = rtrim($device['api_url'], '/') . $endpoint;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout más largo para prueba de conexión
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 segundos para conectar
            
            if (!empty($device['username']) && !empty($device['password'])) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                curl_setopt($ch, CURLOPT_USERPWD, $device['username'] . ':' . $device['password']);
            }
            
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (bool)$device['verify_ssl']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $device['verify_ssl'] ? 2 : 0);
            
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
                    'message' => 'Conexión exitosa con dispositivo HikVision'
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
