-- =====================================================================
-- Actualización v2.0.0 - Módulo PARQUE ACUÁTICO
-- MySQL 5.7 compatible
-- =====================================================================

-- 1. Agregar rol 'cajero_parque' a la tabla de usuarios
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'supervisor', 'operator', 'viewer', 'client', 'cajero_parque')
    NOT NULL DEFAULT 'operator';

-- 2. Tabla de códigos de acceso por serie (pulseras QR)
CREATE TABLE IF NOT EXISTS aquapark_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    series_number INT NOT NULL COMMENT 'Número secuencial de la pulsera',
    code VARCHAR(100) NOT NULL COMMENT 'Código único de validación',
    valid_date DATE NOT NULL COMMENT 'Fecha en la que el código es válido',
    validated_at DATETIME NULL COMMENT 'Fecha y hora de validación',
    validated_by VARCHAR(150) NULL COMMENT 'Información de quién validó',
    created_by INT NULL COMMENT 'ID del usuario que generó el lote',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_code (code),
    UNIQUE KEY uq_series_date (series_number, valid_date),
    INDEX idx_valid_date (valid_date),
    INDEX idx_series_number (series_number),
    INDEX idx_validated_at (validated_at),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Códigos QR generados por serie para pulseras de parque acuático';

-- 3. Tabla de boletos de visitantes registrados manualmente
CREATE TABLE IF NOT EXISTS aquapark_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_name VARCHAR(150) NULL COMMENT 'Nombre del visitante (opcional)',
    phone VARCHAR(20) NULL COMMENT 'Teléfono del visitante (opcional)',
    visit_date DATE NOT NULL COMMENT 'Fecha de visita',
    ticket_count INT NOT NULL DEFAULT 1 COMMENT 'Número de boletos',
    total_amount DECIMAL(10, 2) NULL COMMENT 'Monto total cobrado',
    code VARCHAR(100) NOT NULL COMMENT 'Código único del boleto',
    notes TEXT NULL COMMENT 'Notas adicionales',
    created_by INT NULL COMMENT 'ID del usuario que registró',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ticket_code (code),
    INDEX idx_visit_date (visit_date),
    INDEX idx_created_by (created_by),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Boletos de visitantes registrados manualmente en el parque acuático';

-- 4. Configuraciones del parque acuático en la tabla settings
INSERT INTO settings (setting_key, setting_value)
VALUES
    ('aquapark_ticket_price_series', '0.00'),
    ('aquapark_ticket_price_manual', '0.00')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
