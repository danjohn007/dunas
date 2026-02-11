-- =====================================================================
-- MIGRATION: Mejoras de Estado de Pago y Control de PINs
-- Fecha: 2026-02-11
-- Descripción:
--   1. Agregar campo de seguimiento de uso de PIN en access_logs
--   2. Crear trigger para actualizar payment_status de vouchers cuando se registra pago
--   3. Crear vista para calcular estado de pago de transacciones basado en vouchers
-- =====================================================================

-- =====================================================================
-- 1. AGREGAR CAMPO PARA RASTREAR USO DE PIN EN ACCESS_LOGS
-- =====================================================================
-- Este campo permite validar que un PIN solo se use una vez
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'access_logs'
      AND COLUMN_NAME = 'pin_used'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `access_logs` ADD COLUMN `pin_used` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''Indica si el PIN ya fue utilizado (1=usado, 0=no usado)'' AFTER `barcode`',
    'SELECT "Column pin_used already exists in access_logs" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar índice para búsqueda rápida de PINs usados
SET @index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'access_logs'
      AND INDEX_NAME = 'idx_ticket_code_pin_used'
);

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE `access_logs` ADD INDEX `idx_ticket_code_pin_used` (`ticket_code`, `pin_used`)',
    'SELECT "Index idx_ticket_code_pin_used already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================================
-- 2. CREAR STORED PROCEDURE PARA ACTUALIZAR PAYMENT_STATUS DE VOUCHERS
-- =====================================================================
-- Este procedimiento calcula y actualiza el estado de pago de los vouchers
-- de un cliente basándose en los pagos registrados vs el costo total

DROP PROCEDURE IF EXISTS `update_voucher_payment_status`;

DELIMITER $$

CREATE PROCEDURE `update_voucher_payment_status`(
    IN p_client_id INT,
    IN p_date_from DATE,
    IN p_date_to DATE
)
BEGIN
    DECLARE v_total_cost DECIMAL(10,2);
    DECLARE v_total_paid DECIMAL(10,2);
    DECLARE v_total_pending DECIMAL(10,2);
    
    -- Calcular el costo total de vales del cliente en el período
    SELECT COALESCE(SUM(cost), 0) INTO v_total_cost
    FROM vouchers
    WHERE client_id = p_client_id
      AND cost IS NOT NULL
      AND DATE(created_at) >= p_date_from
      AND DATE(created_at) <= p_date_to;
    
    -- Calcular el total pagado por el cliente en el período
    SELECT COALESCE(SUM(amount), 0) INTO v_total_paid
    FROM voucher_payments
    WHERE client_id = p_client_id
      AND payment_date >= p_date_from
      AND payment_date <= p_date_to;
    
    -- Calcular el pendiente
    SET v_total_pending = v_total_cost - v_total_paid;
    
    -- Si el pago cubre todo o más, marcar todos los vales del período como pagados
    IF v_total_pending <= 0 THEN
        UPDATE vouchers
        SET payment_status = 'paid'
        WHERE client_id = p_client_id
          AND cost IS NOT NULL
          AND DATE(created_at) >= p_date_from
          AND DATE(created_at) <= p_date_to
          AND payment_status = 'pending';
    ELSE
        -- Si aún hay pendiente, asegurarse de que sigan como pending
        -- (esto mantiene la consistencia en caso de reversiones de pago)
        UPDATE vouchers
        SET payment_status = 'pending'
        WHERE client_id = p_client_id
          AND cost IS NOT NULL
          AND DATE(created_at) >= p_date_from
          AND DATE(created_at) <= p_date_to
          AND payment_status = 'paid';
    END IF;
    
END$$

DELIMITER ;

-- =====================================================================
-- 3. CREAR TRIGGER PARA ACTUALIZAR PAYMENT_STATUS AUTOMÁTICAMENTE
-- =====================================================================
-- Este trigger se ejecuta después de insertar un pago y actualiza
-- el estado de los vouchers del cliente

DROP TRIGGER IF EXISTS `after_voucher_payment_insert`;

DELIMITER $$

CREATE TRIGGER `after_voucher_payment_insert`
AFTER INSERT ON `voucher_payments`
FOR EACH ROW
BEGIN
    DECLARE v_min_date DATE;
    DECLARE v_max_date DATE;
    
    -- Obtener rango de fechas de vouchers del cliente
    SELECT MIN(DATE(created_at)), MAX(DATE(created_at))
    INTO v_min_date, v_max_date
    FROM vouchers
    WHERE client_id = NEW.client_id
      AND cost IS NOT NULL;
    
    -- Si hay vouchers, actualizar su estado de pago
    IF v_min_date IS NOT NULL THEN
        CALL update_voucher_payment_status(
            NEW.client_id,
            v_min_date,
            v_max_date
        );
    END IF;
END$$

DELIMITER ;

