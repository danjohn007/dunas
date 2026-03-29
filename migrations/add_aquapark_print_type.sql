-- ============================================================
-- Migration: add_aquapark_print_type
-- Adds the aquapark_qr_print_type setting to control the
-- QR code print layout: 'pulsera' (wristband) or 'adhesiva'
-- (Tuk-Stik A11 adhesive sticker, 38 x 13 mm).
-- Compatible with MySQL 5.7
-- ============================================================

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('aquapark_qr_print_type', 'pulsera')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
