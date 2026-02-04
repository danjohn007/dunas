# QUICK FIX - Vouchers Module Error

## Problem
```
Column not found: 1054 Unknown column 'capacity' in 'field list'
```

## Solution (Run this in MySQL)

### Option 1: Via Command Line
```bash
cd /home4/systemcontrol/public_html/dunas/7
mysql -u systemco_dunas -p systemco_dunas < config/fix_vouchers_capacity_column.sql
```

### Option 2: Via phpMyAdmin
1. Open phpMyAdmin
2. Select database: `systemco_dunas`
3. Click "SQL" tab
4. Paste and execute:

```sql
ALTER TABLE `vouchers` 
ADD COLUMN `capacity` int(11) NOT NULL DEFAULT '0' AFTER `qr_code`;
```

### Option 3: If Table Doesn't Exist
```bash
cd /home4/systemcontrol/public_html/dunas/7
mysql -u systemco_dunas -p systemco_dunas < config/update_vouchers_module.sql
```

## Verify Fix Worked
```sql
DESCRIBE vouchers;
```
Should show `capacity` column.

## Test
Visit: https://your-domain.com/vouchers
Should load without error.

## Done! ✅
