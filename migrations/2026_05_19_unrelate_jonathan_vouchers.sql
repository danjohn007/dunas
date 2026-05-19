-- =====================================================================
-- SCRIPT: Quitar relación de vales asignados a la empresa "jonathan"
-- Fecha: 2026-05-19
-- MySQL: 5.7+
-- Descripción:
--   - Solo afecta vales de imprenta actualmente relacionados con
--     la empresa "jonathan".
--   - Revierte su estado a pendiente de relación.
--   - No elimina ningún registro.
-- =====================================================================

START TRANSACTION;

UPDATE vouchers v
INNER JOIN clients c ON c.id = v.client_id
SET
    v.client_id = NULL,
    v.status = 'pending_assignment',
    v.updated_at = NOW()
WHERE v.voucher_type = 'imprenta'
  AND v.status = 'active'
  AND v.used_by_access_log_id IS NULL
  AND LOWER(TRIM(c.business_name)) = 'jonathan';

SELECT ROW_COUNT() AS updated_vouchers;

COMMIT;
