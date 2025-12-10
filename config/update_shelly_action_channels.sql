-- Script de Actualización: Canales y Acciones por Contexto en Shelly
-- Fecha: 2025-11-28
-- Descripción: Agrega columnas para configurar canales y acciones específicas por contexto

USE residenc_dunas;

-- ============================================================
-- ACTUALIZAR TABLA SHELLY_DEVICES
-- ============================================================

-- Columnas para Registro Rápido
ALTER TABLE shelly_devices 
ADD COLUMN quick_register_channel TINYINT(4) NULL DEFAULT NULL COMMENT 'Canal para registro rápido' AFTER pulse_duration_ms,
ADD COLUMN quick_register_action ENUM('open','close') NULL DEFAULT 'open' COMMENT 'Acción para registro rápido' AFTER quick_register_channel,
ADD COLUMN quick_register_pulse_enabled TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Si se usa pulso en registro rápido' AFTER quick_register_action,
ADD COLUMN quick_register_pulse_ms INT(11) NULL DEFAULT NULL COMMENT 'Duración del pulso para registro rápido (ms)' AFTER quick_register_pulse_enabled;

-- Columnas para Registro de Salida
ALTER TABLE shelly_devices 
ADD COLUMN exit_register_channel TINYINT(4) NULL DEFAULT NULL COMMENT 'Canal para registro de salida' AFTER quick_register_pulse_ms,
ADD COLUMN exit_register_action ENUM('open','close') NULL DEFAULT 'close' COMMENT 'Acción para registro de salida' AFTER exit_register_channel,
ADD COLUMN exit_register_pulse_enabled TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Si se usa pulso en registro de salida' AFTER exit_register_action,
ADD COLUMN exit_register_pulse_ms INT(11) NULL DEFAULT NULL COMMENT 'Duración del pulso para registro de salida (ms)' AFTER exit_register_pulse_enabled;

-- Columnas para Nuevo Acceso
ALTER TABLE shelly_devices 
ADD COLUMN new_access_channel TINYINT(4) NULL DEFAULT NULL COMMENT 'Canal para nuevo acceso' AFTER exit_register_pulse_ms,
ADD COLUMN new_access_action ENUM('open','close') NULL DEFAULT 'open' COMMENT 'Acción para nuevo acceso' AFTER new_access_channel,
ADD COLUMN new_access_pulse_enabled TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Si se usa pulso en nuevo acceso' AFTER new_access_action,
ADD COLUMN new_access_pulse_ms INT(11) NULL DEFAULT NULL COMMENT 'Duración del pulso para nuevo acceso (ms)' AFTER new_access_pulse_enabled;

-- ============================================================
-- MIGRAR DATOS EXISTENTES
-- ============================================================

-- Copiar configuraciones legacy a los nuevos campos
UPDATE shelly_devices 
SET 
    quick_register_channel = entry_channel,
    quick_register_action = 'open',
    quick_register_pulse_enabled = 1,
    quick_register_pulse_ms = pulse_duration_ms,
    
    exit_register_channel = exit_channel,
    exit_register_action = 'close',
    exit_register_pulse_enabled = 1,
    exit_register_pulse_ms = pulse_duration_ms,
    
    new_access_channel = entry_channel,
    new_access_action = 'open',
    new_access_pulse_enabled = 1,
    new_access_pulse_ms = pulse_duration_ms
WHERE 
    quick_register_channel IS NULL 
    OR exit_register_channel IS NULL 
    OR new_access_channel IS NULL;

-- ============================================================
-- VALIDACIÓN
-- ============================================================

SELECT 'Tabla shelly_devices actualizada exitosamente.' as mensaje;
SELECT 'Se agregaron columnas para configurar canales y acciones por contexto.' as info;
SELECT 'Los valores legacy fueron migrados a los nuevos campos.' as migracion;
