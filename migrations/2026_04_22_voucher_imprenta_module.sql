-- =====================================================================
-- MIGRACIÓN: Módulo Imprenta para Vales
-- Fecha: 2026-04-22
-- MySQL: 5.7+
-- Descripción:
--   1) Agrega tipo de vale (standard/imprenta)
--   2) Agrega estado pending_assignment para activar vales al relacionarlos
--   3) Agrega índices para relación de vales en Gestión de Vales
-- =====================================================================

-- 1) Agregar columna voucher_type en vouchers (si no existe)
SET @voucher_type_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'vouchers'
      AND COLUMN_NAME = 'voucher_type'
);

SET @sql = IF(
    @voucher_type_exists = 0,
    'ALTER TABLE `vouchers`
     ADD COLUMN `voucher_type` ENUM(''standard'',''imprenta'') NOT NULL DEFAULT ''standard'' COMMENT ''Tipo de vale: estándar o imprenta'' AFTER `status`',
    'SELECT "Column voucher_type already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Asegurar estado pending_assignment en vouchers.status
SET @status_has_pending_assignment = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'vouchers'
      AND COLUMN_NAME = 'status'
      AND COLUMN_TYPE LIKE '%pending_assignment%'
);

SET @sql = IF(
    @status_has_pending_assignment = 0,
    'ALTER TABLE `vouchers`
     MODIFY COLUMN `status` ENUM(''active'',''used'',''cancelled'',''registered'',''pending_assignment'')
     COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''active''',
    'SELECT "Status enum already contains pending_assignment" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Índice para búsqueda/relación rápida de vales de imprenta
SET @idx_type_status_client_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'vouchers'
      AND INDEX_NAME = 'idx_voucher_type_status_client'
);

SET @sql = IF(
    @idx_type_status_client_exists = 0,
    'ALTER TABLE `vouchers`
     ADD INDEX `idx_voucher_type_status_client` (`voucher_type`, `status`, `client_id`, `serie`, `folio`)',
    'SELECT "Index idx_voucher_type_status_client already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4) Normalizar datos existentes sin tipo
UPDATE `vouchers`
SET `voucher_type` = 'standard'
WHERE `voucher_type` IS NULL;
