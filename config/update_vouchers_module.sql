-- Migration: Add vouchers table for voucher generation module
-- Date: 2026-02-04
-- Description: Creates table for storing generated vouchers with QR codes

-- Create vouchers table
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_qr_code` (`qr_code`),
  KEY `idx_serie_folio_status` (`serie`,`folio`,`status`),
  KEY `idx_status` (`status`),
  KEY `idx_used_by_access_log_id` (`used_by_access_log_id`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_vouchers_used_by_access` FOREIGN KEY (`used_by_access_log_id`) REFERENCES `access_logs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vouchers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to table
ALTER TABLE `vouchers` COMMENT='Tabla de vales generados para control de suministro de agua';
