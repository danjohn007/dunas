-- Migration: Fix vouchers table constraint and prevent empty QR codes
-- Date: 2026-02-04
-- Description: Ensures vouchers table has correct structure and no empty QR codes

-- Step 1: Check if vouchers table exists
-- If it doesn't exist, create it with the correct structure
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serie` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `folio` int(11) NOT NULL,
  `qr_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT '0',
  `status` enum('active','used','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `used_at` datetime DEFAULT NULL,
  `used_by_access_log_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_qr_code` (`qr_code`),
  KEY `idx_serie_folio_status` (`serie`,`folio`,`status`),
  KEY `idx_status` (`status`),
  KEY `idx_used_by_access_log_id` (`used_by_access_log_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabla de vales generados para control de suministro de agua';

-- Step 2: Clean up any existing vouchers with empty or invalid QR codes
DELETE FROM `vouchers` WHERE `qr_code` = '' OR `qr_code` IS NULL OR LENGTH(`qr_code`) < 10;

-- Step 3: Add foreign key constraints if they don't exist
-- Check and add constraint for used_by_access_log_id
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'vouchers' 
    AND CONSTRAINT_NAME = 'fk_vouchers_used_by_access');

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `vouchers` ADD CONSTRAINT `fk_vouchers_used_by_access` FOREIGN KEY (`used_by_access_log_id`) REFERENCES `access_logs` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint fk_vouchers_used_by_access already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add constraint for created_by
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'vouchers' 
    AND CONSTRAINT_NAME = 'fk_vouchers_created_by');

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `vouchers` ADD CONSTRAINT `fk_vouchers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT',
    'SELECT "Constraint fk_vouchers_created_by already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add constraint for client_id
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'vouchers' 
    AND CONSTRAINT_NAME = 'fk_vouchers_client');

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `vouchers` ADD CONSTRAINT `fk_vouchers_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint fk_vouchers_client already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 4: Verify the final structure
SELECT 'Migration completed successfully' AS status;
DESCRIBE vouchers;
