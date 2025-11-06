-- Sistema de Control de Acceso con IoT
-- Script de Actualización: Dispositivos HikVision y Canales Shelly
-- Fecha: 2025-11-06
-- Versión: 1.4.0

USE dunas_access_control;

-- ============================================================
-- TABLA DE DISPOSITIVOS HIKVISION
-- ============================================================

-- Crear tabla para dispositivos HikVision (cámaras LPR y lectores de código de barras)
CREATE TABLE IF NOT EXISTS hikvision_devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL DEFAULT 'Cámara HikVision',
  device_type ENUM('camera_lpr', 'barcode_reader') NOT NULL DEFAULT 'camera_lpr',
  api_url VARCHAR(255) NOT NULL,
  username VARCHAR(100) NULL,
  password VARCHAR(255) NULL,
  verify_ssl TINYINT NOT NULL DEFAULT 0,
  area VARCHAR(100) NULL COMMENT 'Ubicación física del dispositivo',
  is_enabled TINYINT NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_device_type (device_type),
  INDEX idx_is_enabled (is_enabled),
  INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Dispositivos HikVision para lectura de placas y códigos de barras';

-- ============================================================
-- MODIFICACIONES A LA TABLA shelly_devices
-- ============================================================

-- Agregar campos para canales de entrada y salida
ALTER TABLE shelly_devices
ADD COLUMN entry_channel TINYINT NOT NULL DEFAULT 0 COMMENT 'Canal para apertura (entrada)' AFTER active_channel,
ADD COLUMN exit_channel TINYINT NOT NULL DEFAULT 1 COMMENT 'Canal para cierre (salida)' AFTER entry_channel,
ADD COLUMN pulse_duration_ms INT NOT NULL DEFAULT 5000 COMMENT 'Duración del pulso en milisegundos' AFTER exit_channel;

-- Migrar datos existentes: usar active_channel como entry_channel
-- exit_channel se calcula como (active_channel + 1) % 4 para distribución balanceada
UPDATE shelly_devices
SET entry_channel = active_channel,
    exit_channel = (active_channel + 1) % 4
WHERE entry_channel = 0 AND exit_channel = 1;

-- ============================================================
-- MIGRACIÓN DE CONFIGURACIÓN EXISTENTE DE HIKVISION
-- ============================================================

-- Migrar configuración existente de HikVision desde la tabla settings
INSERT INTO hikvision_devices (name, device_type, api_url, username, password, verify_ssl, area, is_enabled, sort_order)
SELECT 
  'Cámara HikVision Principal',
  'camera_lpr',
  COALESCE((SELECT setting_value FROM settings WHERE setting_key = 'hikvision_api_url' LIMIT 1), ''),
  COALESCE((SELECT setting_value FROM settings WHERE setting_key = 'hikvision_username' LIMIT 1), ''),
  COALESCE((SELECT setting_value FROM settings WHERE setting_key = 'hikvision_password' LIMIT 1), ''),
  CASE 
    WHEN (SELECT setting_value FROM settings WHERE setting_key = 'hikvision_verify_ssl' LIMIT 1) = 'true' THEN 1 
    ELSE 0 
  END,
  'Entrada Principal',
  1,
  0
FROM settings 
WHERE setting_key = 'hikvision_api_url'
  AND (SELECT setting_value FROM settings WHERE setting_key = 'hikvision_api_url' LIMIT 1) IS NOT NULL
  AND (SELECT setting_value FROM settings WHERE setting_key = 'hikvision_api_url' LIMIT 1) != ''
  AND NOT EXISTS (SELECT 1 FROM hikvision_devices)
LIMIT 1;

-- ============================================================
-- VALIDACIÓN DE INTEGRIDAD
-- ============================================================

-- Verificar dispositivos HikVision migrados
SELECT 'Dispositivos HikVision configurados:' as mensaje, COUNT(*) as cantidad
FROM hikvision_devices;

-- Verificar dispositivos Shelly con nuevos campos
SELECT 'Dispositivos Shelly actualizados:' as mensaje, COUNT(*) as cantidad
FROM shelly_devices
WHERE entry_channel >= 0 AND exit_channel >= 0;

-- ============================================================
-- MENSAJES FINALES
-- ============================================================

SELECT 'Actualización de base de datos completada exitosamente.' as mensaje;
SELECT 'Versión: 1.4.0' as version;
SELECT 'Cambios aplicados:' as titulo;
SELECT '1. Tabla hikvision_devices creada para gestión de dispositivos HikVision' as cambio;
SELECT '2. Dispositivos Shelly ahora soportan canales separados para entrada y salida' as cambio;
SELECT '3. Agregado soporte para lectores de código de barras HikVision' as cambio;
SELECT '4. Duración de pulso configurable para dispositivos Shelly' as cambio;
SELECT '5. Configuración existente de HikVision migrada a nueva tabla' as cambio;
SELECT NOW() as fecha_actualizacion;
