<?php
/**
 * Modelo AquaparkTicket - Boletos de visitantes registrados manualmente
 */
class AquaparkTicket {

    /** Identificador de validación por escaneo público (usado en validated_by) */
    const VALIDATOR_PUBLIC = 'Escaneo público';

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Genera un código único para un boleto.
     */
    private function generateCode($date) {
        $dateCompact = str_replace('-', '', $date);
        return 'TKT-' . $dateCompact . '-' . strtoupper(substr(uniqid('', true), -8));
    }

    /**
     * Crea un nuevo boleto de visitante y genera los items individuales.
     */
    public function create($data) {
        $visitDate = $data['visit_date'] ?: date('Y-m-d');
        $code = $this->generateCode($visitDate);

        $sql = "INSERT INTO aquapark_tickets
                    (visitor_name, phone, visit_date, ticket_count, total_amount, code, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['visitor_name'] ?: null,
            $data['phone'] ?: null,
            $visitDate,
            (int)$data['ticket_count'],
            $data['total_amount'] ?: null,
            $code,
            $data['notes'] ?: null,
            $data['created_by'] ?: null
        ];

        $this->db->execute($sql, $params);
        $id = $this->db->lastInsertId();

        // Generate one individual item per ticket
        $this->createItems($id, (int)$data['ticket_count'], $visitDate);

        return $this->getById($id);
    }

    /**
     * Crea los items individuales para un registro de visitante.
     * @param int    $ticketId  ID del registro padre
     * @param int    $count     Número de boletos individuales
     * @param string $visitDate Fecha de visita (YYYY-MM-DD)
     */
    public function createItems($ticketId, $count, $visitDate) {
        for ($i = 1; $i <= $count; $i++) {
            $itemCode = $this->generateCode($visitDate);
            $sql = "INSERT INTO aquapark_ticket_items (ticket_id, item_number, code)
                    VALUES (?, ?, ?)";
            $this->db->execute($sql, [$ticketId, $i, $itemCode]);
        }
    }

    /**
     * Obtiene todos los items individuales de un registro de visitante.
     * @param int $ticketId
     * @return array
     */
    public function getItemsByTicketId($ticketId) {
        $sql = "SELECT * FROM aquapark_ticket_items
                WHERE ticket_id = ?
                ORDER BY item_number ASC";
        return $this->db->fetchAll($sql, [$ticketId]);
    }

    /**
     * Busca un item individual por su código QR.
     * @param string $code
     * @return array|null  Item + datos del ticket padre, o null si no existe
     */
    public function getItemByCode($code) {
        $sql = "SELECT i.*, t.visitor_name, t.phone, t.visit_date, t.ticket_count,
                       t.total_amount, t.notes, t.code AS parent_code,
                       u.full_name AS created_by_name
                FROM aquapark_ticket_items i
                JOIN aquapark_tickets t ON i.ticket_id = t.id
                LEFT JOIN users u ON t.created_by = u.id
                WHERE i.code = ?";
        return $this->db->fetchOne($sql, [$code]);
    }

    /**
     * Marca un item individual como validado (usado en la entrada).
     * @param int $itemId
     */
    public function markItemValidated($itemId) {
        $sql = "UPDATE aquapark_ticket_items
                SET validated_at = NOW(), validated_by = ?
                WHERE id = ?";
        $this->db->execute($sql, [self::VALIDATOR_PUBLIC, $itemId]);
    }

    /**
     * Obtiene un boleto por ID.
     */
    public function getById($id) {
        $sql = "SELECT t.*, u.full_name AS created_by_name
                FROM aquapark_tickets t
                LEFT JOIN users u ON t.created_by = u.id
                WHERE t.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Obtiene un boleto por código.
     */
    public function getByCode($code) {
        $sql = "SELECT t.*, u.full_name AS created_by_name
                FROM aquapark_tickets t
                LEFT JOIN users u ON t.created_by = u.id
                WHERE t.code = ?";
        return $this->db->fetchOne($sql, [$code]);
    }

    /**
     * Obtiene todos los boletos con filtros opcionales.
     */
    public function getAll($filters = []) {
        $sql = "SELECT t.*, u.full_name AS created_by_name
                FROM aquapark_tickets t
                LEFT JOIN users u ON t.created_by = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND t.visit_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.visit_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (t.visitor_name LIKE ? OR t.phone LIKE ? OR t.code LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }

        $sql .= " ORDER BY t.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Cuenta boletos con filtros.
     */
    public function countAll($filters = []) {
        $sql = "SELECT COUNT(*) AS total FROM aquapark_tickets WHERE 1=1";
        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND visit_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND visit_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (visitor_name LIKE ? OR phone LIKE ? OR code LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }

        $result = $this->db->fetchOne($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Obtiene estadísticas por fecha.
     */
    public function getStatsByDate($dateFrom, $dateTo) {
        $sql = "SELECT visit_date,
                       COUNT(*) AS total_records,
                       SUM(ticket_count) AS total_tickets,
                       SUM(total_amount) AS total_amount
                FROM aquapark_tickets
                WHERE visit_date BETWEEN ? AND ?
                GROUP BY visit_date
                ORDER BY visit_date DESC";
        return $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
    }
}
