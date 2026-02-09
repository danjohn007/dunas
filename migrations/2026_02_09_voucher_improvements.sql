-- =====================================================================
-- MIGRATION: Sistema de Vales - Mejoras Completas
-- Fecha: 2026-02-09
-- Descripción: 
--   1. Tabla de registro de errores del sistema
--   2. Tabla de pagos de vales (parciales)
--   3. Corrección de constraint de clients con ON DELETE RESTRICT explícito
--   4. Índices adicionales para optimización de consultas
-- =====================================================================

-- 1. Crear tabla de registro de errores del sistema
-- Esta tabla almacena todos los errores que ocurren en el sistema
CREATE TABLE IF NOT EXISTS `error_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `level` ENUM('critical','error','warning','info') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'error' COMMENT 'Nivel del error',
  `message` TEXT COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mensaje del error',
  `context` TEXT COLLATE utf8mb4_unicode_ci NULL COMMENT 'Contexto adicional del error (JSON)',
  `user_id` INT(11) NULL COMMENT 'ID del usuario que generó el error',
  `ip_address` VARCHAR(45) COLLATE utf8mb4_unicode_ci NULL COMMENT 'Dirección IP del usuario',
  `user_agent` TEXT COLLATE utf8mb4_unicode_ci NULL COMMENT 'User agent del navegador',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_level` (`level`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_user_id` (`user_id`),
  CONSTRAINT `fk_error_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de errores del sistema';

-- 2. Crear tabla de pagos de vales
-- Esta tabla permite registrar pagos parciales de los vales generados
CREATE TABLE IF NOT EXISTS `voucher_payments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) NOT NULL COMMENT 'ID del cliente que realiza el pago',
  `amount` DECIMAL(10,2) NOT NULL COMMENT 'Monto del pago',
  `payment_date` DATE NOT NULL COMMENT 'Fecha del pago',
  `payment_method` ENUM('cash','transfer','check','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash' COMMENT 'Método de pago',
  `reference` VARCHAR(100) COLLATE utf8mb4_unicode_ci NULL COMMENT 'Referencia del pago (número de transferencia, cheque, etc.)',
  `notes` TEXT COLLATE utf8mb4_unicode_ci NULL COMMENT 'Notas adicionales sobre el pago',
  `created_by` INT(11) NOT NULL COMMENT 'ID del usuario que registró el pago',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_client_id` (`client_id`),
  INDEX `idx_payment_date` (`payment_date`),
  INDEX `idx_created_by` (`created_by`),
  CONSTRAINT `fk_voucher_payments_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_voucher_payments_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de pagos de vales por empresa/cliente';

-- 3. Verificar y actualizar foreign keys de access_logs si es necesario
-- Solo si la constraint existe, la eliminamos y la recreamos con ON DELETE RESTRICT explícito
SET @constraint_name = (
  SELECT CONSTRAINT_NAME 
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'access_logs'
    AND COLUMN_NAME = 'client_id'
    AND REFERENCED_TABLE_NAME = 'clients'
  LIMIT 1
);

SET @sql = IF(@constraint_name IS NOT NULL,
  CONCAT('ALTER TABLE `access_logs` DROP FOREIGN KEY `', @constraint_name, '`'),
  'SELECT "Foreign key already dropped or does not exist" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Recrear foreign key con ON DELETE RESTRICT explícito
ALTER TABLE `access_logs` 
ADD CONSTRAINT `fk_access_logs_client` 
FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT;

-- 4. Verificar y agregar índices adicionales si no existen
-- Índice compuesto para consultas de vales por empresa y fecha
ALTER TABLE `vouchers` 
ADD INDEX IF NOT EXISTS `idx_client_created` (`client_id`, `created_at`);

-- Índice para búsquedas por serie
ALTER TABLE `vouchers` 
ADD INDEX IF NOT EXISTS `idx_serie` (`serie`);

-- 5. Actualizar comentarios de tablas para documentación
ALTER TABLE `vouchers` COMMENT='Tabla de vales generados para control de suministro de agua con soporte de pagos';
ALTER TABLE `clients` COMMENT='Tabla de clientes del sistema con protección de integridad referencial';

-- =====================================================================
-- VERIFICACIÓN DE LA MIGRACIÓN
-- =====================================================================
-- Ejecutar estas queries para verificar que la migración se aplicó correctamente:

-- Verificar que la tabla error_logs existe
-- SELECT COUNT(*) as error_logs_exists FROM INFORMATION_SCHEMA.TABLES 
-- WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'error_logs';

-- Verificar que la tabla voucher_payments existe
-- SELECT COUNT(*) as voucher_payments_exists FROM INFORMATION_SCHEMA.TABLES 
-- WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'voucher_payments';

-- Verificar foreign keys de access_logs
-- SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
-- WHERE TABLE_SCHEMA = DATABASE() 
--   AND TABLE_NAME = 'access_logs' 
--   AND REFERENCED_TABLE_NAME = 'clients';

-- Verificar índices de vouchers
-- SHOW INDEX FROM vouchers WHERE Key_name IN ('idx_client_created', 'idx_serie');

-- =====================================================================
-- NOTAS IMPORTANTES
-- =====================================================================
-- 1. Esta migración es IDEMPOTENTE - puede ejecutarse múltiples veces sin errores
-- 2. Las foreign keys con ON DELETE RESTRICT previenen eliminación accidental de datos
-- 3. Los índices mejoran el rendimiento de consultas de reportes
-- 4. La tabla de pagos soporta pagos parciales y múltiples métodos de pago
-- 5. El registro de errores ayuda en el monitoreo y debugging del sistema
-- =====================================================================
