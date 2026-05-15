-- Hace opcionales driver_id, plate_number, brand y model en la tabla units (MySQL 5.7).
-- Convierte valores vacíos/cero existentes a NULL y permite NULL en las columnas.

UPDATE units SET driver_id = NULL WHERE driver_id = 0;

UPDATE units SET plate_number = NULL WHERE plate_number = '';

UPDATE units SET brand = NULL WHERE brand = '';

UPDATE units SET model = NULL WHERE model = '';

ALTER TABLE units
  MODIFY COLUMN driver_id  INT(11)       NULL DEFAULT NULL,
  MODIFY COLUMN plate_number VARCHAR(20) NULL DEFAULT NULL,
  MODIFY COLUMN brand      VARCHAR(100)  NULL DEFAULT NULL,
  MODIFY COLUMN model      VARCHAR(100)  NULL DEFAULT NULL;
