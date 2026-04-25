-- =====================================================================
-- MIGRACIÓN: Ajustes en Generación de Vales
-- Fecha: 2026-04-25
-- MySQL: 5.7+
-- Descripción:
--   1) Expande la columna `serie` de VARCHAR(1) a VARCHAR(2) para
--      permitir series de 1 a 2 letras.
--   2) Elimina el índice único de qr_code para poder actualizar
--      los códigos sin conflictos.
--   3) Migra el qr_code de los vales de imprenta al nuevo formato:
--      SERIE + capacidad + folio con 4 dígitos (sin separadores).
--      Ejemplo: serie=A, capacity=10000, folio=1  → qr_code="A100000001"
--      Ejemplo: serie=AB, capacity=20000, folio=101 → qr_code="AB200000101"
--   4) Recrea el índice único sobre qr_code.
-- =====================================================================

-- -----------------------------------------------------------------------
-- 1. Expandir columna serie a VARCHAR(2)
-- -----------------------------------------------------------------------
SET @serie_type = (
    SELECT COLUMN_TYPE
    FROM   INFORMATION_SCHEMA.COLUMNS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  COLUMN_NAME  = 'serie'
);

SET @sql = IF(
    @serie_type = 'varchar(1)',
    'ALTER TABLE `vouchers` MODIFY COLUMN `serie` VARCHAR(2) NOT NULL COMMENT ''Serie del vale: 1 a 2 letras mayúsculas''',
    'SELECT "Column serie is already VARCHAR(2) or wider, no change needed" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------
-- 2. Eliminar índice único de qr_code si existe (para permitir la
--    actualización masiva sin conflictos de unicidad transitorios)
-- -----------------------------------------------------------------------
SET @idx_qrcode_exists = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'idx_qrcode'
);

SET @sql = IF(
    @idx_qrcode_exists > 0,
    'ALTER TABLE `vouchers` DROP INDEX `idx_qrcode`',
    'SELECT "Index idx_qrcode does not exist, nothing to drop" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------
-- 3. Migrar qr_code de vales de imprenta al nuevo formato:
--    SERIE + capacidad + LPAD(folio, 4, '0')
--    Solo para registros con capacity > 0 (datos válidos).
-- -----------------------------------------------------------------------
UPDATE `vouchers`
SET    `qr_code` = CONCAT(UPPER(`serie`), `capacity`, LPAD(`folio`, 4, '0'))
WHERE  `voucher_type` = 'imprenta'
  AND  `capacity` > 0
  AND  `capacity` IS NOT NULL;

-- -----------------------------------------------------------------------
-- 4. Cancelar duplicados de qr_code (si los hubiera tras la migración),
--    conservando el vale con id más bajo.
-- -----------------------------------------------------------------------
UPDATE `vouchers` v
INNER JOIN (
    SELECT MIN(`id`) AS keep_id, `qr_code`
    FROM   `vouchers`
    GROUP BY `qr_code`
    HAVING COUNT(*) > 1
) dup ON v.`qr_code` = dup.`qr_code`
       AND v.`id`    <> dup.keep_id
SET v.`status` = 'cancelled';

-- -----------------------------------------------------------------------
-- 5. Recrear índice único sobre qr_code
-- -----------------------------------------------------------------------
ALTER TABLE `vouchers`
ADD UNIQUE INDEX `idx_qrcode` (`qr_code`(191));
