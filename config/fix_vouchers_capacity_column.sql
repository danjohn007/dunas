-- Verification and Fix Script for Vouchers Table
-- Date: 2026-02-04
-- Purpose: Verify and fix the vouchers table schema if capacity column is missing

-- Check if vouchers table exists and show its structure
DESCRIBE vouchers;

-- If the table doesn't have the capacity column, add it
-- (This will fail if the column already exists, which is safe)
ALTER TABLE `vouchers` 
ADD COLUMN `capacity` int(11) NOT NULL DEFAULT '0' AFTER `qr_code`;

-- Verify the fix
DESCRIBE vouchers;

-- Show sample data if any exists
SELECT COUNT(*) as total_vouchers FROM vouchers;
