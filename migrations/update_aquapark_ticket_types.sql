-- =====================================================================
-- Actualización - Módulo PARQUE ACUÁTICO: Tipos de boleto y costos
-- MySQL 5.7 compatible
-- =====================================================================

-- 1. Agregar columna ticket_type a la tabla aquapark_codes
--    (Se usa PROCEDURE para hacerlo seguro en caso de que ya exista)
DROP PROCEDURE IF EXISTS add_ticket_type_column;

DELIMITER $$
CREATE PROCEDURE add_ticket_type_column()
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET @err = 1;
    ALTER TABLE `aquapark_codes`
        ADD COLUMN `ticket_type` ENUM('normal','nino','adulto_mayor','capacidades_diferentes')
            NOT NULL DEFAULT 'normal'
            COMMENT 'Tipo de boleto/pulsera'
            AFTER `valid_date`;
    ALTER TABLE `aquapark_codes`
        ADD INDEX `idx_ticket_type` (`ticket_type`);
END$$
DELIMITER ;

CALL add_ticket_type_column();
DROP PROCEDURE IF EXISTS add_ticket_type_column;

-- 2. Agregar configuraciones de precio para los nuevos tipos de boleto
INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES
    ('aquapark_ticket_price_nino',        '0.00'),
    ('aquapark_ticket_price_adulto_mayor','0.00'),
    ('aquapark_ticket_price_capacidades', '0.00')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
