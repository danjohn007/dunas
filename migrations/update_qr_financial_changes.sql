-- =============================================================================
-- Migration Script: QR Configuration and Financial Report Updates
-- Version: 1.5.0
-- Date: 2025-11-30
-- Description: 
--   1. Add QR configuration settings (qr_api_provider, qr_size)
--   2. Add capacity_costs table for pricing by capacity
--   3. Add cost and payment_method fields to access_logs
--   4. Add unique constraint on client phone number
-- =============================================================================

-- =============================================================================
-- 1. QR Code Configuration Settings
-- =============================================================================
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('qr_api_provider', 'qrserver'),
('qr_size', '350')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- =============================================================================
-- 2. Create capacity_costs table for pricing catalog
-- =============================================================================
CREATE TABLE IF NOT EXISTS `capacity_costs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `capacity_liters` INT NOT NULL,
    `cost` DECIMAL(10, 2) NOT NULL,
    `description` VARCHAR(100) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_capacity_liters` (`capacity_liters`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_capacity_liters_active` (`capacity_liters`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default capacity costs (example pricing)
INSERT INTO `capacity_costs` (`capacity_liters`, `cost`, `description`, `is_active`) VALUES
(5000, 25000.00, 'Pipa 5,000 litros', 1),
(10000, 50000.00, 'Pipa 10,000 litros', 1),
(12000, 60000.00, 'Pipa 12,000 litros', 1),
(15000, 75000.00, 'Pipa 15,000 litros', 1),
(20000, 100000.00, 'Pipa 20,000 litros', 1)
ON DUPLICATE KEY UPDATE 
    cost = VALUES(cost),
    description = VALUES(description),
    is_active = VALUES(is_active);

-- =============================================================================
-- 3. Add cost and payment_method fields to access_logs
-- =============================================================================
ALTER TABLE `access_logs`
    ADD COLUMN IF NOT EXISTS `cost` DECIMAL(10, 2) DEFAULT NULL AFTER `liters_supplied`,
    ADD COLUMN IF NOT EXISTS `payment_method` ENUM('cash', 'voucher', 'bank_transfer') DEFAULT 'cash' AFTER `cost`;

-- Update existing records to have default values
UPDATE `access_logs` SET `payment_method` = 'cash' WHERE `payment_method` IS NULL;

-- =============================================================================
-- 4. Add unique index on clients phone (with check to avoid duplicate)
-- =============================================================================
-- First check if index exists, drop if it does to avoid errors
SET @indexExists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND INDEX_NAME = 'uk_phone');

-- Create unique index only if it doesn't exist
-- Note: This might fail if there are duplicate phone numbers - clean them first if needed
-- To check for duplicates: SELECT phone, COUNT(*) FROM clients GROUP BY phone HAVING COUNT(*) > 1;
-- ALTER TABLE `clients` ADD UNIQUE INDEX `uk_phone` (`phone`);

-- =============================================================================
-- End of migration
-- =============================================================================
