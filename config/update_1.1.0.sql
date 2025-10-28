-- Sistema de Control de Acceso con IoT
-- Script de Actualización de Base de Datos
-- Fecha: 2024-10-28
-- Versión: 1.1.0

USE dunas_access_control;

-- Crear tabla de configuraciones del sistema
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones por defecto
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Sistema de Control de Acceso con IoT'),
('site_logo', ''),
('system_email', 'sistema@dunas.com'),
('whatsapp_number', ''),
('contact_phone', ''),
('contact_phone_secondary', ''),
('business_hours_open', '08:00'),
('business_hours_close', '18:00'),
('shelly_api_url', 'http://192.168.1.100'),
('shelly_relay_open', '0'),
('shelly_relay_close', '1'),
('ticket_footer_message', 'Gracias por su preferencia. Para cualquier duda o aclaración contáctenos.')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Verificar que la tabla de usuarios tenga todos los campos necesarios
-- (No se hacen cambios a tablas existentes para preservar datos)

-- Verificar integridad de datos existentes
-- Asegurarse de que no haya transacciones huérfanas
UPDATE transactions t
LEFT JOIN access_logs al ON t.access_log_id = al.id
SET t.access_log_id = NULL
WHERE al.id IS NULL AND t.access_log_id IS NOT NULL;

-- Asegurarse de que todos los access_logs tengan clientes, unidades y choferes válidos
-- (Esto es informativo, no se hacen cambios destructivos)
SELECT 'Verificando integridad de datos...' as mensaje;

SELECT COUNT(*) as access_logs_sin_cliente
FROM access_logs al
LEFT JOIN clients c ON al.client_id = c.id
WHERE c.id IS NULL;

SELECT COUNT(*) as access_logs_sin_unidad
FROM access_logs al
LEFT JOIN units u ON al.unit_id = u.id
WHERE u.id IS NULL;

SELECT COUNT(*) as access_logs_sin_chofer
FROM access_logs al
LEFT JOIN drivers d ON al.driver_id = d.id
WHERE d.id IS NULL;

-- Agregar índices adicionales para mejorar el rendimiento de reportes
CREATE INDEX IF NOT EXISTS idx_transaction_date_status 
ON transactions(transaction_date, payment_status);

CREATE INDEX IF NOT EXISTS idx_access_entry_status 
ON access_logs(entry_datetime, status);

CREATE INDEX IF NOT EXISTS idx_client_type_status 
ON clients(client_type, status);

-- Mensaje final
SELECT 'Actualización de base de datos completada exitosamente.' as mensaje;
SELECT 'Versión: 1.1.0' as version;
SELECT NOW() as fecha_actualizacion;
