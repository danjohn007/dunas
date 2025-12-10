-- Script para agregar configuraciones de limpieza automática al sistema
-- Este script agrega las configuraciones para el tiempo de limpieza de registros automáticos

-- Agregar configuración de habilitación de limpieza automática
INSERT INTO settings (`setting_key`, `setting_value`, `created_at`, `updated_at`)
VALUES (
    'auto_cleanup_enabled',
    '1',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `updated_at` = NOW();

-- Agregar configuración de minutos para limpieza automática
INSERT INTO settings (`setting_key`, `setting_value`, `created_at`, `updated_at`)
VALUES (
    'auto_cleanup_minutes',
    '15',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `updated_at` = NOW();
