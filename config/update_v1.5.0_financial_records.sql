-- Sistema de Control de Acceso con IoT
-- Script de Actualización de Base de Datos
-- Fecha: 2025-11-30
-- Versión: 1.5.0 - Reparación de registros financieros y QR

USE dunas_access_control;

-- ============================================================
-- MODIFICACIONES A LA TABLA access_logs
-- ============================================================

-- Agregar campo de costo para registrar el monto de cada entrada
ALTER TABLE access_logs
ADD COLUMN cost DECIMAL(10, 2) NULL DEFAULT NULL AFTER plate_discrepancy,
ADD COLUMN payment_method ENUM('cash', 'voucher', 'bank_transfer') NOT NULL DEFAULT 'cash' AFTER cost;

-- Agregar índice para búsquedas por método de pago
ALTER TABLE access_logs
ADD INDEX idx_payment_method (payment_method);

-- ============================================================
-- TABLA DE COSTOS POR CAPACIDAD (si no existe)
-- ============================================================

-- Crear tabla capacity_costs si no existe
CREATE TABLE IF NOT EXISTS capacity_costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    capacity_liters INT NOT NULL,
    cost DECIMAL(10, 2) NOT NULL,
    description VARCHAR(100) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_capacity (capacity_liters)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar costos de capacidad por defecto (si la tabla está vacía)
INSERT IGNORE INTO capacity_costs (capacity_liters, cost, description, is_active) VALUES
(10000, 400.00, 'Pipa 10,000 litros', 1),
(12000, 480.00, 'Pipa 12,000 litros', 1),
(15000, 600.00, 'Pipa 15,000 litros', 1);

-- ============================================================
-- MENSAJES FINALES
-- ============================================================

SELECT 'Actualización de base de datos completada exitosamente.' as mensaje;
SELECT 'Versión: 1.5.0 - Reparación de registros financieros y QR' as version;
SELECT 'Cambios aplicados:' as titulo;
SELECT '1. Agregado campo cost en access_logs' as cambio;
SELECT '2. Agregado campo payment_method en access_logs' as cambio;
SELECT '3. Cada emisión de ticket ahora genera una transacción para el reporte financiero' as cambio;
SELECT '4. Código QR reparado en impresión de tickets' as cambio;
SELECT '5. Estilos de botones ahora usan colores del tema personalizado' as cambio;
SELECT NOW() as fecha_actualizacion;
