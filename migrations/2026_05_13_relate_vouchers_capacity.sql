-- =====================================================================
-- MIGRACIÓN: Soporte de capacidad en Relacionar Vales de Imprenta
-- Fecha: 2026-05-13
-- MySQL: 5.7+
-- Descripción:
--   1) Expande la columna `serie` de VARCHAR(2) a VARCHAR(5) para
--      permitir series de más de 2 letras según lo indicado en el
--      issue: "series pueden ser de 2 o más letras".
--   2) Agrega un índice compuesto para acelerar la consulta de
--      Relacionar Vales que filtra por (voucher_type, status,
--      client_id, serie, capacity, folio).
-- =====================================================================

-- -----------------------------------------------------------------------
-- 1. Expandir columna serie a VARCHAR(5) si aún es VARCHAR(1) o VARCHAR(2)
-- -----------------------------------------------------------------------
SET @serie_type = (
    SELECT COLUMN_TYPE
    FROM   INFORMATION_SCHEMA.COLUMNS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  COLUMN_NAME  = 'serie'
);

SET @sql = IF(
    @serie_type IN ('varchar(1)', 'varchar(2)'),
    'ALTER TABLE `vouchers` MODIFY COLUMN `serie` VARCHAR(5) NOT NULL COMMENT ''Serie del vale: 1 a 5 letras mayúsculas''',
    'SELECT "Column serie is already VARCHAR(5) or wider, no change needed" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------
-- 2. Agregar índice compuesto para acelerar búsquedas de relación de vales
--    por (voucher_type, status, serie, capacity, folio)
-- -----------------------------------------------------------------------
SET @idx_relate_exists = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'idx_relate_vouchers'
);

SET @sql = IF(
    @idx_relate_exists = 0,
    'ALTER TABLE `vouchers` ADD INDEX `idx_relate_vouchers` (`voucher_type`, `status`, `serie`, `capacity`, `folio`)',
    'SELECT "Index idx_relate_vouchers already exists, no change needed" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migración 2026_05_13_relate_vouchers_capacity completada exitosamente' AS status;
