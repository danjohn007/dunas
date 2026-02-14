-- =====================================================================
-- MIGRATION: Fix Payment Tracking Per Batch
-- Fecha: 2026-02-14
-- Descripción:
--   Corrige el sistema de pagos para que cada lote de vales consecutivos
--   mantenga su propio seguimiento de pagos independiente.
--   
-- Problemas que resuelve:
--   1. Los pagos parciales no se reflejan en los TOTALES
--   2. Los pagos se aplican incorrectamente a TODOS los lotes de un cliente
--   3. El máximo pago se replica en todos los registros del cliente
--
-- Solución:
--   - Agregar campos para identificar el lote específico en voucher_payments
--   - Crear nuevo stored procedure que actualiza solo el lote especificado
--   - Modificar triggers para usar el nuevo procedimiento
-- =====================================================================

-- =====================================================================
-- 1. AGREGAR CAMPOS PARA IDENTIFICAR EL LOTE EN VOUCHER_PAYMENTS
-- =====================================================================

-- Agregar serie para identificar el lote
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'voucher_payments'
      AND COLUMN_NAME = 'serie'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `voucher_payments` ADD COLUMN `serie` VARCHAR(10) NULL COMMENT ''Serie del lote de vales al que aplica este pago'' AFTER `client_id`',
    'SELECT "Column serie already exists in voucher_payments" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar folio_inicio para identificar el lote
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'voucher_payments'
      AND COLUMN_NAME = 'folio_inicio'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `voucher_payments` ADD COLUMN `folio_inicio` INT NULL COMMENT ''Folio inicial del lote al que aplica este pago'' AFTER `serie`',
    'SELECT "Column folio_inicio already exists in voucher_payments" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar folio_fin para identificar el lote
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'voucher_payments'
      AND COLUMN_NAME = 'folio_fin'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `voucher_payments` ADD COLUMN `folio_fin` INT NULL COMMENT ''Folio final del lote al que aplica este pago'' AFTER `folio_inicio`',
    'SELECT "Column folio_fin already exists in voucher_payments" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar índice compuesto para búsquedas eficientes por lote
SET @index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'voucher_payments'
      AND INDEX_NAME = 'idx_client_serie_folio'
);

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE `voucher_payments` ADD INDEX `idx_client_serie_folio` (`client_id`, `serie`, `folio_inicio`, `folio_fin`)',
    'SELECT "Index idx_client_serie_folio already exists" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================================
-- 2. NUEVO STORED PROCEDURE PARA ACTUALIZAR PAYMENT_STATUS POR LOTE
-- =====================================================================

DROP PROCEDURE IF EXISTS `update_voucher_payment_status_per_batch`;

DELIMITER $$

CREATE PROCEDURE `update_voucher_payment_status_per_batch`(
    IN p_client_id INT,
    IN p_serie VARCHAR(10),
    IN p_folio_inicio INT,
    IN p_folio_fin INT
)
BEGIN
    DECLARE v_total_cost DECIMAL(10,2);
    DECLARE v_total_paid DECIMAL(10,2);
    DECLARE v_total_pending DECIMAL(10,2);
    
    -- Calcular el costo total del lote específico
    SELECT COALESCE(SUM(cost), 0) INTO v_total_cost
    FROM vouchers
    WHERE client_id = p_client_id
      AND serie = p_serie
      AND folio >= p_folio_inicio
      AND folio <= p_folio_fin
      AND cost IS NOT NULL;
    
    -- Calcular el total pagado para este lote específico
    SELECT COALESCE(SUM(amount), 0) INTO v_total_paid
    FROM voucher_payments
    WHERE client_id = p_client_id
      AND serie = p_serie
      AND folio_inicio = p_folio_inicio
      AND folio_fin = p_folio_fin;
    
    -- Calcular el pendiente
    SET v_total_pending = v_total_cost - v_total_paid;
    
    -- Si el pago cubre todo o más, marcar todos los vales del lote como pagados
    IF v_total_pending <= 0 THEN
        UPDATE vouchers
        SET payment_status = 'paid'
        WHERE client_id = p_client_id
          AND serie = p_serie
          AND folio >= p_folio_inicio
          AND folio <= p_folio_fin
          AND cost IS NOT NULL
          AND payment_status = 'pending';
    ELSE
        -- Si aún hay pendiente, los vales siguen como pending
        -- Esto permite manejar pagos parciales correctamente
        UPDATE vouchers
        SET payment_status = 'pending'
        WHERE client_id = p_client_id
          AND serie = p_serie
          AND folio >= p_folio_inicio
          AND folio <= p_folio_fin
          AND cost IS NOT NULL
          AND payment_status = 'paid';
    END IF;
    
END$$

DELIMITER ;

-- =====================================================================
-- 3. REEMPLAZAR TRIGGERS PARA USAR EL NUEVO PROCEDIMIENTO POR LOTE
-- =====================================================================

-- Trigger después de INSERT
DROP TRIGGER IF EXISTS `after_voucher_payment_insert`;

DELIMITER $$

