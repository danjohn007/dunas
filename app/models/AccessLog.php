<?php
/**
 * Modelo AccessLog (Control de Acceso)
 */
class AccessLog {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function countAll($filters = []) {
        $sql = "SELECT COUNT(*) as total
                FROM access_logs al
                JOIN drivers d ON al.driver_id = d.id
                JOIN units u ON al.unit_id = u.id
                JOIN clients c ON al.client_id = c.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND al.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['unit_id'])) {
            $sql .= " AND al.unit_id = ?";
            $params[] = $filters['unit_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.entry_datetime) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.entry_datetime) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (al.ticket_code LIKE ? OR u.plate_number LIKE ? OR c.business_name LIKE ? OR d.full_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['total'];
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT al.*, d.full_name as driver_name, u.plate_number, c.business_name as client_name
                FROM access_logs al
                JOIN drivers d ON al.driver_id = d.id
                JOIN units u ON al.unit_id = u.id
                JOIN clients c ON al.client_id = c.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND al.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['unit_id'])) {
            $sql .= " AND al.unit_id = ?";
            $params[] = $filters['unit_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.entry_datetime) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.entry_datetime) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (al.ticket_code LIKE ? OR u.plate_number LIKE ? OR c.business_name LIKE ? OR d.full_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY al.entry_datetime DESC";
        
        // Paginación
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$filters['limit'];
            $params[] = (int)$filters['offset'];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT al.*, d.full_name as driver_name, d.phone as driver_phone,
                u.plate_number, u.capacity_liters,
                c.business_name as client_name, c.phone as client_phone
                FROM access_logs al
                JOIN drivers d ON al.driver_id = d.id
                JOIN units u ON al.unit_id = u.id
                JOIN clients c ON al.client_id = c.id
                WHERE al.id = ?";
        
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getByTicket($ticketCode) {
        $sql = "SELECT al.*, d.full_name as driver_name, u.plate_number, c.business_name as client_name
                FROM access_logs al
                JOIN drivers d ON al.driver_id = d.id
                JOIN units u ON al.unit_id = u.id
                JOIN clients c ON al.client_id = c.id
                WHERE al.ticket_code = ?";
        
        return $this->db->fetchOne($sql, [$ticketCode]);
    }
    
    public function create($data) {
        if (!empty($data['ticket_code'])) {
            $ticketCode = trim((string)$data['ticket_code']);
            if (!preg_match('/^\d{4}$/', $ticketCode)) {
                throw new Exception("El PIN debe contener exactamente 4 dígitos");
            }
            if ($this->ticketCodeExists($ticketCode)) {
                throw new Exception("El PIN {$ticketCode} ya fue utilizado, valide otro vale.");
            }
        } else {
            $ticketCode = $this->generateTicketCode();
        }
        
        // Get cost from capacity if available
        $cost = null;
        $capacityLiters = 0;
        if (!empty($data['unit_id'])) {
            // Get unit capacity and lookup cost
            require_once APP_PATH . '/models/Unit.php';
            require_once APP_PATH . '/models/CapacityCost.php';
            $unitModel = new Unit();
            $capacityCostModel = new CapacityCost();
            
            $unit = $unitModel->getById($data['unit_id']);
            if ($unit && !empty($unit['capacity_liters'])) {
                $capacityLiters = $unit['capacity_liters'];
                $capacityCost = $capacityCostModel->getByCapacity($unit['capacity_liters']);
                if ($capacityCost) {
                    $cost = $capacityCost['cost'];
                }
            }
        }
        
        // Use provided cost if available
        if (isset($data['cost'])) {
            $cost = $data['cost'];
        }
        
        $paymentMethod = $data['payment_method'] ?? 'cash';
        
        $sql = "INSERT INTO access_logs (entry_datetime, driver_id, unit_id, client_id, ticket_code, license_plate_reading, plate_discrepancy, cost, payment_method, status) 
                VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, 'in_progress')";
        
        // Convertir plate_discrepancy a 1 o 0 explícitamente
        $plateDiscrepancy = isset($data['plate_discrepancy']) ? (int)$data['plate_discrepancy'] : 0;
        
        // Log para debug en el modelo
        error_log("=== MODEL DEBUG ===");
        error_log("plate_discrepancy recibido: " . var_export($data['plate_discrepancy'] ?? 'NO SET', true));
        error_log("plateDiscrepancy a guardar: " . var_export($plateDiscrepancy, true));
        
        $params = [
            $data['driver_id'],
            $data['unit_id'],
            $data['client_id'],
            $ticketCode,
            $data['license_plate_reading'] ?? null,
            $plateDiscrepancy,
            $cost,
            $paymentMethod
        ];
        
        $this->db->execute($sql, $params);
        $id = $this->db->lastInsertId();
        
        // Crear usuario en el dispositivo Hikvision vía PC puente
        $this->createHikvisionUser($id, $ticketCode, $data);
        
        // Generar QR y código de barras
        $this->generateCodes($id, $ticketCode);
        
        // Crear registro de transacción para el reporte financiero
        if ($cost !== null && $cost > 0) {
            $this->createTransaction($id, $data['client_id'], $cost, $paymentMethod, $capacityLiters);
        }
        
        return $id;
    }

    private function ticketCodeExists($ticketCode) {
        $sql = "SELECT COUNT(*) as count FROM access_logs WHERE ticket_code = ?";
        $result = $this->db->fetchOne($sql, [$ticketCode]);
        return isset($result['count']) && (int)$result['count'] > 0;
    }
    
    /**
     * Crea un usuario en el dispositivo Hikvision con el PIN del ticket
     * 
     * @param int $ticketId ID del ticket/access log
     * @param string $pin PIN de 4 dígitos
     * @param array $data Datos del ticket (para obtener nombres)
     */
    private function createHikvisionUser($ticketId, $pin, $data) {
        // Verificar si la integración está habilitada
        if (!defined('HIKVISION_ENABLED') || !HIKVISION_ENABLED) {
            error_log("ℹ️ Integración con Hikvision deshabilitada");
            return;
        }
        
        // Generar datos del usuario
        $deviceUserId = "TKT-" . str_pad($ticketId, 6, '0', STR_PAD_LEFT);
        $clientName = $this->getClientName($data['client_id']);
        $userName = $clientName ? substr($clientName, 0, 30) : "Ticket-{$ticketId}";
        $hoursValid = defined('HIKVISION_USER_VALIDITY_HOURS') ? HIKVISION_USER_VALIDITY_HOURS : 1;
        
        // MODO LOCAL: Guardar en sesión para que JavaScript lo envíe
        if (defined('HIKVISION_BRIDGE_LOCAL_MODE') && HIKVISION_BRIDGE_LOCAL_MODE) {
            if (!isset($_SESSION['hikvision_pending_users'])) {
                $_SESSION['hikvision_pending_users'] = [];
            }
            
            $_SESSION['hikvision_pending_users'][] = [
                'device_user_id' => $deviceUserId,
                'name' => $userName,
                'pin' => $pin,
                'card_number' => $pin,
                'hours_valid' => $hoursValid,
                'ticket_id' => $ticketId
            ];
            
            error_log("🏠 Modo Local: Usuario guardado en sesión para envío client-side: {$deviceUserId} PIN: {$pin}");
            return;
        }
        
        // MODO REMOTO: Enviar por PHP (actual)
        try {
            require_once __DIR__ . '/../services/HikvisionBridgeService.php';
            $bridge = new HikvisionBridgeService();
            
            // Crear usuario en el dispositivo
            $result = $bridge->createTicketUser(
                $deviceUserId,
                $userName,
                $pin,
                $hoursValid,
                true // Modo asíncrono para evitar bloqueos
            );
            
            if ($result['success']) {
                error_log("✅ Usuario enviado a dispositivo Hikvision: {$deviceUserId} con PIN: {$pin} (válido {$hoursValid}h)");
            } else {
                error_log("⚠️ No se pudo enviar usuario a dispositivo: " . $result['message']);
            }
            
        } catch (Exception $e) {
            error_log("❌ Error al crear usuario en Hikvision: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene el nombre del cliente por ID
     */
    private function getClientName($clientId) {
        $sql = "SELECT business_name FROM clients WHERE id = ?";
        $result = $this->db->fetchOne($sql, [$clientId]);
        return $result ? $result['business_name'] : null;
    }
    
    public function registerExit($id, $literSupplied) {
        // Marcar el PIN como usado cuando se registra la salida (acceso completado)
        $sql = "UPDATE access_logs SET exit_datetime = NOW(), liters_supplied = ?, status = 'completed', pin_used = 1 
                WHERE id = ?";
        
        return $this->db->execute($sql, [$literSupplied, $id]);
    }
    
    public function cancel($id) {
        $sql = "UPDATE access_logs SET status = 'cancelled' WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Verifica si un PIN/ticket code ya ha sido usado
     * @param string $ticketCode Código del ticket (PIN de 4 dígitos)
     * @return bool True si el PIN ya fue usado, False si está disponible
     */
    public function isPinUsed($ticketCode) {
        $sql = "SELECT pin_used FROM access_logs WHERE ticket_code = ? LIMIT 1";
        $result = $this->db->fetchOne($sql, [$ticketCode]);
        
        if (!$result) {
            return false; // No existe el ticket
        }
        
        return (bool)$result['pin_used'];
    }
    
    /**
     * Obtiene información sobre el uso de un PIN/ticket code
     * @param string $ticketCode Código del ticket (PIN de 4 dígitos)
     * @return array|null Información del acceso o null si no existe
     */
    public function getPinUsageInfo($ticketCode) {
        $sql = "SELECT al.*, c.business_name as client_name
                FROM access_logs al
                LEFT JOIN clients c ON al.client_id = c.id
                WHERE al.ticket_code = ?
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$ticketCode]);
    }
    
    private function generateTicketCode() {
        // Generar código de 4 dígitos único (solo números)
        $attempts = 0;
        $maxAttempts = 100;
        
        do {
            // Generar 4 dígitos aleatorios entre 1000 y 9999
            $code = (string)rand(1000, 9999);
            
            // Verificar si el código ya existe (sin restricción de fecha)
            $sql = "SELECT COUNT(*) as count FROM access_logs WHERE ticket_code = ?";
            $result = $this->db->fetchOne($sql, [$code]);
            
            if ($result['count'] == 0) {
                return $code;
            }
            
            $attempts++;
        } while ($attempts < $maxAttempts);
        
        // Si no se encontró código único en 100 intentos, usar timestamp
        return (string)rand(1000, 9999);
    }
    
    private function generateCodes($id, $ticketCode) {
        // Aquí se generarían los códigos QR y de barras
        // Por ahora solo guardamos referencias
        $qrCode = 'qr_' . $ticketCode . '.png';
        $barcode = 'bar_' . $ticketCode . '.png';
        
        $sql = "UPDATE access_logs SET qr_code = ?, barcode = ? WHERE id = ?";
        $this->db->execute($sql, [$qrCode, $barcode, $id]);
    }
    
    /**
     * Crea un registro de transacción para el reporte financiero
     * 
     * @param int $accessLogId ID del registro de acceso
     * @param int $clientId ID del cliente
     * @param float $amount Monto de la transacción
     * @param string $paymentMethod Método de pago (cash, voucher, bank_transfer)
     * @param int $capacityLiters Capacidad de la unidad (usado como litros estimados en el ticket)
     */
    private function createTransaction($accessLogId, $clientId, $amount, $paymentMethod, $capacityLiters) {
        try {
            // Calcular precio por litro si hay capacidad
            $pricePerLiter = $capacityLiters > 0 ? ($amount / $capacityLiters) : 0;
            
            $sql = "INSERT INTO transactions (access_log_id, client_id, total_amount, liters_supplied, 
                    price_per_liter, payment_method, payment_status, transaction_date, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, 'paid', NOW(), 'Transacción generada automáticamente al emitir ticket')";
            
            $params = [
                $accessLogId,
                $clientId,
                $amount,
                $capacityLiters,
                $pricePerLiter,
                $paymentMethod
            ];
            
            $this->db->execute($sql, $params);
            error_log("✅ Transacción creada exitosamente para access_log_id: {$accessLogId}, monto: {$amount}");
        } catch (Exception $e) {
            error_log("❌ Error al crear transacción: " . $e->getMessage());
            // No lanzamos excepción para que el ticket se cree de todos modos
        }
    }
    
    public function getInProgress() {
        $sql = "SELECT al.*, d.full_name as driver_name, u.plate_number, c.business_name as client_name
                FROM access_logs al
                JOIN drivers d ON al.driver_id = d.id
                JOIN units u ON al.unit_id = u.id
                JOIN clients c ON al.client_id = c.id
                WHERE al.status = 'in_progress'
                ORDER BY al.entry_datetime DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getLastEntryByPlate($plateNumber) {
        $sql = "SELECT al.*, d.id as driver_id, d.full_name as driver_name, 
                u.id as unit_id, u.plate_number, u.client_id, u.driver_id as unit_driver_id,
                c.id as client_id, c.business_name as client_name
                FROM access_logs al
                JOIN drivers d ON al.driver_id = d.id
                JOIN units u ON al.unit_id = u.id
                JOIN clients c ON al.client_id = c.id
                WHERE u.plate_number = ?
                ORDER BY al.entry_datetime DESC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$plateNumber]);
    }
    
    public function getPlateDiscrepancies($filters = []) {
        $sql = "SELECT al.*, 
                d.full_name as driver_name, d.phone as driver_phone,
                u.plate_number, u.capacity_liters,
                c.business_name as client_name, c.phone as client_phone
                FROM access_logs al
                JOIN drivers d ON al.driver_id = d.id
                JOIN units u ON al.unit_id = u.id
                JOIN clients c ON al.client_id = c.id
                WHERE al.plate_discrepancy = 1";
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.entry_datetime) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.entry_datetime) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND al.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY al.entry_datetime DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getPlateVerifications($filters = []) {
        $sql = "SELECT DISTINCT al.*, 
                d.full_name as driver_name, d.phone as driver_phone,
                u.plate_number, u.capacity_liters,
                c.business_name as client_name, c.phone as client_phone,
                dp.plate_text as detected_plate,
                dp.is_match as detection_match
                FROM access_logs al
                JOIN drivers d ON al.driver_id = d.id
                JOIN units u ON al.unit_id = u.id
                JOIN clients c ON al.client_id = c.id
                LEFT JOIN detected_plates dp ON u.plate_number = dp.plate_text
                WHERE al.plate_discrepancy = 0
                AND dp.plate_text IS NOT NULL";
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.entry_datetime) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.entry_datetime) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY al.entry_datetime DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Eliminar registros anteriores a una fecha
     */
    public function deleteBeforeDate($date) {
        // First delete related transactions
        $sqlTrans = "DELETE FROM transactions WHERE access_log_id IN 
                     (SELECT id FROM access_logs WHERE DATE(entry_datetime) < ?)";
        $this->db->execute($sqlTrans, [$date]);
        
        // Then delete access logs
        $sql = "DELETE FROM access_logs WHERE DATE(entry_datetime) < ?";
        $stmt = $this->db->execute($sql, [$date]);
        return $stmt->rowCount();
    }
}
