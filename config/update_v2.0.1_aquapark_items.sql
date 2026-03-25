-- =====================================================================
-- Actualización v2.0.1 - Módulo PARQUE ACUÁTICO - Boletos Individuales
-- MySQL 5.7 compatible
-- =====================================================================
-- Este script agrega soporte para:
--   1. Boletos individuales (un código QR por persona dentro de un registro)
--   2. Validación de pulseras "válidas hasta" una fecha (no sólo en ese día)
-- =====================================================================

-- 1. Tabla de items individuales de boletos
--    Cada registro en aquapark_tickets puede tener N items (uno por persona).
--    Cada item tiene su propio código QR único para ser escaneado en la entrada.
CREATE TABLE IF NOT EXISTS aquapark_ticket_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL COMMENT 'Referencia al registro padre en aquapark_tickets',
    item_number INT NOT NULL COMMENT 'Número secuencial del boleto dentro del registro (1, 2, 3...)',
    code VARCHAR(120) NOT NULL COMMENT 'Código único del boleto individual (TKT-YYYYMMDD-XXXXXXXX)',
    validated_at DATETIME NULL COMMENT 'Fecha y hora en que se validó el boleto en la entrada',
    validated_by VARCHAR(150) NULL COMMENT 'Quién o qué dispositivo validó el boleto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_item_code (code),
    UNIQUE KEY uq_ticket_item (ticket_id, item_number),
    INDEX idx_ticket_id (ticket_id),
    FOREIGN KEY (ticket_id) REFERENCES aquapark_tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Boletos individuales (uno por persona) generados a partir de un registro de visitante';
