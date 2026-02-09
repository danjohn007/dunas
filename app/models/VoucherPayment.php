<?php
/**
 * Modelo VoucherPayment - Gestión de pagos de vales
 */
class VoucherPayment {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Registra un nuevo pago
     */
    public function create($data) {
        $sql = "INSERT INTO voucher_payments (client_id, amount, payment_date, payment_method, reference, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['client_id'],
            $data['amount'],
            $data['payment_date'],
            $data['payment_method'],
            $data['reference'] ?? null,
            $data['notes'] ?? null,
            $data['created_by']
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Obtiene todos los pagos de un cliente
     */
    public function getByClient($clientId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT vp.*, u.full_name as created_by_name, c.business_name as client_name
                FROM voucher_payments vp
                LEFT JOIN users u ON vp.created_by = u.id
                LEFT JOIN clients c ON vp.client_id = c.id
                WHERE vp.client_id = ?";
        
        $params = [$clientId];
        
        if ($dateFrom) {
            $sql .= " AND vp.payment_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND vp.payment_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY vp.payment_date DESC, vp.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtiene el total pagado por un cliente en un período
     */
    public function getTotalPaidByClient($clientId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM voucher_payments
                WHERE client_id = ?";
        
        $params = [$clientId];
        
        if ($dateFrom) {
            $sql .= " AND payment_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND payment_date <= ?";
            $params[] = $dateTo;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene un pago por ID
     */
    public function getById($id) {
        $sql = "SELECT vp.*, u.full_name as created_by_name, c.business_name as client_name
                FROM voucher_payments vp
                LEFT JOIN users u ON vp.created_by = u.id
                LEFT JOIN clients c ON vp.client_id = c.id
                WHERE vp.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Actualiza un pago
     */
    public function update($id, $data) {
        $sql = "UPDATE voucher_payments 
                SET amount = ?, payment_date = ?, payment_method = ?, reference = ?, notes = ?
                WHERE id = ?";
        
        $params = [
            $data['amount'],
            $data['payment_date'],
            $data['payment_method'],
            $data['reference'] ?? null,
            $data['notes'] ?? null,
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Elimina un pago
     */
    public function delete($id) {
        $sql = "DELETE FROM voucher_payments WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
}
