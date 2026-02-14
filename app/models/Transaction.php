<?php
/**
 * Modelo Transaction (Transacciones y Pagos)
 */
class Transaction {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    private function buildFilterConditions($filters, &$params) {
        $conditions = [];
        
        if (!empty($filters['payment_status'])) {
            // Use a CASE statement in the condition to match the actual_payment_status logic
            $conditions[] = "CASE 
                WHEN t.payment_method = 'voucher' AND v.id IS NOT NULL THEN v.payment_status
                ELSE t.payment_status
            END = ?";
            $params[] = $filters['payment_status'];
        }
        
        if (!empty($filters['payment_method'])) {
            $conditions[] = "t.payment_method = ?";
            $params[] = $filters['payment_method'];
        }
        
        if (!empty($filters['client_id'])) {
            $conditions[] = "t.client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(t.transaction_date) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(t.transaction_date) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(al.ticket_code LIKE ? OR v.qr_code LIKE ? OR CONCAT(COALESCE(v.serie, ''), '-', COALESCE(v.folio, '')) LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        return $conditions;
    }
    
    public function getAll($filters = []) {
        // Usar la vista que calcula el payment_status correcto basado en vouchers
        $sql = "SELECT t.*, c.business_name as client_name, al.ticket_code,
                v.qr_code as voucher_code, v.serie as voucher_serie, v.folio as voucher_folio,
                CASE 
                    WHEN t.payment_method = 'voucher' AND v.id IS NOT NULL THEN v.payment_status
                    ELSE t.payment_status
                END as actual_payment_status
                FROM transactions t
                JOIN clients c ON t.client_id = c.id
                JOIN access_logs al ON t.access_log_id = al.id
                LEFT JOIN vouchers v ON v.used_by_access_log_id = al.id AND t.payment_method = 'voucher'
                WHERE 1=1";
        $params = [];
        
        $conditions = $this->buildFilterConditions($filters, $params);
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY t.transaction_date DESC";
        
        // Add pagination if specified
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$filters['limit'];
            $params[] = (int)$filters['offset'];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getTotalAmount($filters = []) {
        $sql = "SELECT COALESCE(SUM(t.total_amount), 0) as total
                FROM transactions t
                JOIN clients c ON t.client_id = c.id
                JOIN access_logs al ON t.access_log_id = al.id
                LEFT JOIN vouchers v ON v.used_by_access_log_id = al.id AND t.payment_method = 'voucher'
                WHERE 1=1";
        $params = [];
        
        $conditions = $this->buildFilterConditions($filters, $params);
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as count
                FROM transactions t
                JOIN clients c ON t.client_id = c.id
                JOIN access_logs al ON t.access_log_id = al.id
                LEFT JOIN vouchers v ON v.used_by_access_log_id = al.id AND t.payment_method = 'voucher'
                WHERE 1=1";
        $params = [];
        
        $conditions = $this->buildFilterConditions($filters, $params);
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] ?? 0;
    }
    
    public function getById($id) {
        $sql = "SELECT t.*, c.business_name as client_name, c.phone as client_phone,
                al.ticket_code, al.entry_datetime, al.exit_datetime,
                v.qr_code as voucher_code, v.serie as voucher_serie, v.folio as voucher_folio
                FROM transactions t
                JOIN clients c ON t.client_id = c.id
                JOIN access_logs al ON t.access_log_id = al.id
                LEFT JOIN vouchers v ON v.used_by_access_log_id = al.id AND t.payment_method = 'voucher'
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
