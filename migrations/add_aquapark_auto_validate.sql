-- Agrega configuración: validación automática en la pantalla de Validar Código QR.
-- Cuando está activa, el formulario se envía automáticamente en cuanto el código
-- ingresado manualmente coincide con el formato completo (AQP-XXXXXXXX-XXXXXX
-- o TKT-XXXXXXXX-XXXXXXXX). Valor predeterminado: 1 (activa).
INSERT INTO settings (setting_key, setting_value)
VALUES ('aquapark_validate_auto', '1')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
