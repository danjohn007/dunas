-- =====================================================================
-- MIGRACIÓN: Folio único por serie + capacidad (en lugar de sólo serie)
-- Fecha: 2026-04-26
-- MySQL: 5.7+
-- Descripción:
--   Permite generar vales con folio inicial 1 (o el solicitado) cuando
--   la capacidad del nuevo lote es diferente a la de lotes anteriores
--   de la misma serie.
--
--   Cambios:
--     1) Elimina la restricción UNIQUE (serie, folio) para desacoplar
--        el folio de la capacidad.
--     2) Agrega la restricción UNIQUE (serie, folio, capacity) para que
--        la combinación de serie + folio siga siendo única dentro de
--        una misma capacidad, evitando duplicados en el mismo lote.
--
--   Nota: el código QR de los vales de imprenta ya incorpora la
--   capacidad (formato: SERIE + capacity + folio4dígitos), por lo que
--   la unicidad del QR sigue garantizada a nivel de base de datos
--   mediante el índice UNIQUE idx_qrcode.
-- =====================================================================

-- -----------------------------------------------------------------------
-- 1. Eliminar restricción UNIQUE (serie, folio) — puede tener cualquiera
--    de los dos nombres usados en migraciones anteriores.
-- -----------------------------------------------------------------------

-- Eliminar uk_serie_folio si existe
SET @uk_exists_1 = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'uk_serie_folio'
);

SET @sql = IF(
    @uk_exists_1 > 0,
    'ALTER TABLE `vouchers` DROP INDEX `uk_serie_folio`',
    'SELECT "Index uk_serie_folio no existe, nada que eliminar" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Eliminar idx_serie_folio_unique si existe
SET @uk_exists_2 = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'idx_serie_folio_unique'
);

SET @sql = IF(
    @uk_exists_2 > 0,
    'ALTER TABLE `vouchers` DROP INDEX `idx_serie_folio_unique`',
    'SELECT "Index idx_serie_folio_unique no existe, nada que eliminar" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------
-- 2. Agregar restricción UNIQUE (serie, folio, capacity) si no existe
-- -----------------------------------------------------------------------

SET @new_uk_exists = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'uk_serie_folio_capacity'
);

SET @sql = IF(
    @new_uk_exists = 0,
    'ALTER TABLE `vouchers` ADD UNIQUE KEY `uk_serie_folio_capacity` (`serie`, `folio`, `capacity`)',
    'SELECT "Unique key uk_serie_folio_capacity ya existe, no se requiere cambio" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migración 2026_04_26_voucher_folio_by_capacity completada exitosamente' AS status;
