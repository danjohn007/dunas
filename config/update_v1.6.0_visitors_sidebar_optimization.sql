-- =====================================================
-- ACTUALIZACIÓN v1.6.0 - Visitantes, Sidebar y Optimización
-- =====================================================
-- Este script agrega:
-- 1. Tabla de visitantes para registro público
-- 2. Configuración de autoborrado de registros
-- =====================================================

-- --------------------------------------------------------
-- 1. TABLA DE VISITANTES
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plate_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Foto de identificación',
  `plate_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Foto de placas',
  `badge_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Foto de gafete (opcional)',
  `entry_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `exit_datetime` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('in','out','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_entry_datetime` (`entry_datetime`),
  KEY `idx_status` (`status`),
  KEY `idx_plate_number` (`plate_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de visitantes';

-- --------------------------------------------------------
-- 2. CONFIGURACIONES DE OPTIMIZACIÓN DEL SISTEMA
-- --------------------------------------------------------

-- Agregar configuración para borrado automático de registros
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES 
('auto_delete_enabled', '0'),
('auto_delete_days', '0')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- --------------------------------------------------------
-- 3. AGREGAR CAMPO DE MÉTODO DE PAGO A ACCESS_LOGS
-- --------------------------------------------------------

-- Verificar si la columna ya existe antes de agregarla
SET @col_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'access_logs' 
    AND COLUMN_NAME = 'payment_method');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `access_logs` ADD COLUMN `payment_method` enum(''cash'',''voucher'',''bank_transfer'') COLLATE utf8mb4_unicode_ci DEFAULT ''cash'' AFTER `status`',
    'SELECT "Column payment_method already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- FIN DE LA ACTUALIZACIÓN
-- =====================================================
