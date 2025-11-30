<?php
/**
 * Modelo CapacityCost (Costos por Capacidad)
 */
class CapacityCost {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get count of capacity costs
     */
    public function count($activeOnly = false) {
        $sql = "SELECT COUNT(*) as total FROM capacity_costs";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $result = $this->db->fetchOne($sql);
        return (int)$result['total'];
    }
    
    public function getAll($activeOnly = true) {
        $sql = "SELECT * FROM capacity_costs";
        $params = [];
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY capacity_liters ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM capacity_costs WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getByCapacity($capacityLiters) {
        $sql = "SELECT * FROM capacity_costs WHERE capacity_liters = ? AND is_active = 1";
        return $this->db->fetchOne($sql, [$capacityLiters]);
    }
    
    public function getCostForCapacity($capacityLiters) {
        $result = $this->getByCapacity($capacityLiters);
        return $result ? $result['cost'] : null;
    }
    
    public function create($data) {
        $sql = "INSERT INTO capacity_costs (capacity_liters, cost, description, is_active) 
                VALUES (?, ?, ?, ?)";
        
        $params = [
            $data['capacity_liters'],
            $data['cost'],
            $data['description'] ?? null,
            $data['is_active'] ?? 1
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE capacity_costs SET capacity_liters = ?, cost = ?, description = ?, is_active = ? WHERE id = ?";
        
        $params = [
            $data['capacity_liters'],
            $data['cost'],
            $data['description'] ?? null,
            $data['is_active'] ?? 1,
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM capacity_costs WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Get capacity options formatted for dropdown
     */
    public function getOptionsForSelect() {
        $capacities = $this->getAll(true);
        $options = [];
        
        foreach ($capacities as $cap) {
            $options[] = [
                'value' => $cap['capacity_liters'],
                'label' => number_format($cap['capacity_liters']) . ' L - $' . number_format($cap['cost'], 2),
                'cost' => $cap['cost'],
                'description' => $cap['description']
            ];
        }
        
        return $options;
    }
}
