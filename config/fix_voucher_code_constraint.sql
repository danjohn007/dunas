-- Migration: Fix voucher_code constraint and ensure proper schema
-- Date: 2026-02-04
-- Description: Handles production database schema variations and ensures proper setup

-- Step 1: Check current schema and identify issues
-- This script is safe to run multiple times

-- First, let's check if the table exists and what columns it has
SET @table_exists = (SELECT COUNT(*) FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vouchers');

-- Step 2: If voucher_code column exists (old schema), rename it to qr_code
SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'vouchers' 
    AND COLUMN_NAME = 'voucher_code');

SET @sql = IF(@column_exists > 0,
    'ALTER TABLE `vouchers` CHANGE COLUMN `voucher_code` `qr_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL',
    'SELECT "Column voucher_code does not exist, skipping rename" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 3: Ensure qr_code column exists and has correct properties
SET @qr_column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'vouchers' 
    AND COLUMN_NAME = 'qr_code');

-- If qr_code doesn't exist at all, add it
SET @sql = IF(@qr_column_exists = 0 AND @table_exists > 0,
    'ALTER TABLE `vouchers` ADD COLUMN `qr_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL AFTER `folio`',
    'SELECT "qr_code column already exists or table does not exist" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 4: Clean up any vouchers with empty or invalid QR codes
DELETE FROM `vouchers` WHERE `qr_code` = '' OR `qr_code` IS NULL OR LENGTH(`qr_code`) < 10;

-- Step 5: Ensure UNIQUE constraint exists on qr_code
-- Drop old constraint if it exists with wrong name
SET @old_constraint_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'vouchers' 
    AND CONSTRAINT_NAME = 'voucher_code'
    AND CONSTRAINT_TYPE = 'UNIQUE');

SET @sql = IF(@old_constraint_exists > 0,
    'ALTER TABLE `vouchers` DROP INDEX `voucher_code`',
    'SELECT "Old voucher_code constraint does not exist" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure correct UNIQUE constraint exists
SET @new_constraint_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'vouchers' 
    AND CONSTRAINT_NAME = 'idx_qr_code'
    AND CONSTRAINT_TYPE = 'UNIQUE');

SET @sql = IF(@new_constraint_exists = 0 AND @table_exists > 0,
    'ALTER TABLE `vouchers` ADD UNIQUE KEY `idx_qr_code` (`qr_code`)',
    'SELECT "idx_qr_code constraint already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 6: Add serie+folio unique constraint if not exists
SET @serie_folio_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'vouchers' 
    AND CONSTRAINT_NAME = 'idx_serie_folio_unique'
    AND CONSTRAINT_TYPE = 'UNIQUE');

SET @sql = IF(@serie_folio_exists = 0 AND @table_exists > 0,
    'ALTER TABLE `vouchers` ADD UNIQUE KEY `idx_serie_folio_unique` (`serie`, `folio`)',
    'SELECT "Serie+folio unique constraint already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 7: Verify final structure
SELECT 'Migration completed - verifying structure:' AS status;
DESCRIBE vouchers;

SELECT CONCAT('Total vouchers: ', COUNT(*)) AS stats FROM vouchers;
SELECT CONCAT('Empty QR codes: ', COUNT(*)) AS validation_check FROM vouchers WHERE qr_code = '' OR qr_code IS NULL;
SELECT CONCAT('Short QR codes: ', COUNT(*)) AS validation_check FROM vouchers WHERE LENGTH(qr_code) < 10;

-- Show constraints
SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE, TABLE_NAME 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vouchers';
