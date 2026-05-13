-- =====================================================================
-- MIGRACIÓN: Corrección definitiva del constraint serie+folio
-- Fecha: 2026-05-13
-- MySQL: 5.7+
-- Descripción:
--   Reemplaza cualquier restricción UNIQUE sobre (serie, folio) por
--   una nueva sobre (serie, folio, capacity), permitiendo reutilizar
--   el mismo rango de folios para distintas capacidades (QR codes
--   distintos ya que el QR incluye la capacidad en su valor).
--
--   Maneja todos los posibles nombres del constraint en producción:
--     - 'serie_folio'           (nombre encontrado en producción)
--     - 'uk_serie_folio'        (nombre usado en migraciones previas)
--     - 'idx_serie_folio_unique'(nombre usado en scripts de config)
--
--   Adicionalmente cancela duplicados de (serie, folio, capacity)
--   conservando el registro con id más bajo.
-- =====================================================================

-- -----------------------------------------------------------------------
-- 1. Cancelar duplicados de (serie, folio, capacity), conservando el
--    registro con id más bajo para cada trío.
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
-- 2. Eliminar el constraint 'serie_folio' (nombre en producción)
-- -----------------------------------------------------------------------
SET @drop1 = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'serie_folio'
);
SET @sql = IF(@drop1 > 0,
    'ALTER TABLE `vouchers` DROP INDEX `serie_folio`',
    'SELECT "Index serie_folio no existe, sin cambio" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------
-- 3. Eliminar el constraint 'uk_serie_folio' (migraciones previas)
-- -----------------------------------------------------------------------
SET @drop2 = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'uk_serie_folio'
);
SET @sql = IF(@drop2 > 0,
    'ALTER TABLE `vouchers` DROP INDEX `uk_serie_folio`',
    'SELECT "Index uk_serie_folio no existe, sin cambio" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------
-- 4. Eliminar el constraint 'idx_serie_folio_unique' (scripts de config)
-- -----------------------------------------------------------------------
SET @drop3 = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'idx_serie_folio_unique'
);
SET @sql = IF(@drop3 > 0,
    'ALTER TABLE `vouchers` DROP INDEX `idx_serie_folio_unique`',
    'SELECT "Index idx_serie_folio_unique no existe, sin cambio" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------
-- 5. Agregar UNIQUE KEY en (serie, folio, capacity) si no existe ya
-- -----------------------------------------------------------------------
SET @add_new = (
    SELECT COUNT(*)
    FROM   INFORMATION_SCHEMA.STATISTICS
    WHERE  TABLE_SCHEMA = DATABASE()
      AND  TABLE_NAME   = 'vouchers'
      AND  INDEX_NAME   = 'uk_serie_folio_capacity'
);
SET @sql = IF(@add_new = 0,
    'ALTER TABLE `vouchers` ADD UNIQUE KEY `uk_serie_folio_capacity` (`serie`, `folio`, `capacity`)',
    'SELECT "Unique key uk_serie_folio_capacity ya existe, sin cambio" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migración 2026_05_13_fix_serie_folio_constraint completada exitosamente' AS status;
