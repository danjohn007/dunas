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
        $sql = "INSERT INTO voucher_payments (client_id, serie, folio_inicio, folio_fin, amount, payment_date, payment_method, reference, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['client_id'],
            $data['serie'] ?? null,
            $data['folio_inicio'] ?? null,
            $data['folio_fin'] ?? null,
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
    public function getTotalPaidByClient($clientId, $dateFrom = null, $dateTo = null, $serie = null, $folioInicio = null, $folioFin = null) {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM voucher_payments
                WHERE client_id = ?";
        
        $params = [$clientId];
        
        // Si se especifica lote (serie + rango folios), filtrar por lote
        if ($serie !== null && $folioInicio !== null && $folioFin !== null) {
            $sql .= " AND serie = ? AND folio_inicio = ? AND folio_fin = ?";
            $params[] = $serie;
            $params[] = $folioInicio;
            $params[] = $folioFin;
        }
        
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
                SET serie = ?, folio_inicio = ?, folio_fin = ?, amount = ?, payment_date = ?, payment_method = ?, reference = ?, notes = ?
                WHERE id = ?";
        
        $params = [
            $data['serie'] ?? null,
            $data['folio_inicio'] ?? null,
            $data['folio_fin'] ?? null,
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
    
    /**
     * Obtiene el total de pagos registrados en un período (para incluir en totales del reporte financiero)
     */
    public function getTotalPaymentsInPeriod($dateFrom = null, $dateTo = null) {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM voucher_payments
                WHERE 1=1";
        
        $params = [];
        
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
}
