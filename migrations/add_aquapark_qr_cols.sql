-- Adds settings to control the number of columns per page when printing
-- adhesive labels (Tuk-Stik A11 and A14).
--
-- aquapark_qr_cols_a11: columns per letter page for A11 stickers (default 5)
-- aquapark_qr_cols_a14: columns per letter page for A14 stickers (default 4)

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('aquapark_qr_cols_a11', '5')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('aquapark_qr_cols_a14', '4')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;