CREATE TRIGGER `after_voucher_payment_insert`
AFTER INSERT ON `voucher_payments`
FOR EACH ROW
BEGIN
    -- Solo actualizar si se especificó serie y rango de folios
    IF NEW.serie IS NOT NULL AND NEW.folio_inicio IS NOT NULL AND NEW.folio_fin IS NOT NULL THEN
        CALL update_voucher_payment_status_per_batch(
            NEW.client_id,
            NEW.serie,
            NEW.folio_inicio,
            NEW.folio_fin
        );
    ELSE
        -- Si no se especificó lote, usar el procedimiento anterior (retrocompatibilidad)
        -- Solo actualizar si existe el procedimiento anterior
        IF EXISTS (
            SELECT 1 FROM information_schema.ROUTINES 
            WHERE ROUTINE_SCHEMA = DATABASE() 
            AND ROUTINE_NAME = 'update_voucher_payment_status'
        ) THEN
            BEGIN
                DECLARE v_min_date DATE;
                DECLARE v_max_date DATE;
                
                SELECT MIN(DATE(created_at)), MAX(DATE(created_at))
                INTO v_min_date, v_max_date
                FROM vouchers
                WHERE client_id = NEW.client_id
                  AND cost IS NOT NULL;
                
                IF v_min_date IS NOT NULL THEN
                    CALL update_voucher_payment_status(
                        NEW.client_id,
                        v_min_date,
                        v_max_date
                    );
                END IF;
            END;
        END IF;
    END IF;
END$$

DELIMITER ;

-- Trigger después de UPDATE
DROP TRIGGER IF EXISTS `after_voucher_payment_update`;

DELIMITER $$

CREATE TRIGGER `after_voucher_payment_update`
AFTER UPDATE ON `voucher_payments`
FOR EACH ROW
BEGIN
    -- Solo actualizar si se especificó serie y rango de folios
    IF NEW.serie IS NOT NULL AND NEW.folio_inicio IS NOT NULL AND NEW.folio_fin IS NOT NULL THEN
        CALL update_voucher_payment_status_per_batch(
            NEW.client_id,
            NEW.serie,
            NEW.folio_inicio,
            NEW.folio_fin
        );
    ELSE
        -- Retrocompatibilidad con procedimiento anterior
        IF EXISTS (
            SELECT 1 FROM information_schema.ROUTINES 
            WHERE ROUTINE_SCHEMA = DATABASE() 
            AND ROUTINE_NAME = 'update_voucher_payment_status'
        ) THEN
            BEGIN
                DECLARE v_min_date DATE;
                DECLARE v_max_date DATE;
                
                SELECT MIN(DATE(created_at)), MAX(DATE(created_at))
                INTO v_min_date, v_max_date
                FROM vouchers
                WHERE client_id = NEW.client_id
                  AND cost IS NOT NULL;
                
                IF v_min_date IS NOT NULL THEN
                    CALL update_voucher_payment_status(
                        NEW.client_id,
                        v_min_date,
                        v_max_date
                    );
                END IF;
            END;
        END IF;
    END IF;
END$$

DELIMITER ;

-- Trigger después de DELETE
DROP TRIGGER IF EXISTS `after_voucher_payment_delete`;

DELIMITER $$

CREATE TRIGGER `after_voucher_payment_delete`
AFTER DELETE ON `voucher_payments`
FOR EACH ROW
BEGIN
    -- Solo actualizar si se especificó serie y rango de folios
    IF OLD.serie IS NOT NULL AND OLD.folio_inicio IS NOT NULL AND OLD.folio_fin IS NOT NULL THEN
        CALL update_voucher_payment_status_per_batch(
            OLD.client_id,
            OLD.serie,
            OLD.folio_inicio,
            OLD.folio_fin
        );
    ELSE
        -- Retrocompatibilidad con procedimiento anterior
        IF EXISTS (
            SELECT 1 FROM information_schema.ROUTINES 
            WHERE ROUTINE_SCHEMA = DATABASE() 
            AND ROUTINE_NAME = 'update_voucher_payment_status'
        ) THEN
            BEGIN
                DECLARE v_min_date DATE;
                DECLARE v_max_date DATE;
                
                SELECT MIN(DATE(created_at)), MAX(DATE(created_at))
                INTO v_min_date, v_max_date
                FROM vouchers
                WHERE client_id = OLD.client_id
                  AND cost IS NOT NULL;
                
                IF v_min_date IS NOT NULL THEN
                    CALL update_voucher_payment_status(
                        OLD.client_id,
                        v_min_date,
                        v_max_date
                    );
                END IF;
            END;
        END IF;
    END IF;
END$$

DELIMITER ;

-- =====================================================================
-- VERIFICACIÓN DE LA MIGRACIÓN
-- =====================================================================
-- Para verificar que la migración se aplicó correctamente:

-- 1. Verificar columnas agregadas
-- SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
-- FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_SCHEMA = DATABASE()
--   AND TABLE_NAME = 'voucher_payments'
--   AND COLUMN_NAME IN ('serie', 'folio_inicio', 'folio_fin');

-- 2. Verificar el nuevo stored procedure
-- SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'update_voucher_payment_status_per_batch';

-- 3. Verificar los triggers actualizados
-- SHOW TRIGGERS WHERE `Table` = 'voucher_payments';

-- =====================================================================
-- NOTAS IMPORTANTES
-- =====================================================================
-- 1. Esta migración agrega campos serie, folio_inicio, folio_fin a voucher_payments
--    para identificar el lote específico al que aplica cada pago
-- 2. El nuevo stored procedure update_voucher_payment_status_per_batch actualiza
--    solo los vouchers del lote especificado, manteniendo independencia entre lotes
-- 3. Los triggers fueron modificados para usar el nuevo procedimiento cuando se
--    especifican los campos del lote, manteniendo retrocompatibilidad
-- 4. Los pagos parciales ahora se reflejarán correctamente por lote
-- 5. Cada lote mantiene su propio estado de pago independiente
-- 6. Esta migración es IDEMPOTENTE - puede ejecutarse múltiples veces sin errores
-- =====================================================================
