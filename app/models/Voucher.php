<?php
/**
 * Modelo Voucher - Manejo de vales de suministro de agua
 */
class Voucher {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function formatAccessPin($folio) {
        return str_pad((string)$folio, 4, '0', STR_PAD_LEFT);
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
     * Verifica si una combinación serie+folio (y opcionalmente capacidad) ya existe
     */
    public function seriesFolioExists($serie, $folio, $capacity = null) {
        if ($capacity !== null) {
            $sql = "SELECT COUNT(*) as count FROM vouchers WHERE serie = ? AND folio = ? AND capacity = ?";
            $result = $this->db->fetchOne($sql, [$serie, $folio, (int)$capacity]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM vouchers WHERE serie = ? AND folio = ?";
            $result = $this->db->fetchOne($sql, [$serie, $folio]);
        }
        return $result['count'] > 0;
    }
    
    /**
     * Obtiene el siguiente folio disponible para una serie (y opcionalmente capacidad)
     */
    public function getNextAvailableFolio($serie, $startFrom = 1, $capacity = null) {
        // Constante para límite de búsqueda de huecos en folios
        $MAX_GAP_SEARCH = 100;
        
        // Buscar el folio más alto en la serie (filtrado por capacidad si se indica)
        if ($capacity !== null) {
            $sql = "SELECT MAX(folio) as max_folio FROM vouchers WHERE serie = ? AND capacity = ?";
            $result = $this->db->fetchOne($sql, [$serie, (int)$capacity]);
        } else {
            $sql = "SELECT MAX(folio) as max_folio FROM vouchers WHERE serie = ?";
            $result = $this->db->fetchOne($sql, [$serie]);
        }
        
        $maxFolio = $result['max_folio'] ?? 0;
        
        // Si no hay folios, empezar desde startFrom
        if ($maxFolio === 0) {
            return $startFrom;
        }
        
        // Si el folio solicitado es mayor que el máximo, usarlo
        if ($startFrom > $maxFolio) {
            return $startFrom;
        }
        
        // Buscar el primer folio disponible desde startFrom
        for ($i = $startFrom; $i <= $maxFolio + $MAX_GAP_SEARCH; $i++) {
            if (!$this->seriesFolioExists($serie, $i, $capacity)) {
                return $i;
            }
        }
        
        // Si no se encuentra ningún hueco, devolver siguiente al máximo
        return $maxFolio + 1;
    }
    
    /**
     * Genera un código QR único
     * - imprenta: SERIE + capacidad + folio 4 dígitos (ej: A100000001)
     * - standard:  solo el folio de 4 dígitos (ej: 0001)
     */
    private function generateUniqueQRCode($folio, $serie = '', $capacity = 0, $voucherType = 'standard') {
        // Validar que folio no esté vacío
        if ($folio < 1) {
            throw new Exception("El folio debe ser mayor a 0 para generar el código QR");
        }
        
        $folioPadded = $this->formatAccessPin($folio);

        if ($voucherType === 'imprenta' && !empty($serie) && $capacity > 0) {
            // Formato: SERIE + capacidad + folio 4 dígitos (sin separadores)
            $qrCode = strtoupper(trim($serie)) . $capacity . $folioPadded;
        } else {
            // Formato estándar: solo los 4 dígitos del folio
            $qrCode = $folioPadded;
        }
        
        // Verificar que el código no esté vacío ni demasiado corto
        // (estándar mínimo = 4; imprenta mínimo = SERIE+capacity+4 dígitos, siempre > 4)
        if (empty($qrCode) || strlen($qrCode) < 4) {
            throw new Exception("Error al generar código QR válido");
        }
        
        // Verificar que no exista
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
        
        // Validar que serie+folio+capacity no existan
        if ($this->seriesFolioExists($data['serie'], $data['folio'], $data['capacity'])) {
            throw new Exception("Ya existe un vale con la serie {$data['serie']}, folio {$data['folio']} y capacidad {$data['capacity']} L");
        }
        
        $status = $data['status'] ?? 'active';
        $allowedStatus = ['active', 'used', 'cancelled', 'registered', 'pending_assignment'];
        if (!in_array($status, $allowedStatus, true)) {
            throw new Exception("Estado de vale no válido");
        }
        
        $voucherType = $data['voucher_type'] ?? 'standard';
        $allowedTypes = ['standard', 'imprenta'];
        if (!in_array($voucherType, $allowedTypes, true)) {
            throw new Exception("Tipo de vale no válido");
        }
        
        // Generar código QR único
        $qrCode = $this->generateUniqueQRCode(
            $data['folio'],
            $data['serie'] ?? '',
            $data['capacity'] ?? 0,
            $data['voucher_type'] ?? 'standard'
        );
        
        // Última validación antes de insertar
        if (empty($qrCode) || $qrCode === '-' || strlen($qrCode) < 4) {
            throw new Exception("Error crítico: código QR generado inválido");
        }
        
        $sql = "INSERT INTO vouchers (serie, folio, qr_code, capacity, cost, payment_status, created_by, client_id, status, voucher_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            strtoupper(trim($data['serie'])),
            (int)$data['folio'],
            $qrCode,
            (int)$data['capacity'],
            isset($data['cost']) ? (float)$data['cost'] : null,
            $data['payment_status'] ?? 'pending',
            $data['created_by'],
            $data['client_id'] ?? null,
            $status,
            $voucherType
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
     * Genera vales de imprenta (sin cliente y con PIN de 4 dígitos)
     *
     * @param string   $serie      Serie del vale (1-2 letras)
     * @param int      $startPin   Folio inicial
     * @param int      $quantity   Cantidad de vales a generar
     * @param int      $capacity   Capacidad en litros
     * @param int      $createdBy  ID del usuario creador
     * @param float|null $cost     Costo del vale (si null se guarda null)
     */
    public function generateImprentaBatch($serie, $startPin, $quantity, $capacity, $createdBy, $cost = null) {
        $createdVouchers = [];
        $errors = [];
        
        for ($i = 0; $i < $quantity; $i++) {
            $pin = $startPin + $i;
            
            try {
                $result = $this->create([
                    'serie' => strtoupper($serie),
                    'folio' => $pin,
                    'capacity' => $capacity,
                    'created_by' => $createdBy,
                    'client_id' => null,
                    'cost' => $cost,
                    'payment_status' => 'pending',
                    'status' => 'pending_assignment',
                    'voucher_type' => 'imprenta',
                    'pad_folio' => true
                ]);
                
                $createdVouchers[] = [
                    'id' => $result['id'],
                    'serie' => strtoupper($serie),
                    'folio' => $pin,
                    'qr_code' => $result['qr_code'],
                    'capacity' => $capacity
                ];
            } catch (Exception $e) {
                $errors[] = [
                    'serie' => $serie,
                    'folio' => $pin,
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
     * Relaciona vales de imprenta con una empresa y los activa
     */
    public function relateImprentaVouchers($serie, $folioStart, $folioEnd, $clientId) {
        $sql = "UPDATE vouchers
                SET client_id = ?, status = 'active'
                WHERE serie = ?
                  AND folio BETWEEN ? AND ?
                  AND voucher_type = 'imprenta'
                  AND status = 'pending_assignment'
                  AND client_id IS NULL";
        
        $stmt = $this->db->execute($sql, [
            (int)$clientId,
            strtoupper(trim($serie)),
            (int)$folioStart,
            (int)$folioEnd
        ]);
        
        return $stmt->rowCount();
    }

    /**
     * Obtiene series únicas por tipo de vale
     */
    public function getUniqueSeriesByType($voucherType) {
        $sql = "SELECT DISTINCT serie FROM vouchers WHERE voucher_type = ? ORDER BY serie ASC";
        return $this->db->fetchAll($sql, [$voucherType]);
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
        
        $affectedRows = $this->db->execute($sql, [$accessLogId, $id])->rowCount();
        
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
        
        $affectedRows = $this->db->execute($sql, [$accessLogId, $id])->rowCount();
        
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
        
        $affectedRows = $this->db->execute($sql, [$id])->rowCount();
        
        if ($affectedRows === 0) {
            throw new Exception("No se pudo cancelar el vale. El vale puede estar ya usado o cancelado.");
        }
        
        return true;
    }
    
    /**
     * Obtiene el conteo total de vales según filtros
     */
    public function getTotalCount($filters = []) {
        $sql = "SELECT COUNT(*) as total 
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
        
        if (!empty($filters['client_id'])) {
            $sql .= " AND v.client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
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
    
    /**
     * Obtiene los datos de un cliente por su ID
     */
    public function getClientById($clientId) {
        $sql = "SELECT id, business_name, rfc_curp, address, phone, client_type 
                FROM clients 
                WHERE id = ?";
        return $this->db->fetchOne($sql, [$clientId]);
    }
    
    /**
     * Obtiene resumen de vales agrupados por empresa/cliente
     */
    public function getVouchersByCompany($dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    c.id as client_id,
                    c.business_name as client_name,
                    COUNT(v.id) as total_vouchers,
                    SUM(v.capacity) as total_capacity,
                    SUM(CASE WHEN v.status = 'active' THEN 1 ELSE 0 END) as active_count,
                    SUM(CASE WHEN v.status = 'used' OR v.status = 'registered' THEN 1 ELSE 0 END) as used_count,
                    SUM(CASE WHEN v.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                    SUM(CASE WHEN v.payment_status = 'paid' THEN v.cost ELSE 0 END) as total_paid,
                    SUM(CASE WHEN v.payment_status = 'pending' THEN v.cost ELSE 0 END) as total_pending,
                    MIN(v.folio) as folio_inicial,
                    MAX(v.folio) as folio_final,
                    v.serie
                FROM vouchers v
                LEFT JOIN clients c ON v.client_id = c.id
                WHERE v.cost IS NOT NULL";
        
        $params = [];
        
        if ($dateFrom) {
            $sql .= " AND v.created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $sql .= " AND v.created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $sql .= " GROUP BY c.id, c.business_name, v.serie
                  ORDER BY MIN(v.created_at) DESC, v.serie ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtiene el detalle de vales por empresa
     */
    public function getVoucherDetailsByCompany($clientId = null, $dateFrom = null, $dateTo = null, $filters = []) {
        $sql = "SELECT 
                    v.id,
                    v.serie,
                    v.folio,
                    v.qr_code,
                    v.capacity,
                    v.cost,
                    v.payment_status,
                    v.status,
                    v.created_at,
                    v.used_at,
                    c.business_name as client_name,
                    c.id as client_id
                FROM vouchers v
                LEFT JOIN clients c ON v.client_id = c.id
                WHERE 1=1";
        
        $params = [];
        
        if ($clientId) {
            $sql .= " AND v.client_id = ?";
            $params[] = $clientId;
        }
        
        if ($dateFrom) {
            $sql .= " AND v.created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $sql .= " AND v.created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }
        
        // Add additional filters
        if (!empty($filters['search'])) {
            $sql .= " AND (v.qr_code LIKE ? OR v.serie LIKE ? OR v.folio LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['serie'])) {
            $sql .= " AND v.serie = ?";
            $params[] = $filters['serie'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND v.status = ?";
            $params[] = $filters['status'];
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
     * Obtiene el conteo total de vales por empresa
     */
    public function getTotalCountByCompany($clientId = null, $dateFrom = null, $dateTo = null, $filters = []) {
        $sql = "SELECT COUNT(*) as total
                FROM vouchers v
                LEFT JOIN clients c ON v.client_id = c.id
                WHERE 1=1";
        
        $params = [];
        
        if ($clientId) {
            $sql .= " AND v.client_id = ?";
            $params[] = $clientId;
        }
        
        if ($dateFrom) {
            $sql .= " AND v.created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $sql .= " AND v.created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }
        
        // Add additional filters
        if (!empty($filters['search'])) {
            $sql .= " AND (v.qr_code LIKE ? OR v.serie LIKE ? OR v.folio LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['serie'])) {
            $sql .= " AND v.serie = ?";
            $params[] = $filters['serie'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND v.status = ?";
            $params[] = $filters['status'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
}
