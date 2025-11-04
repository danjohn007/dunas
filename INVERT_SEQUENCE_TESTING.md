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
- [ ] **Open action**: Should execute OFF (single call only)
  - Check logs for: "TOGGLE OPEN (invertido) => OFF"
- [ ] **Close action**: Should execute ON (single call only)
  - Check logs for: "TOGGLE CLOSE (invertido) => ON"

### 4. Action Testing - Toggle with Invertido OFF
When `invert_sequence = 0`:
- [ ] Uncheck "Invertido (off → on)" in Settings
- [ ] Save the device configuration
- [ ] **Open action**: Should execute ON (single call only)
  - Check logs for: "TOGGLE OPEN (normal) => ON"
- [ ] **Close action**: Should execute OFF (single call only)
  - Check logs for: "TOGGLE CLOSE (normal) => OFF"

### 5. Action Testing - ON
- [ ] **Always**: Should execute only ON (single call, no pre-steps)
  - Check logs for: "ON"
  - No difference between Invertido ON or OFF

### 6. Action Testing - OFF
- [ ] **Always**: Should execute only OFF (single call, no pre-steps)
  - Check logs for: "OFF"
  - No difference between Invertido ON or OFF

### 7. Action Testing - PULSE
- [ ] **With Invertido ON**: Should execute ON → wait → OFF
  - Check logs for: "PULSE invertido: ON→OFF"
- [ ] **With Invertido OFF**: Should execute OFF → wait → ON
  - Check logs for: "PULSE normal: OFF→ON"

### 8. Multi-Device Testing
- [ ] Configure multiple devices with different `invert_sequence` values
- [ ] Verify each device respects its own setting independently
- [ ] Verify actions on different devices work correctly

## Expected Behavior Summary

| Action | Mode  | Invertido ON (1)     | Invertido OFF (0)    |
|--------|-------|----------------------|----------------------|
| toggle | open  | OFF (single)         | ON (single)          |
| toggle | close | ON (single)          | OFF (single)         |
| on     | -     | ON (single)          | ON (single)          |
| off    | -     | OFF (single)         | OFF (single)         |
| pulse  | -     | ON → wait → OFF      | OFF → wait → ON      |

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
