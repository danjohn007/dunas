<?php
/**
 * Modelo Voucher (Vales de Agua)
 * Gestiona la generación y validación de vales de agua
 */
class Voucher {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todos los vales con filtros opcionales
     */
    public function getAll($filters = []) {
        $sql = "SELECT v.*, u.username as created_by_name
                FROM vouchers v
                LEFT JOIN users u ON v.created_by = u.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND v.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['serie'])) {
            $sql .= " AND v.serie = ?";
            $params[] = $filters['serie'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (v.voucher_code LIKE ? OR v.serie LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY v.created_at DESC, v.serie ASC, v.folio ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtener un vale por su ID
     */
    public function getById($id) {
        $sql = "SELECT v.*, u.username as created_by_name,
                al.ticket_code as used_in_ticket
                FROM vouchers v
                LEFT JOIN users u ON v.created_by = u.id
                LEFT JOIN access_logs al ON v.used_by_access_log_id = al.id
                WHERE v.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Obtener un vale por su código QR o código de vale
     * Maneja tanto el formato QR (VALE:CODE:LITERS) como el código directo (SERIE-FOLIO)
     */
    public function getByQRCode($qrCode) {
        // Primero intentar buscar directamente por QR code
        $sql = "SELECT v.* FROM vouchers v WHERE v.qr_code = ?";
        $voucher = $this->db->fetchOne($sql, [$qrCode]);
        
        if ($voucher) {
            return $voucher;
        }
        
        // Si no se encuentra, intentar extraer el código del formato VALE:CODE:LITERS
        if (strpos($qrCode, 'VALE:') === 0) {
            $parts = explode(':', $qrCode);
            if (count($parts) >= 2) {
                $voucherCode = $parts[1];
                return $this->getByVoucherCode($voucherCode);
            }
        }
        
        // Si no tiene el prefijo VALE:, intentar buscar por código directamente
        return $this->getByVoucherCode($qrCode);
    }
    
    /**
     * Obtener un vale por su código de vale (SERIE-FOLIO)
     */
    public function getByVoucherCode($voucherCode) {
        $sql = "SELECT v.* FROM vouchers v WHERE v.voucher_code = ?";
        return $this->db->fetchOne($sql, [$voucherCode]);
    }
    
    /**
     * Verificar si un vale ya existe (serie + folio)
     */
    public function exists($serie, $folio) {
        $sql = "SELECT COUNT(*) as count FROM vouchers WHERE serie = ? AND folio = ?";
        $result = $this->db->fetchOne($sql, [$serie, $folio]);
        return $result['count'] > 0;
    }
    
    /**
     * Generar múltiples vales consecutivos
     * @param string $serie Serie de los vales (letras)
     * @param int $folioInicio Folio inicial
     * @param int $cantidad Cantidad de vales a generar
     * @param int $capacityLiters Capacidad en litros
     * @param int $createdBy ID del usuario que genera los vales
     * @return array ['success' => bool, 'vouchers' => array, 'error' => string]
     */
    public function generateVouchers($serie, $folioInicio, $cantidad, $capacityLiters, $createdBy) {
        try {
            $vouchers = [];
            $this->db->beginTransaction();
            
            // Validar que la serie sea solo letras
            if (!preg_match('/^[A-Za-z]+$/', $serie)) {
                throw new Exception('La serie debe contener solo letras');
            }
            
            // Normalizar serie a mayúsculas
            $serie = strtoupper($serie);
            
            // Validar datos
            if ($folioInicio < 1 || $cantidad < 1 || $capacityLiters < 1) {
                throw new Exception('Los valores deben ser mayores a cero');
            }
            
            if ($cantidad > 1000) {
                throw new Exception('No se pueden generar más de 1000 vales a la vez');
            }
            
            // Verificar si algún folio ya existe
            for ($i = 0; $i < $cantidad; $i++) {
                $folio = $folioInicio + $i;
                if ($this->exists($serie, $folio)) {
                    throw new Exception("El vale {$serie}-{$folio} ya existe");
                }
            }
            
            // Generar vales
            for ($i = 0; $i < $cantidad; $i++) {
                $folio = $folioInicio + $i;
                $voucherCode = $serie . '-' . str_pad($folio, 4, '0', STR_PAD_LEFT);
                
                // Generar QR code único
                // El QR contendrá el código del vale para ser escaneado por HikVision
                $qrCode = 'VALE:' . $voucherCode . ':' . $capacityLiters . 'L';
                
                $sql = "INSERT INTO vouchers (serie, folio, voucher_code, capacity_liters, qr_code, created_by)
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $this->db->execute($sql, [
                    $serie,
                    $folio,
                    $voucherCode,
                    $capacityLiters,
                    $qrCode,
                    $createdBy
                ]);
                
                $vouchers[] = [
                    'id' => $this->db->lastInsertId(),
                    'serie' => $serie,
                    'folio' => $folio,
                    'voucher_code' => $voucherCode,
                    'capacity_liters' => $capacityLiters,
                    'qr_code' => $qrCode
                ];
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'vouchers' => $vouchers,
                'error' => null
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'vouchers' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Marcar un vale como usado
     */
    public function markAsUsed($voucherId, $accessLogId) {
        $sql = "UPDATE vouchers 
                SET status = 'used', 
                    used_at = NOW(), 
                    used_by_access_log_id = ? 
                WHERE id = ? AND status = 'active'";
        return $this->db->execute($sql, [$accessLogId, $voucherId]);
    }
    
    /**
     * Validar un vale para su uso
     * @param string $qrCode Código QR escaneado
     * @return array ['valid' => bool, 'voucher' => array, 'error' => string]
     */
    public function validateVoucher($qrCode) {
        $voucher = $this->getByQRCode($qrCode);
        
        if (!$voucher) {
            return [
                'valid' => false,
                'voucher' => null,
                'error' => 'Vale no encontrado'
            ];
        }
        
        if ($voucher['status'] === 'used') {
            return [
                'valid' => false,
                'voucher' => $voucher,
                'error' => 'Este vale ya ha sido utilizado'
            ];
        }
        
        if ($voucher['status'] === 'cancelled') {
            return [
                'valid' => false,
                'voucher' => $voucher,
                'error' => 'Este vale ha sido cancelado'
            ];
        }
        
        return [
            'valid' => true,
            'voucher' => $voucher,
            'error' => null
        ];
    }
    
    /**
     * Cancelar un vale
     */
    public function cancel($voucherId) {
        $sql = "UPDATE vouchers SET status = 'cancelled' WHERE id = ? AND status = 'active'";
        return $this->db->execute($sql, [$voucherId]);
    }
    
    /**
     * Obtener estadísticas de vales
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'used' THEN 1 ELSE 0 END) as used,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'active' THEN capacity_liters ELSE 0 END) as total_liters_available
                FROM vouchers";
        return $this->db->fetchOne($sql);
    }
}
