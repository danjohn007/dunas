-- ============================================================
-- Migration: add_aquapark_qr_rows
-- Adds aquapark_qr_rows_a11 and aquapark_qr_rows_a14 settings
-- to control the number of rows per page when printing adhesive
-- labels (Tuk-Stik A11 and A14).
-- Default values match the previously hardcoded values:
--   9 rows for A11, 6 rows for A14.
-- ============================================================

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('aquapark_qr_rows_a11', '9')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('aquapark_qr_rows_a14', '6')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;
