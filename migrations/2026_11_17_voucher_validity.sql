CREATE TABLE IF NOT EXISTS `voucher_validity_settings` (
    `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    `validity_minutes` INT UNSIGNED NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

SET @valid_until_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'vouchers'
      AND COLUMN_NAME = 'valid_until'
);

SET @sql = IF(
    @valid_until_exists = 0,
    'ALTER TABLE `vouchers` ADD COLUMN `valid_until` DATETIME NULL DEFAULT NULL AFTER `used_at`',
    'SELECT "Column valid_until already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
