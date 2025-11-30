-- Script seguro para MySQL 5.7 (sin information_schema / PREPARE)
-- Usa: residenc_dunas (ajusta si tu esquema es otro)
-- IMPORTANTE: hacer backup antes de ejecutar

USE residenc_dunas;

-- 1) (Opcional) Añadir columna 'cost' si no existe:
-- Ejecuta este ALTER solo si SHOW COLUMNS FROM access_logs LIKE 'cost' devuelve 0 filas.
-- ALTER TABLE access_logs ADD COLUMN cost DECIMAL(10,2) DEFAULT NULL AFTER plate_discrepancy;

-- 2) (Opcional) Añadir columna 'payment_method' si no existe:
-- Ejecuta este ALTER solo si SHOW COLUMNS FROM access_logs LIKE 'payment_method' devuelve 0 filas.
-- ALTER TABLE access_logs ADD COLUMN payment_method ENUM('cash','voucher','bank_transfer') NOT NULL DEFAULT 'cash' AFTER cost;

-- 3) Crear índice para búsquedas por método de pago (ejecutar solo si no existe)
-- Antes de ejecutar, verifica con: SHOW INDEX FROM access_logs WHERE Key_name = 'idx_payment_method';
ALTER TABLE access_logs
  ADD INDEX idx_payment_method (payment_method);

-- 4) Crear tabla capacity_costs si no existe e insertar valores por defecto
CREATE TABLE IF NOT EXISTS capacity_costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    capacity_liters INT NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    description VARCHAR(100) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_capacity (capacity_liters)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO capacity_costs (capacity_liters, cost, description, is_active) VALUES
(10000, 400.00, 'Pipa 10,000 litros', 1),
(12000, 480.00, 'Pipa 12,000 litros', 1),
(15000, 600.00, 'Pipa 15,000 litros', 1);

-- Mensaje final (consulta para confirmar)
SELECT 'Script ejecutado (si hubo errores por índice/columna, revisa las comprobaciones anteriores).' AS mensaje;
SELECT NOW() AS fecha_ejecucion;
