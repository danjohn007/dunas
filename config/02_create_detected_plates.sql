-- Sistema de Control de Acceso con IoT
-- Script de Actualización: Tabla de Placas Detectadas
-- Fecha: 2025-11-06
-- Descripción: Crear tabla para registrar detecciones de placas (solo texto + trazabilidad)

USE dunas_access_control;

-- ============================================================
-- TABLA DE DETECCIONES DE PLACAS
-- ============================================================

CREATE TABLE IF NOT EXISTS detected_plates (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  plate_text      VARCHAR(20) NOT NULL,
  confidence      DECIMAL(5,2) NULL,
  captured_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  device_id       INT NULL,
  unit_id         INT NULL,
  is_match        TINYINT(1) DEFAULT 0,
  payload_json    JSON NULL,
  status          ENUM('new','processed') DEFAULT 'new',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_plate_text (plate_text),
  INDEX idx_captured_at (captured_at DESC),
  INDEX idx_device_id (device_id),
  INDEX idx_unit_id (unit_id),
  INDEX idx_status (status),
  FOREIGN KEY (device_id) REFERENCES hikvision_devices(id) ON DELETE SET NULL,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de placas detectadas por cámaras LPR';

-- ============================================================
-- VALIDACIÓN
-- ============================================================

SELECT 'Tabla detected_plates creada exitosamente' as mensaje;
SHOW COLUMNS FROM detected_plates;
