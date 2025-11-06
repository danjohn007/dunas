-- Script de Actualización: Idempotencia de pulsos Shelly
-- Fecha: 2025-11-06
-- Descripción: Agrega tabla para evitar dobles pulsos y duplicados

USE dunas_access_control;

-- ============================================================
-- TABLA DE LOG DE PULSOS (IDEMPOTENCIA)
-- ============================================================

CREATE TABLE IF NOT EXISTS io_pulse_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  action ENUM('entry','exit') NOT NULL COMMENT 'Tipo de acción que generó el pulso',
  relay_id INT NOT NULL COMMENT 'ID del relay que se activó',
  correlation VARCHAR(64) NOT NULL COMMENT 'Identificador único de correlación (ej: "access:123:entry")',
  device_id VARCHAR(64) NULL COMMENT 'ID del dispositivo Shelly',
  duration_ms INT NULL COMMENT 'Duración del pulso en milisegundos',
  success TINYINT NOT NULL DEFAULT 1 COMMENT 'Si el pulso fue exitoso',
  error_message TEXT NULL COMMENT 'Mensaje de error si hubo fallo',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_correlation (correlation),
  INDEX idx_action_relay (action, relay_id),
  INDEX idx_created (created_at),
  UNIQUE KEY uniq_pulse (relay_id, correlation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log de pulsos enviados a Shelly para evitar duplicados';

-- ============================================================
-- VALIDACIÓN
-- ============================================================

SELECT 'Tabla io_pulse_log creada exitosamente.' as mensaje;
SELECT 'Ahora los pulsos serán registrados para evitar duplicados.' as info;
