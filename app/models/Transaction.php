<?php
/**
 * Modelo Transaction (Transacciones y Pagos)
 */
class Transaction {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT t.*, c.business_name as client_name, al.ticket_code
                FROM transactions t
                JOIN clients c ON t.client_id = c.id
                JOIN access_logs al ON t.access_log_id = al.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['payment_status'])) {
            $sql .= " AND t.payment_status = ?";
            $params[] = $filters['payment_status'];
        }
        
        if (!empty($filters['payment_method'])) {
            $sql .= " AND t.payment_method = ?";
            $params[] = $filters['payment_method'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(t.transaction_date) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(t.transaction_date) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY t.transaction_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT t.*, c.business_name as client_name, c.phone as client_phone,
                al.ticket_code, al.entry_datetime, al.exit_datetime
                FROM transactions t
                JOIN clients c ON t.client_id = c.id
                JOIN access_logs al ON t.access_log_id = al.id
                WHERE t.id = ?";
        
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO transactions (access_log_id, client_id, total_amount, liters_supplied, 
                price_per_liter, payment_method, payment_status, transaction_date, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $params = [
            $data['access_log_id'],
            $data['client_id'],
            $data['total_amount'],
            $data['liters_supplied'],
            $data['price_per_liter'],
            $data['payment_method'],
            $data['payment_status'] ?? 'pending',
            $data['notes'] ?? null
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE transactions SET total_amount = ?, payment_method = ?, 
                payment_status = ?, notes = ? WHERE id = ?";
        
        $params = [
            $data['total_amount'],
            $data['payment_method'],
            $data['payment_status'],
            $data['notes'] ?? null,
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function updateStatus($id, $status) {
        $sql = "UPDATE transactions SET payment_status = ? WHERE id = ?";
        return $this->db->execute($sql, [$status, $id]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM transactions WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    public function getByAccessLog($accessLogId) {
        $sql = "SELECT * FROM transactions WHERE access_log_id = ?";
        return $this->db->fetchOne($sql, [$accessLogId]);
    }
    
    public function getRevenueByPeriod($dateFrom, $dateTo) {
        $sql = "SELECT 
                    DATE(transaction_date) as date,
                    SUM(total_amount) as revenue,
                    SUM(liters_supplied) as liters,
                    COUNT(*) as transactions
                FROM transactions
                WHERE DATE(transaction_date) BETWEEN ? AND ?
                AND payment_status = 'paid'
                GROUP BY DATE(transaction_date)
                ORDER BY date";
        
        return $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
    }
}
