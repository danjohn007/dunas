-- Sistema de Control de Acceso con IoT
-- Script de Actualización de Base de Datos
-- Fecha: 2024-10-29
-- Versión: 1.2.0

USE dunas_access_control;

-- Agregar roles 'viewer' y 'client' a la tabla users si no existen
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'supervisor', 'operator', 'viewer', 'client') NOT NULL DEFAULT 'operator';

-- Mensaje final
SELECT 'Actualización de base de datos completada exitosamente.' as mensaje;
SELECT 'Versión: 1.2.0 - Agregado soporte para roles viewer y client' as version;
SELECT NOW() as fecha_actualizacion;
