# Plate Validation V3 - Testing Guide

## Overview
This guide provides comprehensive testing procedures for the plate validation fixes implemented in V3, which include diagnostic mode, improved error handling, and MySQL version compatibility.

## Changes Summary

### 1. Backend API (`compare_plate.php`)
- **Diagnostic Mode**: Added `?diag=1` query parameter for detailed debugging
- **MySQL Version Detection**: Automatic detection and fallback for MySQL < 8.0
- **Error Handling**: Always returns JSON (no HTML output on errors)
- **Improved Logging**: Better error messages in server logs

### 2. Frontend Views
- **Absolute URLs**: Using fixed URL to module 5 endpoint
- **Content-Type Validation**: Checks response type before parsing JSON
- **Error Logging**: Detailed console warnings for non-JSON responses
- **User-Friendly Messages**: Better error messages for end users

---

## Testing Procedures

### Test 1: Normal Operation (Happy Path)

#### Registrar Entrada View
1. Navigate to: `https://fix360.app/dunas/dunasshelly/5/access/create`
2. Select a client from the dropdown
3. Select a unit from the dropdown
4. **Expected Results:**
   - Comparison box should appear
   - Saved plate should display the unit's plate number
   - Detected plate should load from the camera
   - Status should show "Coincide" (green) or "No coincide" (gray)
   - No errors in browser console

#### Registro Rápido View
1. Navigate to: `https://fix360.app/dunas/dunasshelly/5/access/quick_registration`
2. Enter a plate number (e.g., "ABC-123")
3. Click "Buscar"
4. **Expected Results:**
   - Comparison box should appear after entering 3+ characters
   - Saved plate shows entered plate
   - Detected plate loads from camera
   - Status shows match or no match
   - No errors in browser console

---

### Test 2: Diagnostic Mode

#### Test API Directly
1. Open browser DevTools (F12)
2. Go to Console tab
3. Execute the following fetch command:

```javascript
// Test with unit_id
fetch('https://fix360.app/dunas/dunasshelly/5/api/compare_plate.php?diag=1', {
  method: 'POST',
  body: new FormData(Object.assign(document.createElement('form'), {
    elements: {unit_id: {value: '1'}}
  }))
}).then(r => r.json()).then(console.log);

// Test with unit_plate
fetch('https://fix360.app/dunas/dunasshelly/5/api/compare_plate.php?diag=1', {
  method: 'POST',
  body: (() => {
    const fd = new FormData();
    fd.append('unit_plate', 'ABC123');
    return fd;
  })()
}).then(r => r.json()).then(console.log);
```

4. **Expected Results in Response:**
   - `success: true`
   - `detected`: plate text from camera
   - `unit_plate`: requested plate
   - `is_match`: true/false
   - `_diag` object containing:
     - `mode`: "mysql8" or "php-filter"
     - `unit_id`: ID of unit (if provided)
     - `unit_plate`: plate from unit
     - `targetNorm`: normalized target plate
     - `db_version`: MySQL version string
     - `matched_id`: ID of matched record (if match found)
     - `matched_norm`: normalized matched plate (if match found)

---

### Test 3: Error Handling

#### Test Non-JSON Response
1. Temporarily rename the config file to simulate an error:
   ```bash
   # Don't actually do this in production!
   # This is just to test error handling
   ```

2. Instead, monitor the Network tab while using the views
3. Look for the compare_plate.php request
4. **Expected Results:**
   - Content-Type header should be: `application/json; charset=utf-8`
   - Response should be valid JSON
   - If there's an error, console should show: 
     ```
     COMPARE non-JSON: { url: "...", status: 500, ct: "...", sample: "..." }
     ```
   - UI should show: "No se pudo comparar (respuesta no JSON)"

---

### Test 4: MySQL Version Compatibility

#### Check Which Mode Is Being Used
1. Test with diagnostic mode enabled:
   ```bash
   curl -X POST "https://fix360.app/dunas/dunasshelly/5/api/compare_plate.php?diag=1" \
     -d "unit_id=1"
   ```

2. **Expected Results:**
   - Check `_diag.mode` in response
   - Check `_diag.db_version` in response
   - If MySQL 8+: mode should be "mysql8"
   - If MySQL < 8: mode should be "php-filter"

#### Verify Behavior
- **MySQL 8+ mode**: Uses `REGEXP_REPLACE` in SQL query
- **PHP-filter mode**: Fetches last 500 records and filters in PHP
- Both should produce the same results for plate matching

---

### Test 5: Plate Normalization

#### Test Various Plate Formats
Test that the following plate formats all match correctly:

```javascript
const testPlates = [
  'ABC-123',
  'ABC 123',
  'ABC123',
  'abc-123',  // lowercase
  'abc 123',  // lowercase with space
  'abc123',   // lowercase no separator
];

// Test each format
testPlates.forEach(plate => {
  const fd = new FormData();
  fd.append('unit_plate', plate);
  fetch('https://fix360.app/dunas/dunasshelly/5/api/compare_plate.php', {
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(data => console.log(`Plate: ${plate}, Match: ${data.is_match}, Detected: ${data.detected}`));
});
```

