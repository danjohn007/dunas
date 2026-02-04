# Test Plan - Voucher Generation

## Prerequisites
1. ✅ Code fixes deployed
2. ✅ Database migration executed (`capacity` column exists)
3. ✅ User logged in as Admin or Supervisor

## Test 1: Generate Small Batch
**Purpose**: Verify basic voucher generation works

### Steps
1. Navigate to `/vouchers`
2. Click "Generar Vales" button
3. Fill form:
   - Serie: `TEST`
   - Folio inicial: `1`
   - Cantidad: `5`
   - Capacidad: `1000`
4. Click "Generar Vales"

### Expected Results
- ✅ No PHP errors
- ✅ Success message: "Se generaron exitosamente 5 vales"
- ✅ Redirects to print view
- ✅ Print view shows 5 vouchers with QR codes

### Verify in Database
```sql
SELECT * FROM vouchers WHERE serie = 'TEST';
```
Should return 5 rows with:
- serie: TEST
- folio: 1, 2, 3, 4, 5
- qr_code: TEST-000001-[timestamp], etc.
- capacity: 1000
- status: active

## Test 2: Verify Uniqueness
**Purpose**: Ensure duplicate prevention works

### Steps
1. Try to generate same batch again:
   - Serie: `TEST`
   - Folio inicial: `1`
   - Cantidad: `5`

### Expected Results
- ✅ Should show errors about duplicates
- ✅ No additional vouchers created
- ✅ Original 5 vouchers remain unchanged

## Test 3: Generate Larger Batch
**Purpose**: Test batch generation performance

### Steps
1. Generate larger batch:
   - Serie: `R`
   - Folio inicial: `1000`
   - Cantidad: `50`
   - Capacidad: `10000`

### Expected Results
- ✅ Generates 50 vouchers successfully
- ✅ Print view displays all 50
- ✅ QR codes unique for each
- ✅ Takes less than 10 seconds

## Test 4: View Vouchers List
**Purpose**: Verify listing and statistics

### Steps
1. Navigate to `/vouchers`
2. Check statistics at top
3. Verify vouchers appear in table

### Expected Results
- ✅ Statistics show correct counts
- ✅ Total: 55 (5 + 50)
- ✅ Activos: 55
- ✅ Usados: 0
- ✅ All vouchers listed in table

## Test 5: View Voucher Detail
**Purpose**: Verify detail page works

### Steps
1. Click eye icon on any voucher
2. View detail page

### Expected Results
- ✅ Detail page loads (URL: `/vouchers/detail/[id]`)
- ✅ Shows voucher information
- ✅ QR code displayed
- ✅ No errors

## Test 6: Print Functionality
**Purpose**: Verify print view works

### Steps
1. From vouchers list, generate new batch
2. Verify print view opens automatically
3. Click "Imprimir" button

### Expected Results
- ✅ Print dialog opens
- ✅ Vouchers formatted correctly (1/2 carta)
- ✅ QR codes visible and scannable
- ✅ All fields present

## Common Issues

### Issue: "Column 'capacity' not found"
**Solution**: Database migration not executed
```bash
mysql -u systemco_dunas -p systemco_dunas < config/fix_vouchers_capacity_column.sql
```

### Issue: Duplicate key error on qr_code
**Solution**: Normal behavior - trying to create duplicate vouchers
Change the serie or folio to generate unique vouchers

### Issue: No statistics shown
**Solution**: Check database has vouchers table and capacity column exists

## Success Criteria
All tests should pass:
- [ ] Test 1: Basic generation ✅
- [ ] Test 2: Uniqueness validation ✅
- [ ] Test 3: Larger batch ✅
- [ ] Test 4: List view ✅
- [ ] Test 5: Detail view ✅
- [ ] Test 6: Print functionality ✅

## Final Verification
```sql
-- Check voucher counts
SELECT 
    status,
    COUNT(*) as total,
    SUM(capacity) as total_capacity
FROM vouchers
GROUP BY status;

-- Check sample vouchers
SELECT * FROM vouchers LIMIT 10;
```
