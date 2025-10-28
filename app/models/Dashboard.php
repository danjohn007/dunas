<?php
/**
 * Modelo Dashboard
 */
class Dashboard {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getStats() {
        $stats = [];
        
        // Total de clientes activos
        $sql = "SELECT COUNT(*) as count FROM clients WHERE status = 'active'";
        $result = $this->db->fetchOne($sql);
        $stats['active_clients'] = $result['count'];
        
        // Total de unidades activas
        $sql = "SELECT COUNT(*) as count FROM units WHERE status = 'active'";
        $result = $this->db->fetchOne($sql);
        $stats['active_units'] = $result['count'];
        
        // Total de choferes activos
        $sql = "SELECT COUNT(*) as count FROM drivers WHERE status = 'active'";
        $result = $this->db->fetchOne($sql);
        $stats['active_drivers'] = $result['count'];
        
        // Accesos en progreso
        $sql = "SELECT COUNT(*) as count FROM access_logs WHERE status = 'in_progress'";
        $result = $this->db->fetchOne($sql);
        $stats['in_progress_access'] = $result['count'];
        
        // Total de accesos hoy
        $sql = "SELECT COUNT(*) as count FROM access_logs WHERE DATE(entry_datetime) = CURDATE()";
        $result = $this->db->fetchOne($sql);
        $stats['today_access'] = $result['count'];
        
        // Total de litros suministrados hoy
        $sql = "SELECT COALESCE(SUM(liters_supplied), 0) as total FROM access_logs 
                WHERE DATE(entry_datetime) = CURDATE() AND status = 'completed'";
        $result = $this->db->fetchOne($sql);
        $stats['today_liters'] = $result['total'];
        
        return $stats;
    }
    
    public function getRecentAccess($limit = 5) {
        $sql = "SELECT al.*, d.full_name as driver_name, u.plate_number, c.business_name as client_name
                FROM access_logs al
                JOIN drivers d ON al.driver_id = d.id
                JOIN units u ON al.unit_id = u.id
                JOIN clients c ON al.client_id = c.id
                ORDER BY al.entry_datetime DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    public function getTodayRevenue() {
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as revenue 
                FROM transactions 
                WHERE DATE(transaction_date) = CURDATE() AND payment_status = 'paid'";
        
        $result = $this->db->fetchOne($sql);
        return $result['revenue'];
    }
    
    public function getMonthlyData() {
        $sql = "SELECT 
                    DATE_FORMAT(transaction_date, '%Y-%m') as month,
                    SUM(total_amount) as revenue,
                    SUM(liters_supplied) as liters
                FROM transactions
                WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                AND payment_status = 'paid'
                GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
                ORDER BY month";
        
        return $this->db->fetchAll($sql);
    }
}
