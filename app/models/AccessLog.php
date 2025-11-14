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
        $ticketCode = $this->generateTicketCode();
        
        $sql = "INSERT INTO access_logs (entry_datetime, driver_id, unit_id, client_id, ticket_code, license_plate_reading, plate_discrepancy, status) 
                VALUES (NOW(), ?, ?, ?, ?, ?, ?, 'in_progress')";
        
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
            $plateDiscrepancy
        ];
        
        $this->db->execute($sql, $params);
        $id = $this->db->lastInsertId();
        
        // Generar QR y código de barras
        $this->generateCodes($id, $ticketCode);
        
        return $id;
    }
    
    public function registerExit($id, $literSupplied) {
        $sql = "UPDATE access_logs SET exit_datetime = NOW(), liters_supplied = ?, status = 'completed' 
                WHERE id = ?";
        
        return $this->db->execute($sql, [$literSupplied, $id]);
    }
    
    public function cancel($id) {
        $sql = "UPDATE access_logs SET status = 'cancelled' WHERE id = ?";
        return $this->db->execute($sql, [$id]);
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
}
