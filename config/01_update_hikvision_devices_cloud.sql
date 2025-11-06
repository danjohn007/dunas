-- Sistema de Control de Acceso con IoT
-- Script de Actualización: HikVision Cloud API (Hik-Partner)
-- Fecha: 2025-11-06
-- Descripción: Agregar soporte para API Cloud de Hik-Partner

USE dunas_access_control;

-- ============================================================
-- ACTUALIZACIÓN DE TABLA hikvision_devices PARA CLOUD API
-- ============================================================

-- Agregar columnas para autenticación y gestión de tokens cloud
ALTER TABLE hikvision_devices
  ADD COLUMN api_key VARCHAR(64) NULL AFTER name,
  ADD COLUMN api_secret VARCHAR(64) NULL AFTER api_key,
  ADD COLUMN token_endpoint VARCHAR(255) NULL AFTER api_secret,
  ADD COLUMN area_domain VARCHAR(255) NULL AFTER token_endpoint,
  ADD COLUMN access_token VARCHAR(512) NULL AFTER area_domain,
  ADD COLUMN token_expires_at DATETIME NULL AFTER access_token,
  ADD COLUMN device_index_code VARCHAR(64) NULL AFTER token_expires_at,
  ADD COLUMN area_label VARCHAR(100) NULL AFTER verify_ssl,
  MODIFY COLUMN device_type ENUM('camera_lpr','barcode_reader') NOT NULL DEFAULT 'camera_lpr';

-- Actualizar índices
CREATE INDEX idx_token_expires_at ON hikvision_devices(token_expires_at);
CREATE INDEX idx_device_index_code ON hikvision_devices(device_index_code);

-- ============================================================
-- VALIDACIÓN
-- ============================================================

-- Verificar estructura actualizada
SELECT 'Columnas agregadas a hikvision_devices para API Cloud' as mensaje;
SHOW COLUMNS FROM hikvision_devices;
