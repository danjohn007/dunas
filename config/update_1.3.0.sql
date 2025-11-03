-- Sistema de Control de Acceso con IoT
-- Script de Actualización de Base de Datos
-- Fecha: 2024-11-03
-- Versión: 1.3.0

USE dunas_access_control;

-- ============================================================
-- MODIFICACIONES A LA TABLA units
-- ============================================================

-- Agregar relaciones obligatorias a cliente y chofer
ALTER TABLE units 
ADD COLUMN client_id INT NULL AFTER id,
ADD COLUMN driver_id INT NULL AFTER client_id,
ADD CONSTRAINT fk_units_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
ADD CONSTRAINT fk_units_driver FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE RESTRICT;

-- Agregar índices para mejorar el rendimiento
ALTER TABLE units
ADD INDEX idx_client_id (client_id),
ADD INDEX idx_driver_id (driver_id);

-- Hacer opcionales los campos year y serial_number
ALTER TABLE units
MODIFY COLUMN year INT NULL,
MODIFY COLUMN serial_number VARCHAR(100) NULL;

-- Eliminar restricción de único en serial_number para permitir valores NULL duplicados
ALTER TABLE units
DROP INDEX serial_number;

-- Nota: MySQL no soporta índices parciales (WHERE clause)
-- Para serial_number, permitimos múltiples NULL pero valores duplicados no-NULL generarán error en aplicación

-- ============================================================
-- MODIFICACIONES A LA TABLA drivers
-- ============================================================

-- Agregar relación obligatoria a cliente
ALTER TABLE drivers
ADD COLUMN client_id INT NULL AFTER id,
ADD CONSTRAINT fk_drivers_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT;

-- Agregar índice para mejorar el rendimiento
ALTER TABLE drivers
ADD INDEX idx_client_id (client_id);

-- Hacer opcionales los campos license_number y license_expiry
ALTER TABLE drivers
MODIFY COLUMN license_number VARCHAR(50) NULL,
MODIFY COLUMN license_expiry DATE NULL;

-- Eliminar restricción de único en license_number para permitir valores NULL duplicados
ALTER TABLE drivers
DROP INDEX license_number;

-- ============================================================
-- MODIFICACIONES A LA TABLA access_logs
-- ============================================================

-- Agregar campo para lectura de cámara de placas Hikvision
ALTER TABLE access_logs
ADD COLUMN license_plate_reading VARCHAR(20) NULL AFTER ticket_code,
ADD COLUMN plate_discrepancy BOOLEAN DEFAULT FALSE AFTER license_plate_reading;

-- Agregar índice para búsquedas por lectura de placa
ALTER TABLE access_logs
ADD INDEX idx_license_plate_reading (license_plate_reading);

-- ============================================================
-- MIGRACIÓN DE DATOS EXISTENTES
-- ============================================================

-- Para unidades existentes, asignar el primer cliente activo si no tienen uno
-- Esto es solo para datos de ejemplo, en producción ajustar según necesidad
UPDATE units u
LEFT JOIN clients c ON c.id = (SELECT id FROM clients WHERE status = 'active' LIMIT 1)
SET u.client_id = c.id
WHERE u.client_id IS NULL AND c.id IS NOT NULL;

-- Para unidades existentes, asignar el primer driver activo si no tienen uno
-- Esto es solo para datos de ejemplo, en producción ajustar según necesidad
UPDATE units u
LEFT JOIN drivers d ON d.id = (SELECT id FROM drivers WHERE status = 'active' LIMIT 1)
SET u.driver_id = d.id
WHERE u.driver_id IS NULL AND d.id IS NOT NULL;

-- Para drivers existentes, asignar el primer cliente activo si no tienen uno
-- Esto es solo para datos de ejemplo, en producción ajustar según necesidad
UPDATE drivers dr
LEFT JOIN clients c ON c.id = (SELECT id FROM clients WHERE status = 'active' LIMIT 1)
SET dr.client_id = c.id
WHERE dr.client_id IS NULL AND c.id IS NOT NULL;

-- ============================================================
-- VALIDACIÓN DE INTEGRIDAD
-- ============================================================

-- Verificar que no hay unidades sin cliente o driver
SELECT 'Unidades sin cliente:' as mensaje, COUNT(*) as cantidad
FROM units WHERE client_id IS NULL;

SELECT 'Unidades sin driver:' as mensaje, COUNT(*) as cantidad
FROM units WHERE driver_id IS NULL;

-- Verificar que no hay drivers sin cliente
SELECT 'Drivers sin cliente:' as mensaje, COUNT(*) as cantidad
FROM drivers WHERE client_id IS NULL;

-- ============================================================
-- CONFIGURACIONES DE SISTEMA - HIKVISION
-- ============================================================

-- Agregar configuraciones para cámara Hikvision
INSERT INTO settings (setting_key, setting_value) VALUES
('hikvision_api_url', ''),
('hikvision_username', ''),
('hikvision_password', ''),
('hikvision_verify_ssl', 'false')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- ============================================================
-- MENSAJES FINALES
-- ============================================================

SELECT 'Actualización de base de datos completada exitosamente.' as mensaje;
SELECT 'Versión: 1.3.0' as version;
SELECT 'Cambios aplicados:' as titulo;
SELECT '1. Unidades ahora requieren cliente y driver' as cambio;
SELECT '2. Año y número de serie son opcionales en unidades' as cambio;
SELECT '3. Drivers ahora requieren cliente' as cambio;
SELECT '4. Número de licencia y vencimiento son opcionales en drivers' as cambio;
SELECT '5. Access logs ahora soportan lectura de cámara de placas' as cambio;
SELECT '6. Configuraciones agregadas para cámara Hikvision' as cambio;
SELECT NOW() as fecha_actualizacion;
