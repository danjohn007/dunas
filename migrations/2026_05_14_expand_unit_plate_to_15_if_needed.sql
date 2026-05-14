-- Aumenta units.plate_number a 15 caracteres solo cuando la columna es menor a 15.
-- Compatible con MySQL 5.7.

SET @needs_alter := (
    SELECT CASE
        WHEN CHARACTER_MAXIMUM_LENGTH < 15 THEN 1
        ELSE 0
    END
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'units'
      AND COLUMN_NAME = 'plate_number'
    LIMIT 1
);

SET @sql := IF(
    @needs_alter = 1,
    'ALTER TABLE units MODIFY COLUMN plate_number VARCHAR(15) NOT NULL',
    'SELECT "units.plate_number ya permite 15 o más caracteres" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
