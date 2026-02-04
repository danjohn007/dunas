# Database Schema Verification and Fix

## Issue
The vouchers module is failing with:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'capacity' in 'field list'
```

## Root Cause
The `vouchers` table exists but is missing the `capacity` column. This can happen if:
1. The original migration was not executed completely
2. The table was created manually without all columns
3. An ALTER TABLE statement failed silently

## Solution

### Option 1: Run the Complete Migration (Recommended for new installations)
```bash
mysql -u systemco_dunas -p systemco_dunas < config/update_vouchers_module.sql
```

### Option 2: Fix Existing Table (For tables that already exist)
```bash
mysql -u systemco_dunas -p systemco_dunas < config/fix_vouchers_capacity_column.sql
```

### Option 3: Manual Fix via phpMyAdmin
1. Go to phpMyAdmin
2. Select the `systemco_dunas` database
3. Select the `vouchers` table
4. Go to "Structure" tab
5. Click "Add column"
6. Add column with these settings:
   - Name: `capacity`
   - Type: `INT(11)`
   - Default: `0`
   - NOT NULL: checked
   - Position: After `qr_code`

### Option 4: Direct SQL Command
```sql
ALTER TABLE `vouchers` 
ADD COLUMN `capacity` int(11) NOT NULL DEFAULT '0' AFTER `qr_code`;
```

## Verification
After applying the fix, verify the column exists:
```sql
DESCRIBE vouchers;
```

You should see `capacity` listed among the columns.

## Testing
1. Access the vouchers module: `/vouchers`
2. The page should load without errors
3. Try generating a new voucher to ensure everything works

## Prevention
To prevent this issue in the future:
1. Always run migrations immediately after deploying new code
2. Verify table structure matches the migration files
3. Check error logs after deployment
4. Use migration tracking to ensure all migrations are applied

## Related Files
- Migration: `config/update_vouchers_module.sql`
- Model: `app/models/Voucher.php` (line 234 uses capacity column)
- Controller: `app/controllers/VoucherController.php` (line 30 calls getStats())
