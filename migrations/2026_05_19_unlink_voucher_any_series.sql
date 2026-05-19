-- =====================================================================
-- SCRIPT: Desvincular vale activo relacionado (cualquier serie)
-- Fecha: 2026-05-19
-- MySQL: 5.7+
-- Descripción:
--   - Permite desvincular manualmente un vale activo con empresa asignada
--     sin depender de la serie del vale.
--   - Ajusta el estado a pendiente de relación.
-- =====================================================================

START TRANSACTION;

SET @voucher_id := 0; -- Reemplazar por el ID real del vale a desvincular

UPDATE vouchers
SET
    client_id = NULL,
    status = 'pending_assignment',
    updated_at = NOW()
WHERE id = @voucher_id
  AND status = 'active'
  AND client_id IS NOT NULL;

SELECT ROW_COUNT() AS updated_vouchers;

COMMIT;
