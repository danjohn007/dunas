-- Agrega configuración: tiempo en segundos para restablecer automáticamente
-- la pantalla de validación de Código QR tras mostrar el resultado.
-- Valor predeterminado: 3 segundos.
INSERT INTO settings (setting_key, setting_value)
VALUES ('aquapark_validate_reset_seconds', '3')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
