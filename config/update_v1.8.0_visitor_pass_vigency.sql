-- =====================================================
-- ACTUALIZACIÓN v1.8.0 - Pase de Visita con Vigencia
-- =====================================================
-- Este script agrega:
-- 1. Nuevos campos para pase de visita con vigencia
-- 2. Campo de identificación para visitantes
-- 3. Campo de tipo de visita
-- =====================================================

-- --------------------------------------------------------
-- 1. AGREGAR CAMPO IDENTIFICATION A VISITORS
-- --------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'visitors' 
    AND COLUMN_NAME = 'identification');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `visitors` ADD COLUMN `identification` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `phone`',
    'SELECT "Column identification already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 2. AGREGAR CAMPO VISIT_TYPE A VISITORS
-- --------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'visitors' 
    AND COLUMN_NAME = 'visit_type');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `visitors` ADD COLUMN `visit_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT \'personal\' AFTER `identification`',
    'SELECT "Column visit_type already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 3. AGREGAR CAMPO VALID_FROM A VISITORS
-- --------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'visitors' 
    AND COLUMN_NAME = 'valid_from');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `visitors` ADD COLUMN `valid_from` datetime DEFAULT NULL AFTER `visit_type`',
    'SELECT "Column valid_from already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 4. AGREGAR CAMPO VALID_UNTIL A VISITORS
-- --------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'visitors' 
    AND COLUMN_NAME = 'valid_until');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `visitors` ADD COLUMN `valid_until` datetime DEFAULT NULL AFTER `valid_from`',
    'SELECT "Column valid_until already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 5. MODIFICAR STATUS PARA INCLUIR 'pending'
-- --------------------------------------------------------

-- Update the status ENUM to include 'pending' for visitor passes
-- Note: This requires modifying the ENUM type

SET @sql = 'ALTER TABLE `visitors` MODIFY COLUMN `status` ENUM(''in'', ''out'', ''cancelled'', ''pending'') NOT NULL DEFAULT ''in''';
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 6. AGREGAR ÍNDICES PARA VALIDACIÓN DE PASES
-- --------------------------------------------------------

-- Index for valid_from and valid_until for faster validity checks
SET @idx_exists = (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'visitors' AND index_name = 'idx_validity'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_validity ON visitors(valid_from, valid_until)',
    'SELECT "idx_validity already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- FIN DE LA ACTUALIZACIÓN
-- =====================================================
