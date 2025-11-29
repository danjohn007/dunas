-- =====================================================
-- DUNAS v1.5.0 - Migration Script
-- Cambios de validación y campos opcionales
-- Fecha: 2024-11-29
-- =====================================================

-- =====================================================
-- 1. Modificar tabla 'drivers' para hacer client_id opcional
-- =====================================================

-- Modificar columna client_id para permitir NULL (ya lo permite según el esquema)
ALTER TABLE `drivers` MODIFY `client_id` int(11) DEFAULT NULL;

-- Eliminar FK existente si existe y recrearla con ON DELETE SET NULL
ALTER TABLE `drivers` DROP FOREIGN KEY IF EXISTS `fk_drivers_client`;
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
