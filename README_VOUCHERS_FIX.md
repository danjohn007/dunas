# 🚨 URGENT FIX NEEDED - Vouchers Module

## Current Status: ⚠️ TWO ISSUES

### Issue 1: ✅ FIXED (Code)
Method signature conflict - **Already resolved in code**

### Issue 2: ⚠️ NEEDS FIX (Database)
Missing `capacity` column in database

---

## 🔧 QUICK FIX (5 Minutes)

### Option A: phpMyAdmin (Easiest) ⭐

1. **Open phpMyAdmin** → Select database `systemco_dunas`
2. **Click "SQL" tab** at the top
3. **Paste this** and click "Go":

```sql
ALTER TABLE `vouchers` 
ADD COLUMN `capacity` int(11) NOT NULL DEFAULT '0' AFTER `qr_code`;
```

4. **Done!** Visit `/vouchers` - should work now

### Option B: Command Line

```bash
cd /home4/systemcontrol/public_html/dunas/7
mysql -u systemco_dunas -p systemco_dunas < config/fix_vouchers_capacity_column.sql
```

---

## 📖 Detailed Guides

| Guide | Use When |
|-------|----------|
| `QUICK_FIX.md` | Need immediate 1-page reference |
| `VISUAL_FIX_GUIDE.md` | Want step-by-step with phpMyAdmin |
| `DATABASE_FIX_VOUCHERS.md` | Need 4 different fix methods |
| `DEPLOYMENT_CHECKLIST_VOUCHERS.md` | Doing full deployment |
| `RESOLUTION_SUMMARY.md` | Want complete overview |

---

## ✅ Verify Fix Worked

```sql
DESCRIBE vouchers;
```
Should show `capacity` column.

Test: Visit `/vouchers` - should load without errors.

---

## 📞 Need Help?

See detailed troubleshooting in:
- `DATABASE_FIX_VOUCHERS.md`
- `VISUAL_FIX_GUIDE.md`
