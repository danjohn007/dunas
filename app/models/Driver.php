<?php
/**
 * Modelo Driver (Choferes)
 */
class Driver {
    
    // Constants for default values
    const DEFAULT_DRIVER_NAME = 'Chofer General';
    const DEFAULT_DRIVER_PHONE = 'Sin teléfono';
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT d.*, c.business_name as client_name 
                FROM drivers d
                LEFT JOIN clients c ON d.client_id = c.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND d.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['client_id'])) {
            $sql .= " AND d.client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        $sql .= " ORDER BY d.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT d.*, c.business_name as client_name 
                FROM drivers d
                LEFT JOIN clients c ON d.client_id = c.id
                WHERE d.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO drivers (client_id, full_name, license_number, license_expiry, phone, photo, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['client_id'],
            $data['full_name'],
            $data['license_number'] ?? null,
            $data['license_expiry'] ?? null,
            $data['phone'],
            $data['photo'] ?? null,
            $data['status'] ?? 'active'
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE drivers SET client_id = ?, full_name = ?, license_number = ?, license_expiry = ?, 
                phone = ?, status = ?";
        
        $params = [
            $data['client_id'],
            $data['full_name'],
            $data['license_number'] ?? null,
            $data['license_expiry'] ?? null,
            $data['phone'],
            $data['status']
        ];
        
        if (!empty($data['photo'])) {
            $sql .= ", photo = ?";
            $params[] = $data['photo'];
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        return $this->db->execute($sql, $params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM drivers WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    public function getAssignedUnit($driverId) {
        $sql = "SELECT u.*, dua.assigned_date 
                FROM driver_unit_assignments dua
                JOIN units u ON dua.unit_id = u.id
                WHERE dua.driver_id = ? AND dua.end_date IS NULL
                ORDER BY dua.assigned_date DESC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$driverId]);
    }
    
    public function assignUnit($driverId, $unitId) {
        // Finalizar asignaciones anteriores
        $sql = "UPDATE driver_unit_assignments SET end_date = CURDATE() 
                WHERE driver_id = ? AND end_date IS NULL";
        $this->db->execute($sql, [$driverId]);
        
        // Crear nueva asignación
        $sql = "INSERT INTO driver_unit_assignments (driver_id, unit_id, assigned_date) 
                VALUES (?, ?, CURDATE())";
        $this->db->execute($sql, [$driverId, $unitId]);
        
        return $this->db->lastInsertId();
    }
    
    public function getExpiringLicenses($days = 30) {
        $sql = "SELECT * FROM drivers 
                WHERE status = 'active' 
                AND license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY license_expiry ASC";
        
        return $this->db->fetchAll($sql, [$days]);
    }
    
    /**
     * Verificar si un teléfono ya existe para otro chofer
     * @param string $phone Número de teléfono
     * @param int|null $excludeId ID del chofer a excluir (para edición)
     * @return bool
     */
    public function phoneExists($phone, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM drivers WHERE phone = ?";
        $params = [$phone];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Obtiene o crea un chofer genérico para un cliente
     * Usado cuando se registra con vale pero sin datos del chofer
     */
    public function getOrCreateGenericDriver($clientId) {
        // Buscar si ya existe un chofer genérico para este cliente
        $sql = "SELECT id FROM drivers 
                WHERE client_id = ? AND full_name = ? 
                LIMIT 1";
        $driver = $this->db->fetchOne($sql, [$clientId, self::DEFAULT_DRIVER_NAME]);
        
        if ($driver) {
            return $driver['id'];
        }
        
        // Crear un chofer genérico
        $data = [
            'client_id' => $clientId,
            'full_name' => self::DEFAULT_DRIVER_NAME,
            'license_number' => null,
            'license_expiry' => null,
            'phone' => self::DEFAULT_DRIVER_PHONE,
            'status' => 'active'
        ];
        
        return $this->create($data);
    }
}
