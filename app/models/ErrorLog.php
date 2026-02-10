<?php
/**
 * Modelo ErrorLog - Gestión de registro de errores del sistema
 */
class ErrorLog {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Registra un error en la base de datos
     */
    public function log($level, $message, $context = [], $userId = null) {
        $sql = "INSERT INTO error_logs (level, message, context, user_id, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $level,
            $message,
            json_encode($context),
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Obtiene todos los errores con filtros opcionales y paginación
     */
    public function getAll($filters = []) {
        $sql = "SELECT e.*, u.username as user_username, u.full_name as user_name
                FROM error_logs e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['level'])) {
            $sql .= " AND e.level = ?";
            $params[] = $filters['level'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(e.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(e.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (e.message LIKE ? OR e.context LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        
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
     * Obtiene el conteo total de errores según filtros
     */
    public function getTotalCount($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM error_logs e WHERE 1=1";
        $params = [];
        
        if (!empty($filters['level'])) {
            $sql .= " AND e.level = ?";
            $params[] = $filters['level'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(e.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(e.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (e.message LIKE ? OR e.context LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene un error por ID
     */
    public function getById($id) {
        $sql = "SELECT e.*, u.username as user_username, u.full_name as user_name
                FROM error_logs e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Obtiene estadísticas de errores
     */
    public function getStats($dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN level = 'critical' THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as error,
                    SUM(CASE WHEN level = 'warning' THEN 1 ELSE 0 END) as warning,
                    SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as info
                FROM error_logs
                WHERE 1=1";
        
        $params = [];
        
        if ($dateFrom) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }
        
        return $this->db->fetchOne($sql, $params);
    }
    
    /**
     * Elimina errores antiguos (más de X días)
     */
    public function deleteOlderThan($days) {
        $sql = "DELETE FROM error_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->execute($sql, [$days]);
    }
    
    /**
     * Elimina todos los errores
     */
    public function deleteAll() {
        $sql = "DELETE FROM error_logs";
        return $this->db->execute($sql);
    }
}
