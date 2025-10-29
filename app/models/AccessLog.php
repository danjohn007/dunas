<?php
/**
 * Modelo AccessLog (Control de Acceso)
 */
class AccessLog {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
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
        
        $sql .= " ORDER BY al.entry_datetime DESC";
        
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
        
        $sql = "INSERT INTO access_logs (entry_datetime, driver_id, unit_id, client_id, ticket_code, status) 
                VALUES (NOW(), ?, ?, ?, ?, 'in_progress')";
        
        $params = [
            $data['driver_id'],
            $data['unit_id'],
            $data['client_id'],
            $ticketCode
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
        // Generar código de 4 dígitos único
        $attempts = 0;
        $maxAttempts = 100;
        
        do {
            // rand(1000, 9999) ya genera 4 dígitos, no necesita str_pad
            $code = (string)rand(1000, 9999);
            // Verificar si el código ya existe hoy
            $sql = "SELECT COUNT(*) as count FROM access_logs 
                    WHERE ticket_code = ? AND DATE(entry_datetime) = CURDATE()";
            $result = $this->db->fetchOne($sql, [$code]);
            
            if ($result['count'] == 0) {
                return $code;
            }
            
            $attempts++;
        } while ($attempts < $maxAttempts);
        
        // Si no se encontró código único en 100 intentos, usar fallback que garantiza 4 dígitos
        return substr(date('His'), -2) . str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
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
}
