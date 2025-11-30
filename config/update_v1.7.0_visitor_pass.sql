-- =====================================================
-- ACTUALIZACIÓN v1.7.0 - Pase de Visita
-- =====================================================
-- Este script agrega:
-- 1. Campo pass_code para código de pase de visita
-- =====================================================

-- --------------------------------------------------------
-- 1. AGREGAR CAMPO PASS_CODE A VISITORS
-- --------------------------------------------------------

-- Verificar si la columna ya existe antes de agregarla
SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'visitors' 
    AND COLUMN_NAME = 'pass_code');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `visitors` ADD COLUMN `pass_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `id`',
    'SELECT "Column pass_code already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar índice único para pass_code
SET @idx_exists = (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'visitors' AND index_name = 'idx_pass_code'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE UNIQUE INDEX idx_pass_code ON visitors(pass_code)',
    'SELECT "idx_pass_code already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Actualizar registros existentes sin pass_code
UPDATE visitors 
SET pass_code = CONCAT('VIS-', DATE_FORMAT(entry_datetime, '%Y%m%d'), '-', UPPER(SUBSTRING(MD5(CONCAT(id, entry_datetime)), 1, 8)))
WHERE pass_code IS NULL;

-- =====================================================
-- FIN DE LA ACTUALIZACIÓN
-- =====================================================
