-- =====================================================================
-- SCRIPT: Quitar relaciones de vales activos por empresa y rango de folios
-- Fecha: 2026-05-19
-- MySQL: 5.7+
-- Descripción:
--   - Quita relaciones activas de vales usando la empresa y un rango
--     de folios con formato SERIE-0000.
--   - Si @folio_start_code y @folio_end_code se dejan en NULL,
--     desvincula todos los vales activos de la empresa indicada.
--   - Mantiene la lógica actual del sistema: client_id = NULL y
--     status = 'pending_assignment'.
-- =====================================================================

START TRANSACTION;

-- Reemplace estas variables antes de ejecutar el script.
-- Puede dejar @folio_start_code y @folio_end_code en NULL para desvincular
-- todos los vales activos de la empresa indicada.
-- Ejemplo: @company_name = 'Jonathan', @folio_start_code = 'AC-0026', @folio_end_code = 'AC-0030'
SET @company_name := 'REEMPLAZAR_EMPRESA';
SET @folio_start_code := NULL;
SET @folio_end_code := NULL;

SET @serie := NULL;
SET @serie_end := NULL;
SET @folio_start := NULL;
SET @folio_end := NULL;

SET @serie := IF(@folio_start_code IS NULL OR @folio_end_code IS NULL, NULL, SUBSTRING_INDEX(@folio_start_code, '-', 1));
SET @serie_end := IF(@folio_start_code IS NULL OR @folio_end_code IS NULL, NULL, SUBSTRING_INDEX(@folio_end_code, '-', 1));
SET @folio_start := IF(@folio_start_code IS NULL OR @folio_end_code IS NULL, NULL, CAST(SUBSTRING_INDEX(@folio_start_code, '-', -1) AS UNSIGNED));
SET @folio_end := IF(@folio_start_code IS NULL OR @folio_end_code IS NULL, NULL, CAST(SUBSTRING_INDEX(@folio_end_code, '-', -1) AS UNSIGNED));

UPDATE vouchers v
INNER JOIN clients c ON c.id = v.client_id
SET
    v.client_id = NULL,
    v.status = 'pending_assignment'
WHERE v.status = 'active'
  AND LOWER(TRIM(c.business_name)) = LOWER(TRIM(@company_name))
  AND (
        (@serie IS NULL AND @serie_end IS NULL)
        OR (
            v.serie = @serie
            AND @serie = @serie_end
            AND @folio_end >= @folio_start
            AND v.folio BETWEEN @folio_start AND @folio_end
        )
      );

SELECT ROW_COUNT() AS updated_vouchers;

COMMIT;
