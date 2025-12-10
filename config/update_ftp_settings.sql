-- Script para agregar configuraciones FTP al sistema
-- Este script agrega las rutas de origen y destino para el movimiento automático de imágenes FTP

-- Agregar configuración de ruta origen FTP
INSERT INTO settings (`setting_key`, `setting_value`, `created_at`, `updated_at`)
VALUES (
    'ftp_source_dir',
    '/home2/residencial/placas/',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `setting_value` = '/home2/residencial/placas/',
    `updated_at` = NOW();

-- Agregar configuración de ruta destino
INSERT INTO settings (`setting_key`, `setting_value`, `created_at`, `updated_at`)
VALUES (
    'ftp_destination_dir',
    '/home2/residencial/public_html/dunas/imagenes/',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `setting_value` = '/home2/residencial/public_html/dunas/imagenes/',
    `updated_at` = NOW();
