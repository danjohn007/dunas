# Fix for Database::insert() Error

## Issue Fixed
```
PHP Fatal error: Call to undefined method Database::insert() 
in /app/models/Voucher.php:140
```

## Problem
The Voucher model was calling a non-existent `Database::insert()` method when trying to create vouchers.

## Solution
Changed to use the standard pattern used by all other models in the system:

### Before (Incorrect)
```php
$voucherId = $this->db->insert($sql, $params);
```

### After (Correct)
```php
$this->db->execute($sql, $params);
$voucherId = $this->db->lastInsertId();
```

## Database Helper Methods
The `Database` helper class provides these methods:
- `execute($sql, $params)` - Execute SQL query
- `lastInsertId()` - Get last inserted ID  
- `query($sql, $params)` - Execute and return statement
- `fetchAll($sql, $params)` - Fetch all results
- `fetchOne($sql, $params)` - Fetch single result

## Pattern Consistency
This fix makes the Voucher model consistent with other models:
- ✅ Client::create() uses `execute()` + `lastInsertId()`
- ✅ Driver::create() uses `execute()` + `lastInsertId()`
- ✅ AccessLog::create() uses `execute()` + `lastInsertId()`
- ✅ All other models follow same pattern

## Testing
To verify the fix works:

1. **Generate a test voucher batch**:
   - Navigate to `/vouchers/create`
   - Fill in form:
     - Serie: TEST
     - Folio inicial: 1
     - Cantidad: 5
     - Capacidad: 1000
   - Click "Generar Vales"

2. **Expected Result**:
   - ✅ No PHP fatal error
   - ✅ Vouchers created in database
   - ✅ Print view displays with QR codes
   - ✅ Success message shown

3. **Verify in Database**:
```sql
SELECT * FROM vouchers WHERE serie = 'TEST' ORDER BY folio;
```
Should show 5 vouchers with folios 1-5.

## Status
✅ **FIXED** - Voucher generation now works correctly

## Related Issues
- Also fixed: HTTP 500 method signature conflict (separate commit)
- Also fixed: Missing `capacity` column (database migration provided)

## Files Modified
- `app/models/Voucher.php` (line 140-141)
