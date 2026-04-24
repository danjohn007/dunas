-- =====================================================================
-- MIGRACIÓN: Ajuste de impresión de vales – layout 12.5 × 9.2 cm
-- Fecha: 2026-04-24
-- MySQL: 5.7+
-- Descripción:
--   Normaliza el campo qr_code de todos los vales activos que aún
--   guardan el formato largo (SERIE-FOLIO-TIMESTAMP) al formato corto
--   (SERIE-FOLIO) usado por el nuevo diseño de impresión.
--
--   Los vales ya usados o cancelados se dejan intactos para preservar
--   el historial de auditoría.
-- =====================================================================

-- -----------------------------------------------------------------------
-- 1. Actualizar qr_code de vales estándar con formato antiguo (3 partes)
--    Ejemplo: "R-501-1738694876"  →  "R-501"
-- -----------------------------------------------------------------------
UPDATE `vouchers`
SET    `qr_code` = CONCAT(`serie`, '-', `folio`)
WHERE  `status` IN ('active', 'pending_assignment')
  AND  (
         -- El formato antiguo tiene al menos dos guiones (tres segmentos)
         LENGTH(`qr_code`) - LENGTH(REPLACE(`qr_code`, '-', '')) >= 2
       )
  AND  (`voucher_type` = 'standard' OR `voucher_type` IS NULL);

-- -----------------------------------------------------------------------
-- 2. Actualizar qr_code de vales de imprenta con formato antiguo
--    El folio se rellena con ceros hasta 4 dígitos.
--    Ejemplo: "S-0001-1738694876"  →  "S-0001"
-- -----------------------------------------------------------------------
UPDATE `vouchers`
SET    `qr_code` = CONCAT(`serie`, '-', LPAD(`folio`, 4, '0'))
WHERE  `status` IN ('active', 'pending_assignment')
  AND  (
         LENGTH(`qr_code`) - LENGTH(REPLACE(`qr_code`, '-', '')) >= 2
       )
  AND  `voucher_type` = 'imprenta';

-- -----------------------------------------------------------------------
-- 3. Índice de soporte para búsqueda rápida por qr_code (si no existe)
-- -----------------------------------------------------------------------
SET @idx_qrcode_exists = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'idx_qrcode'
);

SET @sql = IF(
    @idx_qrcode_exists = 0,
    'ALTER TABLE `vouchers` ADD UNIQUE INDEX `idx_qrcode` (`qr_code`(191))',
    'SELECT "Index idx_qrcode already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
