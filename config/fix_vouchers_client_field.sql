-- Migration: Add client_id field to vouchers table
-- Date: 2026-02-04
-- Description: Adds client_id field to link vouchers with clients and make it required

-- Add client_id column to vouchers table
ALTER TABLE `vouchers` 
ADD COLUMN `client_id` int(11) DEFAULT NULL AFTER `created_by`,
ADD KEY `idx_client_id` (`client_id`),
ADD CONSTRAINT `fk_vouchers_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL;

-- Add comment to column
ALTER TABLE `vouchers` MODIFY COLUMN `client_id` int(11) DEFAULT NULL COMMENT 'ID del cliente asociado al vale';
