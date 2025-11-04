# Invert Sequence Feature - Testing Guide

## Overview
This feature allows configuring Shelly devices to use either:
- **Invertido (default)**: off → on sequence
- **Normal**: on → off sequence

## Database Migration

Run the SQL migration to add the `invert_sequence` column:

```bash
mysql -u [username] -p [database_name] < config/update_invert_sequence.sql
```

Or manually execute:
```sql
ALTER TABLE shelly_devices 
ADD COLUMN invert_sequence TINYINT NOT NULL DEFAULT 1 
AFTER channel_count;

UPDATE shelly_devices SET invert_sequence = 1 WHERE invert_sequence IS NULL;
```

## Testing Checklist

### 1. Database Migration
- [ ] Run the migration SQL script
- [ ] Verify `invert_sequence` column exists in `shelly_devices` table
- [ ] Verify existing devices have `invert_sequence = 1` (default inverted)

### 2. UI Testing (Settings Page)
- [ ] Navigate to Settings page (`/settings`)
- [ ] Verify each device card shows "Invertido (off → on)" checkbox
- [ ] Verify existing devices have the checkbox checked by default
- [ ] Click "Nuevo dispositivo +" and verify new device template has checkbox checked
- [ ] Save devices and verify checkbox state is preserved

### 3. Action Testing - Toggle with Invertido ON (default)
When `invert_sequence = 1`:
- [ ] **Open action**: Should execute OFF → ON
  - Check logs for: "Toggle OPEN con inversión: OFF → ON"
- [ ] **Close action**: Should execute ON → OFF
  - Check logs for: "Toggle CLOSE con inversión: ON → OFF"

### 4. Action Testing - Toggle with Invertido OFF
When `invert_sequence = 0`:
- [ ] Uncheck "Invertido (off → on)" in Settings
- [ ] Save the device configuration
- [ ] **Open action**: Should execute ON → OFF
  - Check logs for: "Toggle OPEN sin inversión: ON → OFF"
- [ ] **Close action**: Should execute OFF → ON
  - Check logs for: "Toggle CLOSE sin inversión: OFF → ON"

### 5. Action Testing - ON
- [ ] **With Invertido ON**: Should execute OFF → ON
  - Check logs for: "ON con inversión: OFF → ON"
- [ ] **With Invertido OFF**: Should execute only ON (single command)

### 6. Action Testing - OFF
- [ ] **With Invertido ON**: Should execute ON → OFF
  - Check logs for: "OFF con inversión: ON → OFF"
- [ ] **With Invertido OFF**: Should execute only OFF (single command)

### 7. Action Testing - PULSE
- [ ] **With Invertido ON**: Should execute OFF → wait → ON
  - Check logs for: "PULSE con inversión: OFF → ON"
- [ ] **With Invertido OFF**: Should execute ON → wait → OFF
  - Check logs for: "PULSE sin inversión: ON → OFF"

### 8. Multi-Device Testing
- [ ] Configure multiple devices with different `invert_sequence` values
- [ ] Verify each device respects its own setting independently
- [ ] Verify actions on different devices work correctly

## Expected Behavior Summary

| Action | Mode  | Invertido ON (1)     | Invertido OFF (0)    |
|--------|-------|----------------------|----------------------|
| toggle | open  | OFF → ON             | ON → OFF             |
| toggle | close | ON → OFF             | OFF → ON             |
| on     | -     | OFF → ON             | ON                   |
| off    | -     | ON → OFF             | OFF                  |
| pulse  | -     | OFF → wait → ON      | ON → wait → OFF      |

## Log Files
Check `/logs` directory for error logs with action execution details:
- `ShellyActionService::execute()` logs show action, mode, and invert flag
- Specific logs for each action type show the sequence being executed

## Rollback
If issues occur, you can rollback the migration:
```sql
ALTER TABLE shelly_devices DROP COLUMN invert_sequence;
```

Note: This will require reverting code changes to avoid errors.
