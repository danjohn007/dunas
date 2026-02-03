-- Migration: Add vouchers table for voucher generation module
-- Date: 2026-02-03

-- Table for storing generated vouchers
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serie` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `folio` int(11) NOT NULL,
  `voucher_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity_liters` int(11) NOT NULL,
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','used','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `used_at` datetime DEFAULT NULL,
  `used_by_access_log_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucher_code` (`voucher_code`),
  UNIQUE KEY `serie_folio` (`serie`, `folio`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`),
  KEY `used_by_access_log_id` (`used_by_access_log_id`),
  CONSTRAINT `fk_vouchers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vouchers_access_log` FOREIGN KEY (`used_by_access_log_id`) REFERENCES `access_logs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for QR code lookups (for HikVision device scanning)
CREATE INDEX `idx_qr_code` ON `vouchers` (`qr_code`);

-- Add index for serie+folio searches
CREATE INDEX `idx_serie_folio_status` ON `vouchers` (`serie`, `folio`, `status`);
