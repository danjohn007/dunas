-- Multi-Device Shelly Cloud Configuration
-- Migration: 2025-01-14
-- Description: Add support for multiple Shelly devices with extensible actions

-- Dispositivos Shelly (ilimitados)
CREATE TABLE IF NOT EXISTS shelly_devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL DEFAULT 'Abrir/Cerrar',
  auth_token VARCHAR(255) NOT NULL,
  device_id VARCHAR(64) NOT NULL,
  server_host VARCHAR(128) NOT NULL,
  active_channel TINYINT NOT NULL DEFAULT 0,     -- 0..3 por defecto
  channel_count TINYINT NOT NULL DEFAULT 4,      -- algunos equipos tienen 2, configurable
  is_enabled TINYINT NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_is_enabled (is_enabled),
  INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Acciones Shelly (extensible)
CREATE TABLE IF NOT EXISTS shelly_actions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  device_id INT NOT NULL,
  code VARCHAR(50) NOT NULL,     -- ej: 'abrir_cerrar', 'vacio', 'abrir_puerta'
  label VARCHAR(100) NOT NULL,   -- ej: 'Abrir/Cerrar', 'Vacío', 'AbrirPuerta'
  action_kind ENUM('toggle','on','off','pulse') NOT NULL DEFAULT 'toggle',
  channel TINYINT NOT NULL DEFAULT 0,    -- por si la acción requiere un canal distinto
  duration_ms INT NULL,                  -- usado en 'pulse'
  is_default TINYINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_shelly_actions_device FOREIGN KEY (device_id) REFERENCES shelly_devices(id) ON DELETE CASCADE,
  INDEX idx_device_code (device_id, code),
  INDEX idx_is_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migración de configuración existente (si existe en settings table)
-- Este script intenta migrar la configuración existente de Shelly desde la tabla settings
INSERT INTO shelly_devices (name, auth_token, device_id, server_host, active_channel, channel_count, is_enabled, sort_order)
SELECT 
  'Abrir/Cerrar',
  (SELECT setting_value FROM settings WHERE setting_key = 'shelly_auth_token' LIMIT 1),
  (SELECT setting_value FROM settings WHERE setting_key = 'shelly_device_id' LIMIT 1),
  (SELECT setting_value FROM settings WHERE setting_key = 'shelly_server' LIMIT 1),
  0,  -- Canal por defecto
  4,  -- 4 canales por defecto
  1,  -- Habilitado
  0   -- Primer dispositivo
FROM settings 
WHERE setting_key = 'shelly_auth_token'
  AND NOT EXISTS (SELECT 1 FROM shelly_devices)
LIMIT 1;

-- Crear acción por defecto para el dispositivo migrado (si se insertó)
INSERT INTO shelly_actions (device_id, code, label, action_kind, channel, duration_ms, is_default)
SELECT 
  id,
  'abrir_cerrar',
  'Abrir/Cerrar',
  'toggle',
  0,
  NULL,
  1
FROM shelly_devices
WHERE id = LAST_INSERT_ID()
  AND NOT EXISTS (SELECT 1 FROM shelly_actions WHERE device_id = LAST_INSERT_ID());
