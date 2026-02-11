# Testing Guide for Dunas System Fixes

## Overview
This guide provides step-by-step instructions for testing the fixes implemented for the Dunas voucher management system.

## Prerequisites
- Access to the Dunas system with admin or supervisor privileges
- Test data: At least 2-3 companies with generated vouchers
- Some vouchers with registered payments in the `voucher_payments` table

## Test 1: Payment Tracking in Financial Report

### Objective
Verify that the MONTOS column in "Resumen de Vales por Empresa" correctly displays all payments including those registered through the payment system.

### Steps
1. Navigate to **Reportes** → **Reporte Financiero**
2. Select a date range that includes vouchers with registered payments
3. Scroll down to the "Resumen de Vales por Empresa" section
4. Locate a company that has both:
   - Vouchers with `payment_status = 'paid'` in the vouchers table
   - Registered payments in the `voucher_payments` table

### Expected Results
- The MONTOS column should show:
  - **Pagado**: Amount from vouchers marked as paid
  - **+ $X registrado**: Additional payments registered separately (if any)
  - **Pendiente**: Remaining pending amount (original pending - registered payments)
  - If registered payments exist, the original pending amount should appear struck through

### Pass Criteria
✅ All payment amounts are displayed correctly
✅ Registered payments are shown as "+ $X registrado"
✅ Adjusted pending amount is calculated correctly (total_pending - registered_payments)

## Test 2: Chronological Sorting

### Objective
Verify that voucher batches are sorted by creation date (newest first) instead of alphabetically by company name.

### Steps
1. Generate vouchers for Company A on Date 1
2. Generate vouchers for Company B on Date 2 (later than Date 1)
3. Navigate to **Reportes** → **Reporte Financiero**
4. View the "Resumen de Vales por Empresa" section

### Expected Results
- Company B's vouchers should appear **before** Company A's vouchers
- Vouchers are sorted by creation date in descending order (newest first)
- Within the same company, series are sorted alphabetically

### Pass Criteria
✅ Newest voucher batches appear first
✅ Order is chronological, not alphabetical by company name
✅ Series within same company are ordered correctly

## Test 3: Company Name Search

### Test 3a: Search in Financial Report

### Steps
1. Navigate to **Reportes** → **Reporte Financiero**
2. Ensure multiple companies with vouchers are visible
3. Locate the search box in the "Resumen de Vales por Empresa" section
4. Type a partial company name (e.g., "Dunas" if company is "Viri Dunas SA")

### Expected Results
- As you type, the table should filter in real-time
- Only companies matching the search term should be visible
- Search should be case-insensitive
- Other companies should be hidden (not deleted)
- Clearing the search should restore all companies

### Test 3b: Search in Vouchers Summary

### Steps
1. Navigate to **Reportes** → **Ver Resumen Completo** (or direct to Resumen de Vales Generados)
2. Locate the search box in the "Detalle por Empresa" section
3. Perform the same search tests as above

### Pass Criteria
✅ Search filters companies in real-time
✅ Case-insensitive search works
✅ Partial matches are found
✅ Clearing search restores all companies
✅ Search works in both financial report and vouchers summary

## Test 4: Transaction Status Filter

### Objective
Verify that the Estado (status) filter in "Gestión de Transacciones" correctly filters transactions, especially showing "Pendiente" status.

### Steps
1. Create or ensure you have test transactions with different statuses:
   - Transactions with cash/transfer payment marked as "pending"
   - Transactions using vouchers where the voucher status is "pending"
   - Transactions with "paid" status
   - Transactions with "cancelled" status

2. Navigate to **Transacciones** → **Gestión de Transacciones**

3. Test Filter: Pendiente
   - Select "Pendiente" from the Estado dropdown
   - Click "Filtrar"
   - Verify that only pending transactions appear

4. Test Filter: Pagado
   - Select "Pagado" from the Estado dropdown
   - Click "Filtrar"
   - Verify that only paid transactions appear

