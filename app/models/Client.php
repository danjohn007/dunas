<?php
/**
 * Modelo Client
 */
class Client {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT c.*, u.username FROM clients c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['client_type'])) {
            $sql .= " AND c.client_type = ?";
            $params[] = $filters['client_type'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT c.*, u.username FROM clients c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO clients (user_id, business_name, rfc_curp, address, phone, email, client_type, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['user_id'] ?? null,
            $data['business_name'],
            $data['rfc_curp'],
            $data['address'],
            $data['phone'],
            $data['email'],
            $data['client_type'],
            $data['status'] ?? 'active'
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE clients SET business_name = ?, rfc_curp = ?, address = ?, phone = ?, 
                email = ?, client_type = ?, status = ? WHERE id = ?";
        
        $params = [
            $data['business_name'],
            $data['rfc_curp'],
            $data['address'],
            $data['phone'],
            $data['email'],
            $data['client_type'],
            $data['status'],
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM clients WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    public function getTransactionHistory($clientId, $limit = 10) {
        $sql = "SELECT t.*, al.ticket_code, u.plate_number 
                FROM transactions t
                JOIN access_logs al ON t.access_log_id = al.id
                JOIN units u ON al.unit_id = u.id
                WHERE t.client_id = ?
                ORDER BY t.transaction_date DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$clientId, $limit]);
    }
}
