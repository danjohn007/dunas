-- =====================================================================
-- MIGRACIÓN: Corrección de salto de folio y alineación de impresión
-- Fecha: 2026-04-25
-- MySQL: 5.7+
-- Descripción:
--   1) Cancela registros duplicados de (serie, folio) conservando el
--      vale con id más bajo, para dejar la tabla en estado consistente.
--   2) Agrega restricción UNIQUE en (serie, folio) para garantizar a
--      nivel de base de datos que no existan dos vales con la misma
--      serie y folio, evitando así los saltos de folio al generar
--      nuevos lotes.
-- Nota:
--   Los ajustes de la lógica PHP (getNextAvailableFolio en storeImprenta)
--   y de CSS (justify-content: space-between en la vista print_batch)
--   se aplican en los archivos correspondientes del código fuente.
-- =====================================================================

-- -----------------------------------------------------------------------
-- 1. Cancelar duplicados de (serie, folio) conservando el id más bajo
-- -----------------------------------------------------------------------
UPDATE `vouchers` v
INNER JOIN (
    SELECT MIN(`id`) AS keep_id, `serie`, `folio`
    FROM   `vouchers`
    GROUP  BY `serie`, `folio`
    HAVING COUNT(*) > 1
) dup ON  v.`serie`  = dup.`serie`
      AND v.`folio`  = dup.`folio`
      AND v.`id`    <> dup.keep_id
SET v.`status` = 'cancelled';

-- -----------------------------------------------------------------------
-- 2. Agregar UNIQUE KEY en (serie, folio) si todavía no existe
-- -----------------------------------------------------------------------
SET @uk_serie_folio = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'uk_serie_folio'
);

SET @sql = IF(
    @uk_serie_folio = 0,
    'ALTER TABLE `vouchers` ADD UNIQUE KEY `uk_serie_folio` (`serie`, `folio`)',
    'SELECT "Unique key uk_serie_folio ya existe, no se requiere cambio" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migración 2026_04_25_fix_folio_jump_and_print completada exitosamente' AS status;