5. Test Filter: Cancelado
   - Select "Cancelado" from the Estado dropdown
   - Click "Filtrar"
   - Verify that only cancelled transactions appear

6. Test Filter: Todos los estados
   - Select "Todos los estados" from the Estado dropdown
   - Click "Filtrar"
   - Verify that all transactions appear

### Expected Results
- For voucher-based transactions, the filter should use the voucher's `payment_status`
- For cash/transfer transactions, the filter should use the transaction's `payment_status`
- The "Pendiente" filter should now work correctly and show pending transactions

### Pass Criteria
✅ "Pendiente" filter shows all pending transactions
✅ "Pagado" filter shows all paid transactions
✅ "Cancelado" filter shows all cancelled transactions
✅ Voucher transactions show correct status from voucher record
✅ Non-voucher transactions show correct status from transaction record

## Test 5: Folio Generation

### Objective
Verify that voucher generation starts from the specified folio number.

### Steps
1. Navigate to **Vales** → **Generar Vales**
2. Fill in the form:
   - **Serie**: Enter a NEW serie (e.g., "TEST1") that has no existing vouchers
   - **Folio Inicial**: Enter `1`
   - **Cantidad**: Enter `5`
   - **Capacidad**: Enter `10000`
   - **Empresa**: Select any company
   - **Costo por Vale**: Enter any value
   - **Estado de Pago**: Select "Pendiente"
3. Click "Generar Vales"
4. View the generated batch (should redirect to print page)

### Expected Results
- Vouchers should be generated with folios: 1, 2, 3, 4, 5
- QR codes should be: TEST1-1, TEST1-2, TEST1-3, TEST1-4, TEST1-5
- No vouchers should be skipped

### Troubleshooting if Folios Start at 10

If folios start at 10 instead of 1, check:

1. **Existing Vouchers**
   ```sql
   SELECT * FROM vouchers WHERE serie = 'TEST1' AND folio BETWEEN 1 AND 9;
   ```
   If folios 1-9 exist, they will be skipped due to duplicate check.

2. **Database Constraints**
   ```sql
   SHOW CREATE TABLE vouchers;
   ```
   Look for CHECK constraints on the `folio` column.

3. **Error Messages**
   - Check if the success message says "Se encontraron X errores (posibles duplicados)"
   - If yes, folios 1-9 likely already exist

### Pass Criteria
✅ Folios start from the specified number (1)
✅ Folios are consecutive (1, 2, 3, 4, 5)
✅ No unexpected gaps in folio numbers
✅ QR codes match format: SERIE-FOLIO

## Browser Compatibility Testing

Test all features in the following browsers:
- [ ] Google Chrome (latest)
- [ ] Mozilla Firefox (latest)
- [ ] Microsoft Edge (latest)
- [ ] Safari (if applicable)

## Regression Testing

Verify that existing functionality still works:
- [ ] Voucher usage/redemption
- [ ] Access log creation
- [ ] Transaction creation
- [ ] Report generation (PDF/Excel exports)
- [ ] Payment registration

## Performance Testing

- [ ] Search functionality responds quickly with large datasets
- [ ] Reports load within acceptable time
- [ ] No JavaScript errors in browser console

## Security Testing

- [ ] SQL injection attempts are prevented (parameterized queries)
- [ ] XSS attempts are sanitized (htmlspecialchars)
- [ ] Only authorized users can access reports
- [ ] Session handling works correctly

## Reporting Issues

If you find issues during testing, report them with:
1. **Step-by-step reproduction**
2. **Expected vs Actual behavior**
3. **Screenshots** (if applicable)
4. **Browser and version**
5. **Any error messages** from browser console or PHP logs

## Success Criteria

All tests must pass for the implementation to be considered complete:
- ✅ Payment tracking shows all payments correctly
- ✅ Chronological sorting works as expected
- ✅ Company search filters properly in both reports
- ✅ Transaction status filter works for all statuses including "Pendiente"
- ✅ Folio generation starts from specified number
- ✅ No regression in existing functionality
- ✅ No security vulnerabilities introduced
