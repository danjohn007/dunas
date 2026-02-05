-- Migration: Update vouchers module for shorter QR codes and payment tracking
-- Date: 2026-02-05
-- Description: 
--   1. Add cost and payment_status fields to vouchers table
--   2. Add 'registered' status to vouchers (used when scanned for access)
--   3. Update existing vouchers to have shorter QR codes format

-- Add cost field
ALTER TABLE `vouchers` 
ADD COLUMN `cost` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Costo del vale' 
AFTER `capacity`;

-- Add payment_status field
ALTER TABLE `vouchers` 
ADD COLUMN `payment_status` ENUM('paid', 'pending') NOT NULL DEFAULT 'pending' COMMENT 'Estado de pago del vale'
AFTER `cost`;

-- Update status enum to include 'registered'
ALTER TABLE `vouchers` 
MODIFY COLUMN `status` ENUM('active','used','cancelled','registered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active';

-- Add index for payment_status for financial reports
ALTER TABLE `vouchers` 
ADD INDEX `idx_payment_status` (`payment_status`);

-- Add index for cost calculations
ALTER TABLE `vouchers` 
ADD INDEX `idx_cost_status` (`cost`, `status`, `payment_status`);

-- Note: Existing QR codes will remain as-is for backward compatibility
-- New vouchers will use the shorter format (SERIE-FOLIO) automatically via code changes
