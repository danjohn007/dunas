<?php
/**
 * Modelo Visitor - Gestión de visitantes
 */
require_once APP_PATH . '/helpers/Database.php';

class Visitor {
    
    private $db;
    private $table = 'visitors';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los visitantes con filtros
     */
    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (visitor_name LIKE ? OR plate_number LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(entry_datetime) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(entry_datetime) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY entry_datetime DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (isset($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar todos los visitantes con filtros
     */
    public function countAll($filters = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (visitor_name LIKE ? OR plate_number LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(entry_datetime) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(entry_datetime) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Obtener un visitante por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un visitante por código de pase
     */
    public function getByPassCode($passCode) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE pass_code = ?");
        $stmt->execute([$passCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generar código de pase único
     */
    private function generatePassCode() {
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        return "VIS-{$date}-{$random}";
    }
    
    /**
     * Crear un nuevo registro de visitante
     */
    public function create($data) {
        // Generar código de pase único
        $passCode = $this->generatePassCode();
        
        $sql = "INSERT INTO {$this->table} 
                (pass_code, visitor_name, plate_number, phone, id_photo, plate_photo, badge_photo, entry_datetime, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'in')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $passCode,
            $data['visitor_name'] ?? null,
            $data['plate_number'] ?? null,
            $data['phone'] ?? null,
            $data['id_photo'] ?? null,
            $data['plate_photo'] ?? null,
            $data['badge_photo'] ?? null,
            $data['notes'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar un visitante
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                visitor_name = ?,
                plate_number = ?,
                phone = ?,
                notes = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['visitor_name'] ?? null,
            $data['plate_number'] ?? null,
            $data['phone'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }
    
    /**
     * Registrar salida de visitante
     */
    public function registerExit($id) {
        $sql = "UPDATE {$this->table} SET exit_datetime = NOW(), status = 'out' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Cancelar registro de visitante
     */
    public function cancel($id) {
        $sql = "UPDATE {$this->table} SET status = 'cancelled' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Obtener visitantes activos (aún dentro)
     */
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'in' ORDER BY entry_datetime DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Eliminar visitantes anteriores a una fecha
     */
    public function deleteBeforeDate($date) {
        $sql = "DELETE FROM {$this->table} WHERE DATE(entry_datetime) < ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        return $stmt->rowCount();
    }
    
    /**
     * Obtener estadísticas de visitantes para un período
     */
    public function getStats($dateFrom, $dateTo) {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'out' THEN 1 ELSE 0 END) as exited,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM {$this->table}
                WHERE DATE(entry_datetime) BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
