-- ============================================================
-- Migration: add_aquapark_qr_row_gap
-- Adds the aquapark_qr_row_gap setting to control the spacing
-- (in millimetres) between each row when printing adhesive
-- labels (Tuk-Stik A11 and A14).  Default value: 1 mm.
-- Compatible with MySQL 5.7
-- ============================================================

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('aquapark_qr_row_gap', '1')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;
