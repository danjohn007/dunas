-- Sistema de Control de Acceso con IoT
-- Script de Actualización: Tabla de Archivos de Placas Procesados
-- Fecha: 2025-11-11
-- Descripción: Crear tabla para rastrear archivos de imágenes de placas ya procesados

USE dunas_access_control;

-- ============================================================
-- TABLA DE ARCHIVOS DE PLACAS PROCESADOS
-- ============================================================

CREATE TABLE IF NOT EXISTS processed_plate_files (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  filename        VARCHAR(255) UNIQUE NOT NULL,
  processed_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_filename (filename),
  INDEX idx_processed_at (processed_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de archivos de imágenes de placas ya procesados para evitar duplicados';

-- ============================================================
-- VALIDACIÓN
-- ============================================================

SELECT 'Tabla processed_plate_files creada exitosamente' as mensaje;
SHOW COLUMNS FROM processed_plate_files;
