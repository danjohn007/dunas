-- =====================================================================
-- MIGRACIÓN: Soporte de empresa en tabla de Gestión de Vales
-- Fecha: 2026-05-14
-- Objetivo:
--   Asegurar que vouchers tenga client_id para mostrar la Empresa asociada.
-- Compatible con MySQL 5.7.
-- =====================================================================

-- 1) Agregar columna client_id si no existe
SET @column_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'vouchers'
    AND COLUMN_NAME = 'client_id'
);

SET @sql = IF(
  @column_exists = 0,
  'ALTER TABLE `vouchers` ADD COLUMN `client_id` INT(11) NULL AFTER `created_by`',
  'SELECT "Column vouchers.client_id already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Agregar índice para relación por cliente si no existe
SET @index_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'vouchers'
    AND INDEX_NAME = 'idx_client_id'
);

SET @sql = IF(
  @index_exists = 0,
  'ALTER TABLE `vouchers` ADD INDEX `idx_client_id` (`client_id`)',
  'SELECT "Index vouchers.idx_client_id already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Agregar foreign key a clients si no existe
SET @fk_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND CONSTRAINT_NAME = 'fk_vouchers_client'
    AND TABLE_NAME = 'vouchers'
);

SET @sql = IF(
  @fk_exists = 0,
  'ALTER TABLE `vouchers` ADD CONSTRAINT `fk_vouchers_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT "Foreign key fk_vouchers_client already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
