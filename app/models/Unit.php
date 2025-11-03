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
        $sql = "SELECT u.*, c.business_name as client_name, d.full_name as driver_name 
                FROM units u
                LEFT JOIN clients c ON u.client_id = c.id
                LEFT JOIN drivers d ON u.driver_id = d.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['client_id'])) {
            $sql .= " AND u.client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT u.*, c.business_name as client_name, d.full_name as driver_name 
                FROM units u
                LEFT JOIN clients c ON u.client_id = c.id
                LEFT JOIN drivers d ON u.driver_id = d.id
                WHERE u.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO units (client_id, driver_id, plate_number, capacity_liters, brand, model, year, serial_number, photo, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['client_id'],
            $data['driver_id'],
            $data['plate_number'],
            $data['capacity_liters'],
            $data['brand'],
            $data['model'],
            $data['year'] ?? null,
            $data['serial_number'] ?? null,
            $data['photo'] ?? null,
            $data['status'] ?? 'active'
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE units SET client_id = ?, driver_id = ?, plate_number = ?, capacity_liters = ?, brand = ?, model = ?, 
                year = ?, serial_number = ?, status = ?";
        
        $params = [
            $data['client_id'],
            $data['driver_id'],
            $data['plate_number'],
            $data['capacity_liters'],
            $data['brand'],
            $data['model'],
            $data['year'] ?? null,
            $data['serial_number'] ?? null,
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
    
    public function findByPlateNumber($plateNumber) {
        $sql = "SELECT u.*, c.business_name as client_name, d.full_name as driver_name 
                FROM units u
                LEFT JOIN clients c ON u.client_id = c.id
                LEFT JOIN drivers d ON u.driver_id = d.id
                WHERE u.plate_number = ?";
        return $this->db->fetchOne($sql, [$plateNumber]);
    }
    
    public function getDriversByClient($clientId) {
        $sql = "SELECT * FROM drivers WHERE client_id = ? AND status = 'active' ORDER BY full_name ASC";
        return $this->db->fetchAll($sql, [$clientId]);
    }
    
    public function searchByPlateNumber($search) {
        $sql = "SELECT * FROM units WHERE plate_number LIKE ? ORDER BY plate_number ASC LIMIT 10";
        return $this->db->fetchAll($sql, ['%' . $search . '%']);
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
