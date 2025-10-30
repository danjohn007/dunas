<?php
/**
 * Modelo Driver (Choferes)
 */
class Driver {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT * FROM drivers WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM drivers WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO drivers (full_name, license_number, license_expiry, phone, photo, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['full_name'],
            $data['license_number'],
            $data['license_expiry'],
            $data['phone'],
            $data['photo'] ?? null,
            $data['status'] ?? 'active'
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE drivers SET full_name = ?, license_number = ?, license_expiry = ?, 
                phone = ?, status = ?";
        
        $params = [
            $data['full_name'],
            $data['license_number'],
            $data['license_expiry'],
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
}
