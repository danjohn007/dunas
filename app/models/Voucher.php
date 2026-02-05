<?php
/**
 * Modelo Voucher - Manejo de vales de suministro de agua
 */
class Voucher {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtiene todos los vales con filtros opcionales
     */
    public function getAll($filters = []) {
        $sql = "SELECT v.*, u.full_name as created_by_name, c.business_name as client_name
                FROM vouchers v 
                LEFT JOIN users u ON v.created_by = u.id 
                LEFT JOIN clients c ON v.client_id = c.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['serie'])) {
            $sql .= " AND v.serie = ?";
            $params[] = $filters['serie'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND v.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (v.serie LIKE ? OR v.folio LIKE ? OR v.qr_code LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY v.created_at DESC, v.serie ASC, v.folio ASC";
        
        // Add pagination if specified
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
            
            if (isset($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$filters['offset'];
            }
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtiene un vale por su ID
     */
    public function getById($id) {
        $sql = "SELECT v.*, u.full_name as created_by_name,
                       a.ticket_code as used_ticket_code,
                       c.business_name as client_name, c.phone as client_phone, c.address as client_address
                FROM vouchers v 
                LEFT JOIN users u ON v.created_by = u.id
                LEFT JOIN access_logs a ON v.used_by_access_log_id = a.id
                LEFT JOIN clients c ON v.client_id = c.id
                WHERE v.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Obtiene un vale por su código QR
     */
    public function getByQRCode($qrCode) {
        $sql = "SELECT v.*, u.full_name as created_by_name, c.business_name as client_name
                FROM vouchers v 
                LEFT JOIN users u ON v.created_by = u.id
                LEFT JOIN clients c ON v.client_id = c.id
                WHERE v.qr_code = ?";
        return $this->db->fetchOne($sql, [$qrCode]);
    }
    
    /**
     * Obtiene un vale por su serie y folio
     */
    public function getBySerieFolio($serie, $folio) {
        $sql = "SELECT v.*, u.full_name as created_by_name, c.business_name as client_name
                FROM vouchers v 
                LEFT JOIN users u ON v.created_by = u.id
                LEFT JOIN clients c ON v.client_id = c.id
                WHERE v.serie = ? AND v.folio = ?";
        return $this->db->fetchOne($sql, [strtoupper($serie), (int)$folio]);
    }
    
    /**
     * Obtiene un vale por código (puede ser QR completo o formato SERIE-FOLIO)
     */
    public function getByCode($code) {
        // Primero intentar búsqueda directa por QR code
        $voucher = $this->getByQRCode($code);
        if ($voucher) {
            return $voucher;
        }
        
        // Si no se encuentra, intentar parsear como SERIE-FOLIO
        $parts = explode('-', $code);
        if (count($parts) >= 2) {
            $serie = $parts[0];
            $folio = $parts[1];
            return $this->getBySerieFolio($serie, $folio);
        }
        
        return null;
    }
    
    /**
     * Verifica si un código QR ya existe
     */
    public function qrCodeExists($qrCode) {
        $sql = "SELECT COUNT(*) as count FROM vouchers WHERE qr_code = ?";
        $result = $this->db->fetchOne($sql, [$qrCode]);
        return $result['count'] > 0;
    }
    
    /**
     * Verifica si una combinación serie+folio ya existe
     */
    public function seriesFolioExists($serie, $folio) {
        $sql = "SELECT COUNT(*) as count FROM vouchers WHERE serie = ? AND folio = ?";
        $result = $this->db->fetchOne($sql, [$serie, $folio]);
        return $result['count'] > 0;
    }
    
    /**
     * Genera un código QR único (formato corto: SERIE-FOLIO)
     */
    private function generateUniqueQRCode($serie, $folio) {
        // Validar que serie y folio no estén vacíos
        if (empty($serie) || $folio < 1) {
            throw new Exception("Serie y folio son requeridos para generar el código QR");
        }
        
        // Formato corto: SERIE-FOLIO (sin timestamp)
        // El folio no lleva padding porque queremos mantenerlo corto
        $qrCode = strtoupper($serie) . '-' . $folio;
        
        // Verificar que el código no esté vacío
        if (empty($qrCode) || $qrCode === '-' || strlen($qrCode) < 4) {
            throw new Exception("Error al generar código QR válido");
        }
        
        // Verificar que no exista (debería no existir ya que serie+folio es único)
        if ($this->qrCodeExists($qrCode)) {
            throw new Exception("Ya existe un vale con el código QR {$qrCode}");
        }
        
        return $qrCode;
    }
    
    /**
     * Crea un nuevo vale
     */
    public function create($data) {
        // Validar campos requeridos
        if (empty($data['serie'])) {
            throw new Exception("La serie es requerida para crear un vale");
        }
        if (empty($data['folio']) || $data['folio'] < 1) {
            throw new Exception("El folio debe ser un número mayor a 0");
        }
        if (empty($data['capacity']) || $data['capacity'] < 1) {
            throw new Exception("La capacidad debe ser mayor a 0 litros");
        }
        if (empty($data['created_by'])) {
            throw new Exception("El usuario creador es requerido");
        }
        
        // Validar que serie+folio no existan
        if ($this->seriesFolioExists($data['serie'], $data['folio'])) {
            throw new Exception("Ya existe un vale con la serie {$data['serie']} y folio {$data['folio']}");
        }
        
        // Generar código QR único
        $qrCode = $this->generateUniqueQRCode($data['serie'], $data['folio']);
        
        // Última validación antes de insertar
        if (empty($qrCode) || $qrCode === '-' || strlen($qrCode) < 4) {
            throw new Exception("Error crítico: código QR generado inválido");
        }
        
        $sql = "INSERT INTO vouchers (serie, folio, qr_code, capacity, cost, payment_status, created_by, client_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $params = [
            strtoupper(trim($data['serie'])),
            (int)$data['folio'],
            $qrCode,
            (int)$data['capacity'],
            isset($data['cost']) ? (float)$data['cost'] : null,
            $data['payment_status'] ?? 'pending',
            $data['created_by'],
            $data['client_id'] ?? null
        ];
        
        $this->db->execute($sql, $params);
        $voucherId = $this->db->lastInsertId();
        
        return [
            'id' => $voucherId,
            'qr_code' => $qrCode
        ];
    }
    
    /**
     * Genera múltiples vales de forma consecutiva
     */
    public function generateBatch($serie, $startFolio, $quantity, $capacity, $createdBy, $clientId = null, $cost = null, $paymentStatus = 'pending') {
        $createdVouchers = [];
        $errors = [];
        
        for ($i = 0; $i < $quantity; $i++) {
            $folio = $startFolio + $i;
            
            try {
                $result = $this->create([
                    'serie' => $serie,
                    'folio' => $folio,
                    'capacity' => $capacity,
                    'created_by' => $createdBy,
                    'client_id' => $clientId,
                    'cost' => $cost,
                    'payment_status' => $paymentStatus
                ]);
                
                $createdVouchers[] = [
                    'id' => $result['id'],
                    'serie' => strtoupper($serie),
                    'folio' => $folio,
                    'qr_code' => $result['qr_code'],
                    'capacity' => $capacity
                ];
            } catch (Exception $e) {
                $errors[] = [
                    'serie' => $serie,
                    'folio' => $folio,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'created' => $createdVouchers,
            'errors' => $errors,
            'total' => count($createdVouchers)
        ];
    }
    
    /**
     * Marca un vale como usado
     */
    public function markAsUsed($id, $accessLogId) {
        $sql = "UPDATE vouchers 
                SET status = 'used', 
                    used_at = NOW(), 
                    used_by_access_log_id = ?
                WHERE id = ? AND status = 'active'";
        
        $affectedRows = $this->db->execute($sql, [$accessLogId, $id]);
        
        if ($affectedRows === 0) {
            throw new Exception("No se pudo marcar el vale como usado. El vale puede estar ya usado o cancelado.");
        }
        
        return true;
    }
    
    /**
     * Marca un vale como registrado (cuando se usa para acceso)
     */
    public function markAsRegistered($id, $accessLogId) {
        $sql = "UPDATE vouchers 
                SET status = 'registered', 
                    used_at = NOW(), 
                    used_by_access_log_id = ?
                WHERE id = ? AND status = 'active'";
        
        $affectedRows = $this->db->execute($sql, [$accessLogId, $id]);
        
        if ($affectedRows === 0) {
            throw new Exception("No se pudo registrar el vale. El vale puede estar ya usado, registrado o cancelado.");
        }
        
        return true;
    }
    
    /**
     * Cancela un vale
     */
    public function cancel($id) {
        $sql = "UPDATE vouchers 
                SET status = 'cancelled'
                WHERE id = ? AND status = 'active'";
        
        $affectedRows = $this->db->execute($sql, [$id]);
        
        if ($affectedRows === 0) {
            throw new Exception("No se pudo cancelar el vale. El vale puede estar ya usado o cancelado.");
        }
        
        return true;
    }
    
    /**
     * Obtiene estadísticas de vales
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'used' THEN 1 ELSE 0 END) as used,
                    SUM(CASE WHEN status = 'registered' THEN 1 ELSE 0 END) as registered,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'active' THEN capacity ELSE 0 END) as total_active_capacity
                FROM vouchers";
        
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Obtiene las series únicas de vales
     */
    public function getUniqueSeries() {
        $sql = "SELECT DISTINCT serie FROM vouchers ORDER BY serie ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Obtiene estadísticas financieras de vales
     */
    public function getFinancialStats($dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    SUM(CASE WHEN payment_status = 'paid' THEN cost ELSE 0 END) as total_paid,
                    SUM(CASE WHEN payment_status = 'pending' THEN cost ELSE 0 END) as total_pending,
                    SUM(cost) as total_amount,
                    COUNT(*) as total_vouchers,
                    SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count
                FROM vouchers 
                WHERE cost IS NOT NULL";
        
        $params = [];
        
        if ($dateFrom) {
            $sql .= " AND created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $sql .= " AND created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }
        
        return $this->db->fetchOne($sql, $params);
    }
}
