<?php
/**
 * Modelo DetectedPlates
 * Gestiona las detecciones de placas vehiculares
 */

require_once APP_PATH . '/helpers/Database.php';
require_once APP_PATH . '/helpers/TextUtils.php';

class DetectedPlates {
    
    /**
     * Inserta una nueva detección de placa en la base de datos
     * 
     * @param PDO|Database $db Instancia de base de datos
     * @param string $plate Texto de la placa
     * @param int|null $unitId ID de la unidad si hay coincidencia
     * @param float|null $confidence Nivel de confianza de la detección
     * @param int|null $deviceId ID del dispositivo que detectó la placa
     * @return int ID de la detección insertada
     */
    public static function insert($db, string $plate, ?int $unitId = null, ?float $confidence = null, ?int $deviceId = null) {
        // Normalizar la placa
        $normalized = TextUtils::normalizePlate($plate);
        
        // Determinar si hay coincidencia
        $isMatch = $unitId ? 1 : 0;
        
        // Preparar la consulta
        if ($db instanceof Database) {
            $connection = $db->getConnection();
        } else {
            $connection = $db;
        }
        
        $stmt = $connection->prepare(
            "INSERT INTO detected_plates (plate_text, is_match, captured_at, unit_id, confidence, device_id, status) 
             VALUES (?, ?, NOW(), ?, ?, ?, 'new')"
        );
        
        $stmt->execute([
            $normalized,
            $isMatch,
            $unitId,
            $confidence,
            $deviceId
        ]);
        
        return $connection->lastInsertId();
    }
    
    /**
     * Obtiene la última detección registrada
     * 
     * @param PDO|Database $db Instancia de base de datos
     * @return array|null Datos de la detección o null
     */
    public static function getLatest($db) {
        if ($db instanceof Database) {
            $connection = $db->getConnection();
        } else {
            $connection = $db;
        }
        
        $stmt = $connection->prepare(
            "SELECT dp.*, u.plate_number as unit_plate, u.brand, u.model 
             FROM detected_plates dp 
             LEFT JOIN units u ON dp.unit_id = u.id 
             ORDER BY dp.captured_at DESC 
             LIMIT 1"
        );
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca una unidad por placa normalizada
     * 
     * @param PDO|Database $db Instancia de base de datos
     * @param string $normalizedPlate Placa normalizada
     * @return array|null Datos de la unidad o null
     */
    public static function findUnitByPlate($db, string $normalizedPlate) {
        if ($db instanceof Database) {
            $connection = $db->getConnection();
        } else {
            $connection = $db;
        }
        
        // Obtener todas las unidades activas
        $stmt = $connection->prepare("SELECT * FROM units WHERE status = 'active'");
        $stmt->execute();
        $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar coincidencia
        foreach ($units as $unit) {
            $unitPlate = TextUtils::normalizePlate($unit['plate_number']);
            if ($unitPlate === $normalizedPlate) {
                return $unit;
            }
        }
        
        return null;
    }
}