**Expected Results:**
- All variations should normalize to "ABC123"
- Should match if "ABC123" exists in detected_plates (regardless of formatting)
- Normalization removes: spaces, hyphens, and converts to uppercase
- Only keeps: A-Z and 0-9

---

### Test 6: Database Verification

#### Check detected_plates Table
```sql
-- View recent detections
SELECT id, plate_text, captured_at, is_match, unit_id
FROM detected_plates
ORDER BY captured_at DESC
LIMIT 10;

-- Check for specific normalized plate
SELECT id, plate_text, captured_at, is_match, unit_id
FROM detected_plates
WHERE UPPER(REPLACE(REPLACE(plate_text, '-', ''), ' ', '')) = 'ABC123'
ORDER BY captured_at DESC;
```

**Expected Results:**
- `plate_text` should contain the raw text from the camera
- When a match occurs, `is_match` should be set to 1
- When a match occurs, `unit_id` should be set to the unit ID
- Only the matched record should have these flags set

---

### Test 7: Real-World Scenarios

#### Scenario A: Existing Unit with Recent Detection
1. Have the camera detect a plate (e.g., "ABC-123")
2. Wait for it to be registered in `detected_plates`
3. Go to "Registrar Entrada"
4. Select the unit with that plate
5. **Expected:** Green "Coincide" status

#### Scenario B: Existing Unit with No Recent Detection
1. Go to "Registrar Entrada"
2. Select a unit whose plate hasn't been detected recently
3. **Expected:** 
   - Shows last global detection
   - Status: "No coincide" (gray)
   - Message: "No se encontró coincidencia exacta por placa normalizada"

#### Scenario C: Quick Registration with New Plate
1. Go to "Registro Rápido"
2. Enter a new plate number
3. **Expected:**
   - Shows comparison if plate length >= 3
   - If camera detected this plate: shows match
   - If not: shows last global detection with no match

---

## Troubleshooting

### Issue: Console shows "COMPARE non-JSON"
**Solution:**
1. Check server error logs: `/path/to/error.log`
2. Look for "compare_plate error:" entries
3. Common causes:
   - Database connection issue
   - PHP syntax error
   - Missing config files

### Issue: Always shows "No coincide"
**Possible Causes:**
1. Plate not in `detected_plates` table
2. Plate format doesn't match after normalization
3. Database query issue

**Debug Steps:**
1. Enable diagnostic mode: `?diag=1`
2. Check `_diag.targetNorm` vs `_diag.matched_norm`
3. Run SQL query manually to verify data

### Issue: API returns 500 error
**Solution:**
1. Check PHP error log
2. Verify database connection
3. Ensure config files exist and are readable
4. Check file permissions

---

## Expected Console Output

### Normal Operation (No Errors)
```
🔁 mover_ftp_a_public.php ejecutado correctamente
✅ Detectadas/insertadas: 0 placas
```

### When Non-JSON Error Occurs (Should Not Happen)
```
COMPARE non-JSON: {
  url: "https://fix360.app/dunas/dunasshelly/5/api/compare_plate.php",
  status: 500,
  ct: "text/html",
  sample: "<html>..."
}
```

### When Match Found
- Green border on comparison box
- Status: "Coincide"
- Detected plate matches unit plate

### When No Match Found
- Gray border on comparison box
- Status: "No coincide"
- Shows last global detection

---

## Module Configuration

**Important:** The current implementation uses module number `5` in the absolute URL:
```javascript
const COMPARE_URL = "https://fix360.app/dunas/dunasshelly/5/api/compare_plate.php";
```

If using a different instance (e.g., module 4, 6, etc.), update the URL in:
- `/app/views/access/create.php` (line ~184)
- `/app/views/access/quick_registration.php` (line ~481)

---

## Success Criteria

All tests pass when:
- ✅ No "COMPARE non-JSON" errors in console
- ✅ Network tab shows JSON responses from compare_plate.php
- ✅ Diagnostic mode returns detailed debug information
- ✅ Plate matching works with various formats (with/without spaces/hyphens)
- ✅ Both MySQL 8+ and older versions work correctly
- ✅ User-friendly error messages appear when needed
- ✅ Green "Coincide" appears when plates match
- ✅ Gray "No coincide" appears when plates don't match

---

## Additional Notes

1. **Performance:** PHP-filter mode (MySQL < 8) limits search to last 500 records for performance
2. **Security:** Diagnostic mode can be used by anyone but doesn't expose sensitive data
3. **Logging:** All errors are logged to PHP error log with prefix "compare_plate error:"
4. **Idempotency:** Multiple calls with same data won't cause issues
5. **Backward Compatibility:** All existing functionality preserved

---

## Next Steps After Testing

If all tests pass:
1. Monitor error logs for a few days
2. Verify no "COMPARE non-JSON" errors occur
3. Check that plate matching accuracy is as expected
4. Document any edge cases discovered

If issues found:
1. Enable diagnostic mode to get detailed information
2. Check server error logs
3. Verify database has expected data
4. Test with different plate formats
