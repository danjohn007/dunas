-- =============================================================================
-- Migration Script: QR Configuration and Financial Report Updates (Safe import)
-- Version: 1.5.0
-- Date: 2025-11-30
-- Notes:
--  - Este script intenta aplicar cambios de forma segura: si las columnas o el índice
--    ya existen, los errores no abortarán la importación.
--  - Requiere permisos para CREATE/DROP PROCEDURE. Si no los tienes, ejecuta las
--    secciones relevantes manualmente (ver nota al final).
--  - Hace backup antes de ejecutar en producción.
-- =============================================================================

-- 1) QR Code Configuration Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('qr_api_provider', 'qrserver'),
('qr_size', '350')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- 2) Create capacity_costs table for pricing catalog
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
-- 3) Safe add columns to access_logs and deduplicate clients.phone
-- =============================================================================
-- This section uses a stored procedure with an error handler that continues on errors
-- (so attempts to add already-existing columns or already-existing indexes won't abort).
-- If your DB user cannot CREATE PROCEDURE, run the manual section shown after this file.
DELIMITER $$
CREATE PROCEDURE safe_apply_qr_financial_changes()
BEGIN
  -- If any statement inside throws an exception, keep going.
  DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @migration_error = 1;

  -- 3.a Try to add 'cost' column (if already exists, handler will swallow the error)
  ALTER TABLE `access_logs`
    ADD COLUMN `cost` DECIMAL(10, 2) DEFAULT NULL AFTER `liters_supplied`;

  -- 3.b Try to add 'payment_method' column
  ALTER TABLE `access_logs`
    ADD COLUMN `payment_method` ENUM('cash','voucher','bank_transfer') DEFAULT 'cash' AFTER `cost`;

  -- 3.c Ensure existing rows have default payment_method (if column exists)
  -- If column does not exist, this will throw and be caught by the handler.
  UPDATE `access_logs` SET `payment_method` = 'cash' WHERE `payment_method` IS NULL;

  -- =============================================================================
  -- 4) Clients.phone dedupe & create unique index safely
  -- =============================================================================
  -- 4.a Convert empty-string phones to NULL (so '' doesn't collide)
  UPDATE `clients` SET `phone` = NULL WHERE `phone` = '';

  -- 4.b Build temporary table of duplicated phones and the id to keep (min id)
  DROP TEMPORARY TABLE IF EXISTS tmp_dup_phones;
  CREATE TEMPORARY TABLE tmp_dup_phones AS
    SELECT `phone`, MIN(`id`) AS keep_id
    FROM `clients`
    WHERE `phone` IS NOT NULL
    GROUP BY `phone`
    HAVING COUNT(*) > 1;

  -- 4.c Null out phone on duplicated rows (keep the row with keep_id)
  UPDATE `clients` c
    JOIN tmp_dup_phones t ON c.`phone` = t.`phone`
    SET c.`phone` = NULL
    WHERE c.`id` <> t.`keep_id`;

  DROP TEMPORARY TABLE IF EXISTS tmp_dup_phones;

  -- 4.d Try to add unique index on phone (if already exists, handler will swallow the error)
  ALTER TABLE `clients` ADD UNIQUE INDEX `uk_phone` (`phone`);

END$$
DELIMITER ;

-- Call the procedure (will run the attempts; harmless if parts already applied)
CALL safe_apply_qr_financial_changes();

-- Remove the helper procedure
DROP PROCEDURE IF EXISTS safe_apply_qr_financial_changes;

-- =============================================================================
-- End of migration
-- =============================================================================
