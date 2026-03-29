-- ============================================================
-- Migration: add_adhesive_a14_print_type
-- Documents the new 'adhesiva_a14' value for the
-- aquapark_qr_print_type setting (Tuk-Stik A14, 19 × 50 mm,
-- 4 columnas × 6 renglones por hoja carta).
-- Also corrects the A11 layout to 5 columnas × 9 renglones.
--
-- The settings table stores free-text values, so no schema
-- change is required. This script ensures the setting row
-- exists with the default 'pulsera' value for new installs.
-- Compatible with MySQL 5.7
-- ============================================================

-- Ensure the setting key exists (default: pulsera).
-- On existing installations the current value is preserved.
INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('aquapark_qr_print_type', 'pulsera')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;

-- Valid values for aquapark_qr_print_type:
--   'pulsera'      → Etiqueta de pulsera (11 por hoja carta)
--   'adhesiva'     → Tuk-Stik A11 (38 × 13 mm) — 5 col × 9 ren
--   'adhesiva_a14' → Tuk-Stik A14 (19 × 50 mm) — 4 col × 6 ren
