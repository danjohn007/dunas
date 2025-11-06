<?php
/**
 * Servicio HikvisionAnprService
 * Gestiona la detección y validación de placas vehiculares
 */

require_once APP_PATH . '/helpers/Database.php';
require_once APP_PATH . '/helpers/TextUtils.php';
require_once APP_PATH . '/helpers/HikvisionAPI.php';
require_once APP_PATH . '/models/HikvisionDevice.php';
require_once APP_PATH . '/models/Unit.php';

class HikvisionAnprService {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtiene y procesa la última detección ANPR
     * @return array ['success' => bool, 'detection' => array|null, 'error' => string|null]
     */
    public function getLatestDetection() {
        try {
            // Obtener primer dispositivo LPR habilitado
            $device = HikvisionDevice::getFirstByType($this->db, 'camera_lpr');
            
            if (!$device) {
                return [
                    'success' => false,
                    'detection' => null,
                    'error' => 'No hay cámaras LPR configuradas'
                ];
            }
            
            // Llamar a la API para obtener el último evento
            $result = HikvisionAPI::fetchLatestAnprEvent($this->db, $device);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'detection' => null,
                    'error' => $result['error']
                ];
            }
            
            // Si no hay placa detectada, retornar null
            if (!$result['plate']) {
                return [
                    'success' => true,
                    'detection' => null,
                    'error' => null
                ];
            }
            
            // Normalizar la placa
            $normalizedPlate = TextUtils::normalizePlate($result['plate']);
            
            // Buscar match en units
            $unit = $this->findUnitByPlate($normalizedPlate);
            
            // Guardar detección en base de datos
            $detectionId = $this->saveDetection(
                $normalizedPlate,
                $result['confidence'],
                $device['id'],
                $unit ? $unit['id'] : null,
                $unit ? 1 : 0,
                $result['payload']
            );
            
            return [
                'success' => true,
                'detection' => [
                    'id' => $detectionId,
                    'plate_text' => $normalizedPlate,
                    'original_plate' => $result['plate'],
                    'confidence' => $result['confidence'],
                    'device_id' => $device['id'],
                    'unit_id' => $unit ? $unit['id'] : null,
                    'is_match' => $unit ? true : false,
                    'unit' => $unit
                ],
                'error' => null
            ];
            
        } catch (Exception $e) {
            error_log("Error en getLatestDetection: " . $e->getMessage());
            return [
                'success' => false,
                'detection' => null,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Busca una unidad por placa normalizada
     * @param string $normalizedPlate Placa normalizada
     * @return array|null Datos de la unidad o null si no se encuentra
     */
    private function findUnitByPlate($normalizedPlate) {
        try {
            // Obtener todas las unidades activas
            $units = $this->db->fetchAll(
                "SELECT * FROM units WHERE status = 'active'"
            );
            
            // Buscar match
            foreach ($units as $unit) {
                $unitPlate = TextUtils::normalizePlate($unit['plate_number']);
                if ($unitPlate === $normalizedPlate) {
                    return $unit;
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error al buscar unidad: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Guarda una detección en la base de datos
     * @param string $plateText Texto de la placa
     * @param float|null $confidence Nivel de confianza
     * @param int $deviceId ID del dispositivo
     * @param int|null $unitId ID de la unidad (si hay match)
     * @param int $isMatch 1 si hay match, 0 si no
     * @param string|null $payloadJson JSON del payload completo
     * @return int ID de la detección insertada
     */
    private function saveDetection($plateText, $confidence, $deviceId, $unitId, $isMatch, $payloadJson) {
        try {
            $this->db->execute(
                "INSERT INTO detected_plates (plate_text, confidence, captured_at, device_id, unit_id, is_match, payload_json, status) VALUES (?, ?, NOW(), ?, ?, ?, ?, 'new')",
                [
                    $plateText,
                    $confidence,
                    $deviceId,
                    $unitId,
                    $isMatch,
                    $payloadJson
                ]
            );
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Error al guardar detección: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtiene la última detección guardada
     * @return array|null Datos de la última detección o null
     */
    public function getLastSavedDetection() {
        try {
            $detection = $this->db->fetchOne(
                "SELECT dp.*, u.plate_number as unit_plate, u.brand, u.model, u.capacity_liters 
                 FROM detected_plates dp 
                 LEFT JOIN units u ON dp.unit_id = u.id 
                 ORDER BY dp.captured_at DESC 
                 LIMIT 1"
            );
            
            if ($detection) {
                return [
                    'success' => true,
                    'detection' => $detection,
                    'error' => null
                ];
            }
            
            return [
                'success' => true,
                'detection' => null,
                'error' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'detection' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}
