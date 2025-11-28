-- Migration: Shelly Device Channel Actions Configuration
-- Description: Add new channel configuration fields for 'Registro Rápido', 'Registrar Salida', 'Nuevo Acceso'
-- Date: 2025-11-28

-- Add new columns to shelly_devices table for channel-specific actions

-- Registro Rápido (Quick Registration) channel configuration
ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `quick_register_channel` TINYINT NOT NULL DEFAULT 0 COMMENT 'Canal para Registro Rápido' AFTER `exit_channel`;

ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `quick_register_action` ENUM('open', 'close') NOT NULL DEFAULT 'open' COMMENT 'Acción para Registro Rápido (abrir/cerrar)' AFTER `quick_register_channel`;

ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `quick_register_pulse_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Habilitar duración de pulso para Registro Rápido' AFTER `quick_register_action`;

ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `quick_register_pulse_ms` INT NOT NULL DEFAULT 5000 COMMENT 'Duración del pulso en ms para Registro Rápido' AFTER `quick_register_pulse_enabled`;

-- Registrar Salida (Exit Registration) channel configuration
ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `exit_register_channel` TINYINT NOT NULL DEFAULT 1 COMMENT 'Canal para Registrar Salida' AFTER `quick_register_pulse_ms`;

ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `exit_register_action` ENUM('open', 'close') NOT NULL DEFAULT 'close' COMMENT 'Acción para Registrar Salida (abrir/cerrar)' AFTER `exit_register_channel`;

ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `exit_register_pulse_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Habilitar duración de pulso para Registrar Salida' AFTER `exit_register_action`;

ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `exit_register_pulse_ms` INT NOT NULL DEFAULT 5000 COMMENT 'Duración del pulso en ms para Registrar Salida' AFTER `exit_register_pulse_enabled`;

-- Nuevo Acceso (New Access) channel configuration
ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `new_access_channel` TINYINT NOT NULL DEFAULT 0 COMMENT 'Canal para Nuevo Acceso' AFTER `exit_register_pulse_ms`;

ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `new_access_action` ENUM('open', 'close') NOT NULL DEFAULT 'open' COMMENT 'Acción para Nuevo Acceso (abrir/cerrar)' AFTER `new_access_channel`;

ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `new_access_pulse_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Habilitar duración de pulso para Nuevo Acceso' AFTER `new_access_action`;

ALTER TABLE `shelly_devices` 
ADD COLUMN IF NOT EXISTS `new_access_pulse_ms` INT NOT NULL DEFAULT 5000 COMMENT 'Duración del pulso en ms para Nuevo Acceso' AFTER `new_access_pulse_enabled`;

-- Add setting for auto cleanup enabled
INSERT INTO `settings` (`setting_key`, `setting_value`) 
VALUES ('auto_cleanup_enabled', '1')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- Migrate existing entry_channel and exit_channel values to new fields
UPDATE `shelly_devices` 
SET 
    `quick_register_channel` = COALESCE(`entry_channel`, 0),
    `quick_register_action` = 'open',
    `quick_register_pulse_enabled` = 1,
    `quick_register_pulse_ms` = COALESCE(`pulse_duration_ms`, 5000),
    `exit_register_channel` = COALESCE(`exit_channel`, 1),
    `exit_register_action` = 'close',
    `exit_register_pulse_enabled` = 1,
    `exit_register_pulse_ms` = COALESCE(`pulse_duration_ms`, 5000),
    `new_access_channel` = COALESCE(`entry_channel`, 0),
    `new_access_action` = 'open',
    `new_access_pulse_enabled` = 1,
    `new_access_pulse_ms` = COALESCE(`pulse_duration_ms`, 5000)
WHERE `quick_register_channel` = 0 
  AND `exit_register_channel` = 1 
  AND `new_access_channel` = 0;
