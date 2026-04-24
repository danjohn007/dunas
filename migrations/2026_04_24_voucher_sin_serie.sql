-- =====================================================================
-- MIGRACIÓN: Ajuste en Vales (sin serie) – QR = solo 4 dígitos del folio
-- Fecha: 2026-04-24
-- MySQL: 5.7+
-- Descripción:
--   1) Elimina temporalmente el índice único de qr_code para permitir
--      la actualización masiva sin conflictos de duplicados.
--   2) Actualiza qr_code de TODOS los vales al formato de solo 4 dígitos
--      (LPAD(folio, 4, '0')), eliminando la parte de la serie.
--   3) En caso de colisiones (dos vales de distinta serie con el mismo
--      folio), conserva el vale con id menor y cancela los duplicados
--      para mantener la unicidad.
--   4) Recrea el índice único sobre qr_code.
-- =====================================================================

-- -----------------------------------------------------------------------
-- 1. Eliminar índice único de qr_code si existe
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
-- 2. Actualizar qr_code de todos los vales al formato de 4 dígitos
-- -----------------------------------------------------------------------
UPDATE `vouchers`
SET    `qr_code` = LPAD(`folio`, 4, '0');

-- -----------------------------------------------------------------------
-- 3. Resolver duplicados generados por vales de distinta serie con el
--    mismo folio: cancelar los duplicados (mantener el de id más bajo).
--    Los registros cancelados por esta migración pueden identificarse
--    consultando: SELECT * FROM vouchers WHERE status='cancelled'
--    y comparando con vales activos que tengan el mismo folio.
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
-- 4. Recrear índice único sobre qr_code
-- -----------------------------------------------------------------------
ALTER TABLE `vouchers`
ADD UNIQUE INDEX `idx_qrcode` (`qr_code`(191));
