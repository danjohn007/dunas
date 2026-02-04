# Visual Guide - Fix Database in phpMyAdmin

## Step-by-Step Instructions with Screenshots

### The Problem
When accessing `/vouchers`, you see:
```
Database Query Error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'capacity' in 'field list'
```

### The Solution (5 minutes)

---

## Method 1: Using SQL Tab (Recommended)

### Step 1: Open phpMyAdmin
- URL: Usually `https://your-domain.com:2083/cpsess.../phpMyAdmin/`
- Or through cPanel → Databases → phpMyAdmin

### Step 2: Select Database
- Click on `systemco_dunas` in the left sidebar
- (Or the database name shown in your config)

### Step 3: Click SQL Tab
- At the top menu, click "SQL"
- You'll see a text area for SQL commands

### Step 4: Paste This SQL
```sql
ALTER TABLE `vouchers` 
ADD COLUMN `capacity` int(11) NOT NULL DEFAULT '0' AFTER `qr_code`;
```

### Step 5: Click "Go" Button
- Click the "Go" button at the bottom right
- You should see: "Query OK, 0 rows affected"

### Step 6: Verify Fix
Click SQL tab again and run:
```sql
DESCRIBE vouchers;
```

You should see `capacity` in the list of columns.

---

## Method 2: Using Structure Tab (Visual)

### Step 1: Navigate to Table
1. phpMyAdmin → `systemco_dunas` database
2. Click on `vouchers` table in left sidebar
3. Click "Structure" tab at top

### Step 2: Add Column
1. Scroll to bottom
2. Click "Add 1 column" (or more)
3. Choose "After" and select `qr_code` from dropdown

### Step 3: Configure Column
Fill in the form:
- **Name**: `capacity`
- **Type**: INT
- **Length/Values**: 11
- **Default**: As defined: 0
- **Attributes**: (leave blank)
- **Null**: Uncheck (NOT NULL)
- **Index**: (leave as None)
- **Auto Increment**: Uncheck

### Step 4: Save
- Click "Save" button
- Column should now appear in the structure

---

## Verification

### Test 1: Check Column Exists
In phpMyAdmin:
1. Select `vouchers` table
2. Click "Structure" tab
3. Look for `capacity` column - should be there between `qr_code` and `status`

### Test 2: Check Application
Visit: `https://your-domain.com/vouchers`
- Should load without error
- Should show statistics at top
- Should list any existing vouchers

### Test 3: Check Error Log
Look at error log (if you have access):
- Should NOT show "Unknown column 'capacity'" anymore

---

## Troubleshooting

### Error: "Table 'vouchers' doesn't exist"
**Solution**: Table needs to be created first. Run complete migration:
```sql
SOURCE /path/to/dunas/config/update_vouchers_module.sql;
```

Or use the SQL tab and paste the entire content of `config/update_vouchers_module.sql`

### Error: "Duplicate column name 'capacity'"
**Good news!** This means the column already exists. The fix was already applied.
Just refresh the vouchers page - it should work now.

### Error: "Access denied"
**Solution**: You need database admin privileges. Contact your system administrator.

### Error: "Foreign key constraint fails"
**Solution**: Make sure `access_logs` and `users` tables exist first.

---

## What This Fix Does

The SQL command adds a column to store the capacity (in liters) of each voucher:

```sql
ALTER TABLE `vouchers`          -- Modify the vouchers table
ADD COLUMN `capacity`            -- Add a new column named capacity
int(11)                          -- Type: Integer, 11 digits max
NOT NULL                         -- Cannot be empty
DEFAULT '0'                      -- Default value is 0
AFTER `qr_code`;                -- Place it after the qr_code column
```

This allows the system to:
- Store how many liters each voucher is worth
- Calculate total active capacity
- Display statistics correctly
- Generate vouchers with different capacities

---

## Still Having Issues?

### Check These Common Problems:

1. **Wrong Database Selected**
   - Make sure you're in `systemco_dunas` (or your DB name)
   - Check the config file for exact database name

2. **Table Name Mismatch**
   - Table must be named exactly `vouchers` (lowercase)
   - Check: `SHOW TABLES LIKE 'vouchers';`

3. **Permissions Issue**
   - Your MySQL user needs ALTER TABLE permission
   - Contact hosting provider if you don't have access

4. **Connection Issue**
   - Verify database connection in application config
   - Check Database.php config file

---

## Reference

- **Original Migration**: `config/update_vouchers_module.sql`
- **Quick Fix Script**: `config/fix_vouchers_capacity_column.sql`
- **Detailed Guide**: `DATABASE_FIX_VOUCHERS.md`
- **Full Checklist**: `DEPLOYMENT_CHECKLIST_VOUCHERS.md`

---

## Success! ✅

After applying the fix:
- `/vouchers` page loads successfully
- Statistics show correctly
- Can create new vouchers
- Can view voucher details
- No SQL errors in logs

**The vouchers module is now fully operational!**