-- También crear trigger para actualizaciones de pagos
DROP TRIGGER IF EXISTS `after_voucher_payment_update`;

DELIMITER $$

CREATE TRIGGER `after_voucher_payment_update`
AFTER UPDATE ON `voucher_payments`
FOR EACH ROW
BEGIN
    DECLARE v_min_date DATE;
    DECLARE v_max_date DATE;
    
    -- Obtener rango de fechas de vouchers del cliente
    SELECT MIN(DATE(created_at)), MAX(DATE(created_at))
    INTO v_min_date, v_max_date
    FROM vouchers
    WHERE client_id = NEW.client_id
      AND cost IS NOT NULL;
    
    -- Si hay vouchers, actualizar su estado de pago
    IF v_min_date IS NOT NULL THEN
        CALL update_voucher_payment_status(
            NEW.client_id,
            v_min_date,
            v_max_date
        );
    END IF;
END$$

DELIMITER ;

-- Y trigger para eliminaciones de pagos
DROP TRIGGER IF EXISTS `after_voucher_payment_delete`;

DELIMITER $$

CREATE TRIGGER `after_voucher_payment_delete`
AFTER DELETE ON `voucher_payments`
FOR EACH ROW
BEGIN
    DECLARE v_min_date DATE;
    DECLARE v_max_date DATE;
    
    -- Obtener rango de fechas de vouchers del cliente
    SELECT MIN(DATE(created_at)), MAX(DATE(created_at))
    INTO v_min_date, v_max_date
    FROM vouchers
    WHERE client_id = OLD.client_id
      AND cost IS NOT NULL;
    
    -- Si hay vouchers, actualizar su estado de pago
    IF v_min_date IS NOT NULL THEN
        CALL update_voucher_payment_status(
            OLD.client_id,
            v_min_date,
            v_max_date
        );
    END IF;
END$$

DELIMITER ;

-- =====================================================================
-- 4. CREAR VISTA PARA ESTADO DE PAGO DE TRANSACCIONES
-- =====================================================================
-- Esta vista muestra las transacciones con su estado de pago real
-- basado en el estado de pago de los vouchers asociados

DROP VIEW IF EXISTS `v_transactions_with_payment_status`;

CREATE VIEW `v_transactions_with_payment_status` AS
SELECT 
    t.*,
    c.business_name as client_name,
    al.ticket_code,
    v.qr_code as voucher_code,
    v.serie as voucher_serie,
    v.folio as voucher_folio,
    -- Si el método de pago es voucher, usar el payment_status del voucher
    -- Si no, usar el payment_status de la transacción
    CASE 
        WHEN t.payment_method = 'voucher' AND v.id IS NOT NULL THEN v.payment_status
        ELSE t.payment_status
    END as actual_payment_status
FROM transactions t
JOIN clients c ON t.client_id = c.id
JOIN access_logs al ON t.access_log_id = al.id
LEFT JOIN vouchers v ON v.used_by_access_log_id = al.id AND t.payment_method = 'voucher';

-- =====================================================================
-- 5. ACTUALIZAR DATOS EXISTENTES
-- =====================================================================
-- Marcar como usados los PINs de access_logs que ya tienen exit_datetime
-- (accesos ya completados)
UPDATE access_logs
SET pin_used = 1
WHERE exit_datetime IS NOT NULL
  AND status = 'completed';

-- =====================================================================
-- VERIFICACIÓN DE LA MIGRACIÓN
-- =====================================================================
-- Ejecutar estas queries para verificar que la migración se aplicó correctamente:

-- Verificar que el campo pin_used existe
-- SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
-- FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_SCHEMA = DATABASE()
--   AND TABLE_NAME = 'access_logs'
--   AND COLUMN_NAME = 'pin_used';

-- Verificar que el stored procedure existe
-- SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'update_voucher_payment_status';

-- Verificar que los triggers existen
-- SHOW TRIGGERS WHERE `Table` = 'voucher_payments';

-- Verificar que la vista existe
-- SHOW FULL TABLES WHERE TABLE_TYPE = 'VIEW' AND Tables_in_dunas_access_control = 'v_transactions_with_payment_status';

-- =====================================================================
-- NOTAS IMPORTANTES
-- =====================================================================
-- 1. El campo pin_used en access_logs permite validar que un PIN solo se use una vez
-- 2. El stored procedure update_voucher_payment_status calcula automáticamente
--    el estado de pago de los vouchers basándose en pagos vs costo total
-- 3. Los triggers automatizan la actualización del payment_status cuando se
--    registran, modifican o eliminan pagos
-- 4. La vista v_transactions_with_payment_status facilita consultas que muestran
--    el estado de pago correcto basado en vouchers
-- 5. Esta migración es IDEMPOTENTE - puede ejecutarse múltiples veces sin errores
-- =====================================================================
