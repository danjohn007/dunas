-- Guarda cuándo se relacionó cada vale con un cliente.
SET @related_at_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'vouchers'
      AND COLUMN_NAME = 'related_at'
);

SET @sql = IF(
    @related_at_exists = 0,
    'ALTER TABLE `vouchers` ADD COLUMN `related_at` DATETIME NULL DEFAULT NULL AFTER `client_id`',
    'SELECT "Column related_at already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Los vales estándar se relacionan al momento de su creación.
UPDATE `vouchers`
SET `related_at` = `created_at`
WHERE `related_at` IS NULL
  AND `client_id` IS NOT NULL
  AND `voucher_type` = 'standard';
