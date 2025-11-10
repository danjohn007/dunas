-- Migration: Agregar configuración FTP para sincronización de imágenes ANPR
-- Fecha: 2025-11-10
-- Descripción: Agrega los parámetros de configuración necesarios para la sincronización
--              de imágenes desde un servidor FTP al sistema local.

-- Nota: Ajuste los valores según su configuración específica antes de ejecutar

-- Configuración del servidor FTP
-- ftp_host: Dirección del servidor FTP (ejemplo: ftp.example.com o 192.168.1.100)
INSERT INTO settings (setting_key, setting_value) VALUES 
('ftp_host', '')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- ftp_port: Puerto del servidor FTP (predeterminado: 21)
INSERT INTO settings (setting_key, setting_value) VALUES 
('ftp_port', '21')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- ftp_user: Usuario de autenticación FTP
INSERT INTO settings (setting_key, setting_value) VALUES 
('ftp_user', '')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- ftp_pass: Contraseña de autenticación FTP
INSERT INTO settings (setting_key, setting_value) VALUES 
('ftp_pass', '')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- ftp_images_path: Ruta en el servidor FTP donde están las imágenes (ejemplo: /capturas/anpr)
INSERT INTO settings (setting_key, setting_value) VALUES 
('ftp_images_path', '/')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- ftp_image_pattern: Patrón regex para filtrar archivos de imagen (ejemplo: _PLACA_.*VEHICLE\.jpg$)
INSERT INTO settings (setting_key, setting_value) VALUES 
('ftp_image_pattern', '_PLACA_.*VEHICLE\.jpg$')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- Verificar la inserción
SELECT setting_key, setting_value 
FROM settings 
WHERE setting_key LIKE 'ftp_%';
