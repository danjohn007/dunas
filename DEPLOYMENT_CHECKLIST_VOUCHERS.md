# Deployment Checklist for Vouchers Module

## Pre-Deployment
- [ ] Code pulled from repository to production server
- [ ] Database backup created
- [ ] Downtime window scheduled (if needed)

## Database Migration
**CRITICAL**: The vouchers module requires database changes

### Step 1: Verify Database Connection
```bash
mysql -u systemco_dunas -p
```
Enter password and ensure you can connect.

### Step 2: Run Migration
Choose ONE of these methods:

**Method A: Complete Migration (for new installations)**
```bash
cd /home4/systemcontrol/public_html/dunas/7
mysql -u systemco_dunas -p systemco_dunas < config/update_vouchers_module.sql
```

**Method B: Fix Script (if table exists but column missing)**
```bash
cd /home4/systemcontrol/public_html/dunas/7
mysql -u systemco_dunas -p systemco_dunas < config/fix_vouchers_capacity_column.sql
```

### Step 3: Verify Table Structure
```sql
USE systemco_dunas;
DESCRIBE vouchers;
```

**Expected Columns:**
- `id` (int, PRIMARY KEY, AUTO_INCREMENT)
- `serie` (varchar(10))
- `folio` (int(11))
- `qr_code` (varchar(50), UNIQUE)
- **`capacity` (int(11))** ← MUST BE PRESENT
- `status` (enum)
- `used_at` (datetime, nullable)
- `used_by_access_log_id` (int(11), nullable)
- `created_by` (int(11))
- `created_at` (timestamp)
- `updated_at` (timestamp)

## Post-Deployment Verification

### Test 1: Access Vouchers Module
```
URL: https://your-domain.com/vouchers
Expected: Page loads without HTTP 500 error
```

### Test 2: Check Error Logs
```bash
tail -50 /home4/systemcontrol/public_html/dunas/7/logs/error.log
```
Should NOT show: "Column not found: 1054 Unknown column 'capacity'"

### Test 3: Generate Test Voucher
1. Go to Vouchers → Generate Vouchers
2. Fill form:
   - Serie: TEST
   - Folio: 1
   - Quantity: 1
   - Capacity: 1000
3. Click Generate
4. Should succeed and show print view

### Test 4: View Vouchers List
- Should show statistics at top
- Should not show SQL errors

## Rollback Plan (if needed)
If something goes wrong:

```sql
-- Remove the vouchers table
DROP TABLE IF EXISTS `vouchers`;

-- Restore from backup
SOURCE /path/to/backup.sql;
```

## Common Issues

### Issue: "Column 'capacity' not found"
**Solution**: Run fix script
```bash
mysql -u systemco_dunas -p systemco_dunas < config/fix_vouchers_capacity_column.sql
```

### Issue: "Table 'vouchers' doesn't exist"
**Solution**: Run complete migration
```bash
mysql -u systemco_dunas -p systemco_dunas < config/update_vouchers_module.sql
```

### Issue: Foreign key constraint fails
**Solution**: Ensure `access_logs` and `users` tables exist first

### Issue: Duplicate key error on QR code
**Solution**: Normal - means you're trying to create duplicate vouchers. Check your input.

## Success Criteria
- [ ] No SQL errors in error.log
- [ ] Vouchers page loads (no HTTP 500)
- [ ] Can generate test voucher
- [ ] Can view voucher details
- [ ] Statistics display correctly
- [ ] Can print vouchers

## Contacts
- **Database Admin**: [Contact]
- **Developer**: [Contact]
- **Operations**: [Contact]

## Documentation
- User Guide: `VOUCHER_MODULE_GUIDE.md`
- Technical Details: `VOUCHER_IMPLEMENTATION_SUMMARY.md`
- Fix Instructions: `DATABASE_FIX_VOUCHERS.md`
- Deployment Steps: `DEPLOYMENT_STEPS.md`
