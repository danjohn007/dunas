-- =====================================================================
-- MIGRACIÓN: Folio único por (serie, folio, capacidad)
-- Fecha: 2026-04-26
-- MySQL: 5.7+
-- Descripción:
--   Reemplaza la restricción UNIQUE `uk_serie_folio` (serie, folio) por
--   `uk_serie_folio_capacity` (serie, folio, capacity), de modo que dos
--   lotes de vales de diferente capacidad en litros puedan compartir el
--   mismo rango de folios (ej. ambos del 1 en adelante) sin conflicto.
--
--   Pasos:
--   1) Cancelar duplicados de (serie, folio, capacity) conservando el
--      vale con id más bajo (precaución ante datos inconsistentes).
--   2) Eliminar la restricción uk_serie_folio si existe.
--   3) Agregar la nueva restricción uk_serie_folio_capacity si no existe.
-- =====================================================================

-- -----------------------------------------------------------------------
-- 1. Cancelar duplicados de (serie, folio, capacity) conservando el
--    registro con id más bajo.
-- -----------------------------------------------------------------------
UPDATE `vouchers` v
INNER JOIN (
    SELECT MIN(`id`) AS keep_id, `serie`, `folio`, `capacity`
    FROM   `vouchers`
    GROUP  BY `serie`, `folio`, `capacity`
    HAVING COUNT(*) > 1
) dup ON  v.`serie`    = dup.`serie`
      AND v.`folio`    = dup.`folio`
      AND v.`capacity` = dup.`capacity`
      AND v.`id`      <> dup.keep_id
SET v.`status` = 'cancelled';

-- -----------------------------------------------------------------------
-- 2. Eliminar la restricción uk_serie_folio si todavía existe
-- -----------------------------------------------------------------------
SET @uk_old = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'uk_serie_folio'
);

SET @sql = IF(
    @uk_old > 0,
    'ALTER TABLE `vouchers` DROP INDEX `uk_serie_folio`',
    'SELECT "Index uk_serie_folio no existe, no se requiere cambio" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------
-- 3. Agregar UNIQUE KEY en (serie, folio, capacity) si no existe
-- -----------------------------------------------------------------------
SET @uk_new = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'uk_serie_folio_capacity'
);

SET @sql = IF(
    @uk_new = 0,
    'ALTER TABLE `vouchers` ADD UNIQUE KEY `uk_serie_folio_capacity` (`serie`, `folio`, `capacity`)',
    'SELECT "Unique key uk_serie_folio_capacity ya existe, no se requiere cambio" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migración 2026_04_26_folio_per_capacity completada exitosamente' AS status;
