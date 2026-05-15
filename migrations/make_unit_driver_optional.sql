-- Migración: Hacer unit_id y driver_id opcionales en access_logs
-- MySQL 5.7 compatible
-- Permite registrar entradas sin seleccionar Unidad (Pipa) ni Chofer

ALTER TABLE access_logs
    MODIFY COLUMN unit_id INT(11) NULL DEFAULT NULL,
    MODIFY COLUMN driver_id INT(11) NULL DEFAULT NULL;
