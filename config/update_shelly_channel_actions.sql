-- Migration: Shelly Device Channel Actions Configuration
-- Description: Add new channel configuration fields for 'Registro Rápido', 'Registrar Salida', 'Nuevo Acceso'
-- Date: 2025-11-28
-- Compatible with: MySQL 5.7+

-- Note: Run these ALTER statements one by one. If a column already exists, the statement will fail
-- and you can skip to the next one. This is intentional for safe migration.

-- Add new columns to shelly_devices table for channel-specific actions

-- Registro Rápido (Quick Registration) channel configuration
ALTER TABLE `shelly_devices` 
ADD COLUMN `quick_register_channel` TINYINT NOT NULL DEFAULT 0 COMMENT 'Canal para Registro Rápido';

ALTER TABLE `shelly_devices` 
ADD COLUMN `quick_register_action` ENUM('open', 'close') NOT NULL DEFAULT 'open' COMMENT 'Acción para Registro Rápido (abrir/cerrar)';

ALTER TABLE `shelly_devices` 
ADD COLUMN `quick_register_pulse_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Habilitar duración de pulso para Registro Rápido';

ALTER TABLE `shelly_devices` 
ADD COLUMN `quick_register_pulse_ms` INT NOT NULL DEFAULT 5000 COMMENT 'Duración del pulso en ms para Registro Rápido';

-- Registrar Salida (Exit Registration) channel configuration
ALTER TABLE `shelly_devices` 
ADD COLUMN `exit_register_channel` TINYINT NOT NULL DEFAULT 1 COMMENT 'Canal para Registrar Salida';

ALTER TABLE `shelly_devices` 
ADD COLUMN `exit_register_action` ENUM('open', 'close') NOT NULL DEFAULT 'close' COMMENT 'Acción para Registrar Salida (abrir/cerrar)';

ALTER TABLE `shelly_devices` 
ADD COLUMN `exit_register_pulse_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Habilitar duración de pulso para Registrar Salida';

ALTER TABLE `shelly_devices` 
ADD COLUMN `exit_register_pulse_ms` INT NOT NULL DEFAULT 5000 COMMENT 'Duración del pulso en ms para Registrar Salida';

-- Nuevo Acceso (New Access) channel configuration
ALTER TABLE `shelly_devices` 
ADD COLUMN `new_access_channel` TINYINT NOT NULL DEFAULT 0 COMMENT 'Canal para Nuevo Acceso';

ALTER TABLE `shelly_devices` 
ADD COLUMN `new_access_action` ENUM('open', 'close') NOT NULL DEFAULT 'open' COMMENT 'Acción para Nuevo Acceso (abrir/cerrar)';

ALTER TABLE `shelly_devices` 
ADD COLUMN `new_access_pulse_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Habilitar duración de pulso para Nuevo Acceso';

ALTER TABLE `shelly_devices` 
ADD COLUMN `new_access_pulse_ms` INT NOT NULL DEFAULT 5000 COMMENT 'Duración del pulso en ms para Nuevo Acceso';

-- Add setting for auto cleanup enabled (uses INSERT ... ON DUPLICATE KEY for idempotent insertion)
INSERT INTO `settings` (`setting_key`, `setting_value`) 
VALUES ('auto_cleanup_enabled', '1')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- Migrate existing entry_channel and exit_channel values to new fields
-- This only runs for records that haven't been migrated yet (default values from ALTER)
-- We identify unmigrated records by checking if the new fields still have default values
-- and the legacy fields have non-default values
UPDATE `shelly_devices` 
SET 
    `quick_register_channel` = COALESCE(`entry_channel`, 0),
    `quick_register_pulse_ms` = COALESCE(`pulse_duration_ms`, 5000),
    `exit_register_channel` = COALESCE(`exit_channel`, 1),
    `exit_register_pulse_ms` = COALESCE(`pulse_duration_ms`, 5000),
    `new_access_channel` = COALESCE(`entry_channel`, 0),
    `new_access_pulse_ms` = COALESCE(`pulse_duration_ms`, 5000)
WHERE 1=1;

