-- =====================================================
-- DUNAS v1.5.0 - Migration Script (corregido)
-- Cambios de validación y campos opcionales
-- Fecha: 2024-11-29
-- =====================================================

-- =====================================================
-- 1. Modificar tabla 'drivers' para hacer client_id opcional
-- =====================================================

-- Modificar columna client_id para permitir NULL (ya lo permite según el esquema)
ALTER TABLE `drivers` MODIFY `client_id` int(11) DEFAULT NULL;

-- Eliminar FK existente si existe y recrearla con ON DELETE SET NULL
-- NOTA: MySQL no soporta "DROP FOREIGN KEY IF EXISTS". Usar la siguiente línea si conoces el nombre exacto de la FK:
ALTER TABLE `drivers` DROP FOREIGN KEY `fk_drivers_client`;

-- Si no estás seguro del nombre de la restricción, puedes obtenerlo con:
-- SELECT CONSTRAINT_NAME
-- FROM information_schema.KEY_COLUMN_USAGE
-- WHERE TABLE_SCHEMA = DATABASE()
--   AND TABLE_NAME = 'drivers'
--   AND COLUMN_NAME = 'client_id'
--   AND REFERENCED_TABLE_NAME = 'clients';

-- Alternativa segura (no lanza error si no existe): (requiere ejecutar varios statements; utilizable en MySQL client)
-- DELIMITER $$
-- DROP PROCEDURE IF EXISTS drop_fk_if_exists$$
-- CREATE PROCEDURE drop_fk_if_exists()
-- BEGIN
--   DECLARE v_sql TEXT;
--   SELECT CONCAT('ALTER TABLE `drivers` DROP FOREIGN KEY `', CONSTRAINT_NAME, '`;')
--     INTO v_sql
--     FROM information_schema.KEY_COLUMN_USAGE
--     WHERE TABLE_SCHEMA = DATABASE()
--       AND TABLE_NAME = 'drivers'
--       AND COLUMN_NAME = 'client_id'
--       AND REFERENCED_TABLE_NAME = 'clients'
--     LIMIT 1;
--   IF v_sql IS NOT NULL THEN
--     PREPARE stmt FROM v_sql;
--     EXECUTE stmt;
--     DEALLOCATE PREPARE stmt;
--   END IF;
-- END$$
-- CALL drop_fk_if_exists();
-- DROP PROCEDURE drop_fk_if_exists$$
-- DELIMITER ;

-- Re-crear la FK con ON DELETE SET NULL
ALTER TABLE `drivers` 
    ADD CONSTRAINT `fk_drivers_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- 2. Modificar tabla 'clients' para hacer campos opcionales
-- =====================================================

-- Permitir valores vacíos en rfc_curp
ALTER TABLE `clients` MODIFY `rfc_curp` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';

-- Permitir valores vacíos en address
ALTER TABLE `clients` MODIFY `address` text COLLATE utf8mb4_unicode_ci;

-- Permitir valores vacíos en email (con valor por defecto)
ALTER TABLE `clients` MODIFY `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sin-email@dunas.com';

-- Establecer valor por defecto para client_type
ALTER TABLE `clients` MODIFY `client_type` enum('residential','commercial','industrial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'commercial';

-- =====================================================
-- 3. Actualizar registros existentes con valores por defecto
-- =====================================================

-- Actualizar emails vacíos con valor por defecto
UPDATE `clients` SET `email` = 'sin-email@dunas.com' WHERE `email` = '' OR `email` IS NULL;

-- Actualizar client_type vacío
UPDATE `clients` SET `client_type` = 'commercial' WHERE `client_type` = '' OR `client_type` IS NULL;

-- =====================================================
-- 4. Comentarios adicionales
-- =====================================================
-- Los siguientes cambios se realizan en la aplicación (código PHP):
-- - Botón "Registrar Acceso" fijo en el layout principal
-- - Cambio de código de barras a código QR en tickets
-- - Renombrar "Teléfono" a "Teléfono/WhatsApp" en formularios
-- - Validación de unicidad de teléfono para choferes
-- - Campo "Empresa" opcional en registro de choferes

-- =====================================================
-- FIN DEL SCRIPT DE MIGRACIÓN
-- =====================================================
