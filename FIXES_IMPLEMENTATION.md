# Fixes Implemented for Dunas System Issues

## Summary of Changes

This document describes the fixes implemented for the issues reported in the Dunas system.

## Issue 1: Payment Tracking in "Resumen de Vales por Empresa" ✅ FIXED

**Problem**: The MONTOS column in the "Resumen de Vales por Empresa" section of the financial report didn't reflect all payments made.

**Root Cause**: The financial report was only showing payments from the `vouchers.payment_status` field, but not including payments registered through the `voucher_payments` table.

**Solution**:
1. Updated `ReportController::financial()` to fetch registered payments for each company using `VoucherPaymentModel::getTotalPaidByClient()`
2. Modified `/app/views/reports/financial.php` to display:
   - Direct voucher payments (`total_paid`)
   - Registered payments (`total_paid_registered`)
   - Adjusted pending amount (`actual_pending`)

**Files Changed**:
- `/app/controllers/ReportController.php` - Added payment calculation logic
- `/app/views/reports/financial.php` - Updated MONTOS column display

## Issue 2: Sort Order in "Resumen de Vales por Empresa" ✅ FIXED

**Problem**: Companies were listed in alphabetical order instead of chronological order.

**Solution**: Updated the SQL query in `Voucher::getVouchersByCompany()` to sort by creation date instead of business name.

**Change**:
```php
// Before
ORDER BY c.business_name ASC, v.serie ASC

// After  
ORDER BY MIN(v.created_at) DESC, v.serie ASC
```

**Files Changed**:
- `/app/models/Voucher.php` - Line 450

## Issue 3: Add Search by Company Name ✅ FIXED

**Problem**: No search functionality existed to filter companies by name in the voucher reports.

**Solution**:
1. Added search input fields in both report views
2. Implemented JavaScript-based client-side filtering

**Features Added**:
- Search input box that filters companies in real-time
- Case-insensitive search
- Filters table rows based on company name match

**Files Changed**:
- `/app/views/reports/financial.php` - Added search input and JavaScript filter
- `/app/views/reports/vouchers_summary.php` - Added search input and JavaScript filter

## Issue 4: Transaction Status Filter Not Working ✅ FIXED

**Problem**: The "Estado" (status) filter in "Gestión de Transacciones" wasn't showing "Pendiente" status correctly.

**Root Cause**: The filter was checking `t.payment_status` directly, but for voucher transactions, the actual status comes from the voucher's `payment_status` field.

**Solution**: Updated `Transaction::buildFilterConditions()` to use a CASE statement that matches the `actual_payment_status` logic:

```php
if (!empty($filters['payment_status'])) {
    $conditions[] = "CASE 
        WHEN t.payment_method = 'voucher' AND v.id IS NOT NULL THEN v.payment_status
        ELSE t.payment_status
    END = ?";
    $params[] = $filters['payment_status'];
}
```

**Files Changed**:
- `/app/models/Transaction.php` - Updated `buildFilterConditions()` method

## Issue 5: Folio Generation Starting at 10 Instead of 1 ⚠️ INVESTIGATION NEEDED

**Problem**: When entering "1" as the "Folio Inicial", the system supposedly starts generating folios from 10.

**Investigation Results**:
- Tested the PHP logic extensively - it is mathematically correct
- Integer casting from string "1" produces integer 1
- Loop iteration produces correct sequence: 1, 2, 3, 4, 5...
- QR code generation format is correct: "SERIE-1", "SERIE-2", etc.

**Potential Causes** (Cannot be confirmed without reproducing the issue):

1. **Duplicate Folios**: If folios 1-9 already exist for that serie in the database, the duplicate check will fail and skip those folios. The first successful folio would be 10.

2. **User Input Error**: User might be accidentally entering "10" instead of "1" or there's confusion between the display format (padded "0001") and actual value.

3. **Database Constraint**: There might be a CHECK constraint or trigger in the database that enforces a minimum folio value.

4. **Browser Autofill**: Browser might be auto-filling a previous value.

**Recommended Next Steps**:
1. Check for existing vouchers with folios 1-9 in the same serie
2. Verify database schema for any CHECK constraints on the folio field
3. Test with a fresh serie that has no existing vouchers
4. Add server-side logging to track what values are actually being received

**Files to Check** (if issue persists):
- Database schema for `vouchers` table
- Any database triggers on the `vouchers` table
- Error logs when generating vouchers

## Testing Checklist

- [ ] Test payment tracking in "Resumen de Vales por Empresa"
  - Verify "Pagado" shows direct voucher payments
  - Verify "+ registrado" shows additional registered payments
  - Verify "Pendiente" shows adjusted amount after registered payments

- [ ] Test chronological sorting
  - Create vouchers for different companies at different times
  - Verify they appear in reverse chronological order (newest first)

- [ ] Test company search
  - Enter company name in search box
  - Verify only matching companies are shown
  - Test partial name matches
  - Test case-insensitive search

- [ ] Test transaction status filter
  - Filter by "Pendiente" status
  - Verify pending voucher transactions appear
  - Filter by "Pagado" status
  - Verify paid transactions appear
  - Filter by "Cancelado" status
  - Verify cancelled transactions appear

- [ ] Test folio generation
  - Create new voucher batch with folio inicial = 1
  - Verify first folio is 1, not 10
  - Check generated QR codes match expected format

## Security Considerations

All changes maintain existing security practices:
- Authentication and authorization checks remain in place
- Input sanitization using htmlspecialchars() for XSS prevention
- Parameterized SQL queries to prevent SQL injection
- No new external dependencies introduced

## Deployment Notes

1. These are VIEW and MODEL changes only - no database schema changes required
2. No new dependencies need to be installed
3. Changes are backward compatible with existing data
4. Can be deployed without downtime

## Support

If issues persist after deployment:
1. Check browser console for JavaScript errors
2. Check PHP error logs for server-side issues
3. Verify database queries are executing correctly
4. Test with different browsers to rule out client-side issues
