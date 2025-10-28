<?php
/**
 * Modelo User
 */
class User {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (username, password, full_name, email, role, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $params = [
            $data['username'],
            $hashedPassword,
            $data['full_name'],
            $data['email'],
            $data['role'],
            $data['status'] ?? 'active'
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE users SET full_name = ?, email = ?, role = ?, status = ? WHERE id = ?";
        
        $params = [
            $data['full_name'],
            $data['email'],
            $data['role'],
            $data['status'],
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function updatePassword($id, $newPassword) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->execute($sql, [$hashedPassword, $id]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
}
