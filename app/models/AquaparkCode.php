<?php
/**
 * Modelo AquaparkCode - Códigos QR de pulseras por serie
 */
class AquaparkCode {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Genera un lote de códigos por serie para una fecha dada.
     * @param int $start      Número inicial de serie
     * @param int $end        Número final de serie
     * @param string $date    Fecha de validez (YYYY-MM-DD)
     * @param int $createdBy  ID del usuario que genera
     * @param string $ticketType Tipo de boleto (normal|nino|adulto_mayor|capacidades_diferentes)
     * @return int Cantidad de códigos generados
     */
    public function generateBatch($start, $end, $date, $createdBy, $ticketType = 'normal') {
        $allowedTypes = ['normal', 'nino', 'adulto_mayor', 'capacidades_diferentes'];
        if (!in_array($ticketType, $allowedTypes)) {
            $ticketType = 'normal';
        }
        $count = 0;
        for ($n = $start; $n <= $end; $n++) {
            $code = $this->buildCode($n, $date);
            $sql = "INSERT IGNORE INTO aquapark_codes (series_number, code, valid_date, ticket_type, created_by)
                    VALUES (?, ?, ?, ?, ?)";
            $this->db->execute($sql, [$n, $code, $date, $ticketType, $createdBy]);
            $count++;
        }
        return $count;
    }

    /**
     * Construye el código único para un número de serie y fecha.
     */
    public function buildCode($seriesNumber, $date) {
        $dateCompact = str_replace('-', '', $date);
        return 'AQP-' . $dateCompact . '-' . str_pad($seriesNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtiene todos los códigos con filtros opcionales.
     */
    public function getAll($filters = []) {
        $sql = "SELECT c.*, u.full_name AS created_by_name
                FROM aquapark_codes c
                LEFT JOIN users u ON c.created_by = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND c.valid_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND c.valid_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (isset($filters['validated']) && $filters['validated'] !== '') {
            if ($filters['validated'] === '1') {
                $sql .= " AND c.validated_at IS NOT NULL";
            } else {
                $sql .= " AND c.validated_at IS NULL";
            }
        }

        $sql .= " ORDER BY c.valid_date DESC, c.series_number ASC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Cuenta códigos con filtros.
     */
    public function countAll($filters = []) {
        $sql = "SELECT COUNT(*) AS total FROM aquapark_codes WHERE 1=1";
        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND valid_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND valid_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (isset($filters['validated']) && $filters['validated'] !== '') {
            if ($filters['validated'] === '1') {
                $sql .= " AND validated_at IS NOT NULL";
            } else {
                $sql .= " AND validated_at IS NULL";
            }
        }

        $result = $this->db->fetchOne($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Obtiene códigos de un rango de serie para una fecha (para impresión).
     */
    public function getBySeriesAndDate($start, $end, $date) {
        $sql = "SELECT * FROM aquapark_codes
                WHERE series_number BETWEEN ? AND ?
                  AND valid_date = ?
                ORDER BY series_number ASC";
        return $this->db->fetchAll($sql, [$start, $end, $date]);
    }

    /**
     * Valida un código: devuelve resultado de validación.
     */
    public function validate($code, $validatedBy = null) {
        $sql = "SELECT * FROM aquapark_codes WHERE code = ?";
        $record = $this->db->fetchOne($sql, [$code]);

        if (!$record) {
            return ['valid' => false, 'status' => 'not_found', 'message' => 'Código no encontrado'];
        }

        $today = date('Y-m-d');
        if ($record['valid_date'] < $today) {
            return [
                'valid' => false,
                'status' => 'expired',
                'message' => 'Este código venció el ' . date('d/m/Y', strtotime($record['valid_date'])),
                'record' => $record
            ];
        }

        if (!empty($record['validated_at'])) {
            return [
                'valid' => false,
                'status' => 'already_used',
                'message' => 'Este código ya fue utilizado a las ' . date('H:i', strtotime($record['validated_at'])),
                'record' => $record
            ];
        }

        // Marcar como validado
        $sql = "UPDATE aquapark_codes SET validated_at = NOW(), validated_by = ? WHERE id = ?";
        $this->db->execute($sql, [$validatedBy, $record['id']]);

        return [
            'valid' => true,
            'status' => 'ok',
            'message' => '¡Acceso autorizado! Pulsera #' . $record['series_number'],
            'record' => $record
        ];
    }

    /**
     * Obtiene estadísticas de pulseras VALIDADAS agrupadas por fecha de validación.
     * Usa DATE(validated_at) para mostrar correctamente los accesos del día,
     * independientemente de cuándo vence el código.
     */
    public function getStatsByDate($dateFrom, $dateTo) {
        $sql = "SELECT DATE(validated_at) AS validated_date,
                       COUNT(*) AS validated_count,
                       ticket_type
                FROM aquapark_codes
                WHERE validated_at IS NOT NULL
                  AND DATE(validated_at) BETWEEN ? AND ?
                GROUP BY DATE(validated_at), ticket_type
                ORDER BY validated_date DESC, ticket_type ASC";
        return $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
    }
}
