<?php
/**
 * Modelo Unit (Pipas)
 */
class Unit {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT * FROM units WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM units WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO units (plate_number, capacity_liters, brand, model, year, serial_number, photo, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['plate_number'],
            $data['capacity_liters'],
            $data['brand'],
            $data['model'],
            $data['year'],
            $data['serial_number'],
            $data['photo'] ?? null,
            $data['status'] ?? 'active'
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE units SET plate_number = ?, capacity_liters = ?, brand = ?, model = ?, 
                year = ?, serial_number = ?, status = ?";
        
        $params = [
            $data['plate_number'],
            $data['capacity_liters'],
            $data['brand'],
            $data['model'],
            $data['year'],
            $data['serial_number'],
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
        $sql = "DELETE FROM units WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    public function getMaintenanceHistory($unitId, $limit = 10) {
        $sql = "SELECT * FROM maintenance_history 
                WHERE unit_id = ? 
                ORDER BY maintenance_date DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$unitId, $limit]);
    }
    
    public function addMaintenance($data) {
        $sql = "INSERT INTO maintenance_history (unit_id, maintenance_date, description, cost, performed_by) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['unit_id'],
            $data['maintenance_date'],
            $data['description'],
            $data['cost'] ?? null,
            $data['performed_by']
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
}
