-- ============================================================
-- Migration: add_aquapark_qr_col_gap
-- Adds the aquapark_qr_col_gap setting to control the spacing
-- (in millimetres) between each column when printing adhesive
-- labels (Tuk-Stik A11 and A14).  Default value: 0 mm.
-- Compatible with MySQL 5.7
-- ============================================================

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('aquapark_qr_col_gap', '0')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;
