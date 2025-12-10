-- Script de Actualización: Canales y Acciones por Contexto en Shelly (SEGURO)
-- Fecha: 2025-11-28
-- Descripción: Agrega columnas verificando si existen primero

USE residenc_dunas;

-- ============================================================
-- VERIFICAR Y AGREGAR COLUMNAS
-- ============================================================

-- Columnas para Registro Rápido
SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'quick_register_channel') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN quick_register_channel TINYINT(4) NULL DEFAULT NULL COMMENT ''Canal para registro rápido'' AFTER pulse_duration_ms',
    'SELECT "La columna quick_register_channel ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'quick_register_action') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN quick_register_action ENUM(''open'',''close'') NULL DEFAULT ''open'' COMMENT ''Acción para registro rápido'' AFTER quick_register_channel',
    'SELECT "La columna quick_register_action ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'quick_register_pulse_enabled') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN quick_register_pulse_enabled TINYINT(1) NOT NULL DEFAULT 1 COMMENT ''Si se usa pulso en registro rápido'' AFTER quick_register_action',
    'SELECT "La columna quick_register_pulse_enabled ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'quick_register_pulse_ms') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN quick_register_pulse_ms INT(11) NULL DEFAULT NULL COMMENT ''Duración del pulso para registro rápido (ms)'' AFTER quick_register_pulse_enabled',
    'SELECT "La columna quick_register_pulse_ms ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Columnas para Registro de Salida
SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'exit_register_channel') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN exit_register_channel TINYINT(4) NULL DEFAULT NULL COMMENT ''Canal para registro de salida'' AFTER quick_register_pulse_ms',
    'SELECT "La columna exit_register_channel ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'exit_register_action') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN exit_register_action ENUM(''open'',''close'') NULL DEFAULT ''close'' COMMENT ''Acción para registro de salida'' AFTER exit_register_channel',
    'SELECT "La columna exit_register_action ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'exit_register_pulse_enabled') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN exit_register_pulse_enabled TINYINT(1) NOT NULL DEFAULT 1 COMMENT ''Si se usa pulso en registro de salida'' AFTER exit_register_action',
    'SELECT "La columna exit_register_pulse_enabled ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'exit_register_pulse_ms') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN exit_register_pulse_ms INT(11) NULL DEFAULT NULL COMMENT ''Duración del pulso para registro de salida (ms)'' AFTER exit_register_pulse_enabled',
    'SELECT "La columna exit_register_pulse_ms ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Columnas para Nuevo Acceso
SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'new_access_channel') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN new_access_channel TINYINT(4) NULL DEFAULT NULL COMMENT ''Canal para nuevo acceso'' AFTER exit_register_pulse_ms',
    'SELECT "La columna new_access_channel ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'new_access_action') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN new_access_action ENUM(''open'',''close'') NULL DEFAULT ''open'' COMMENT ''Acción para nuevo acceso'' AFTER new_access_channel',
    'SELECT "La columna new_access_action ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'new_access_pulse_enabled') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN new_access_pulse_enabled TINYINT(1) NOT NULL DEFAULT 1 COMMENT ''Si se usa pulso en nuevo acceso'' AFTER new_access_action',
    'SELECT "La columna new_access_pulse_enabled ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @query = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'residenc_dunas' 
     AND TABLE_NAME = 'shelly_devices' 
     AND COLUMN_NAME = 'new_access_pulse_ms') = 0,
    'ALTER TABLE shelly_devices ADD COLUMN new_access_pulse_ms INT(11) NULL DEFAULT NULL COMMENT ''Duración del pulso para nuevo acceso (ms)'' AFTER new_access_pulse_enabled',
    'SELECT "La columna new_access_pulse_ms ya existe" AS mensaje'
);
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- MIGRAR DATOS EXISTENTES
-- ============================================================

-- Copiar configuraciones legacy a los nuevos campos
UPDATE shelly_devices 
SET 
    quick_register_channel = COALESCE(quick_register_channel, entry_channel),
    quick_register_action = COALESCE(quick_register_action, 'open'),
    quick_register_pulse_enabled = COALESCE(quick_register_pulse_enabled, 1),
    quick_register_pulse_ms = COALESCE(quick_register_pulse_ms, pulse_duration_ms),
    
    exit_register_channel = COALESCE(exit_register_channel, exit_channel),
    exit_register_action = COALESCE(exit_register_action, 'close'),
    exit_register_pulse_enabled = COALESCE(exit_register_pulse_enabled, 1),
    exit_register_pulse_ms = COALESCE(exit_register_pulse_ms, pulse_duration_ms),
    
    new_access_channel = COALESCE(new_access_channel, entry_channel),
    new_access_action = COALESCE(new_access_action, 'open'),
    new_access_pulse_enabled = COALESCE(new_access_pulse_enabled, 1),
    new_access_pulse_ms = COALESCE(new_access_pulse_ms, pulse_duration_ms);

-- ============================================================
-- VALIDACIÓN
-- ============================================================

SELECT 'Tabla shelly_devices actualizada exitosamente.' as mensaje;
SELECT 'Se agregaron columnas para configurar canales y acciones por contexto.' as info;
SELECT 'Los valores legacy fueron migrados a los nuevos campos.' as migracion;
