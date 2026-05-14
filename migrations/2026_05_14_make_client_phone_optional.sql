-- Hace opcional clients.phone (MySQL 5.7).
-- Convierte valores vacíos existentes a NULL y permite NULL en la columna.

UPDATE clients
SET phone = NULL
WHERE phone = '';

ALTER TABLE clients
MODIFY COLUMN phone VARCHAR(20) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
