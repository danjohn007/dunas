# Invert Sequence Feature - Implementation Summary

## Overview
This implementation adds a configurable "Invertido" (Inverted) setting per Shelly device to control the relay sequence.

## What Changed

### 1. Database Schema (`config/update_invert_sequence.sql`)
Added new column to `shelly_devices` table:
- `invert_sequence` TINYINT NOT NULL DEFAULT 1
- Default value of 1 means "inverted" (off → on)
- Value of 0 means "normal" (on → off)

### 2. Backend Changes

#### ShellyAPI (`app/helpers/ShellyAPI.php`)
- Updated `relayPulse()` to use inverted sequence (off → on) by default
- Changed from: on → wait → off
- Changed to: off → wait → on
- Added descriptive logging

#### ShellyDevice Model (`app/models/ShellyDevice.php`)
- Modified `upsertBatch()` to handle `invert_sequence` field
- Added field to both INSERT and UPDATE SQL statements
- Default value: 1 (inverted) for new devices

#### ShellyActionService (`app/services/ShellyActionService.php`)
**Major Enhancement**: Toggle actions use state mapping (single call), not sequences

**Toggle Actions:**
- Open with invert=1: OFF (single call - default)
- Open with invert=0: ON (single call)
- Close with invert=1: ON (single call - default)
- Close with invert=0: OFF (single call)

**On Actions:**
- Always: ON (single call, no pre-steps regardless of invert flag)

**Off Actions:**
- Always: OFF (single call, no pre-steps regardless of invert flag)

**Pulse Actions:**
- With invert=1: ON → wait → OFF (two-step by definition)
- With invert=0: OFF → wait → ON (two-step by definition)

All actions include detailed error logging for debugging.

#### SettingsController (`app/controllers/SettingsController.php`)
- Modified `saveShellyDevices()` to process `invert_sequence` checkbox
- Converts checkbox to 1 (checked) or 0 (unchecked)
- Added to device data array before saving

### 3. Frontend Changes

#### Settings View (`app/views/settings/index.php`)
Added "Invertido (off → on)" checkbox in two places:

**Existing Device Cards:**
```php
<label class="flex items-center">
    <input type="checkbox" name="devices[<?php echo $index; ?>][invert_sequence]" 
           value="1" <?php echo isset($device['invert_sequence']) && $device['invert_sequence'] ? 'checked' : ''; ?>
           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 mr-2">
    <span class="text-sm text-gray-700">Invertido (off → on)</span>
</label>
```

**New Device Template:**
- Same checkbox but with `checked` attribute by default
- Uses orange color (text-orange-600) to distinguish from the blue "Dispositivo habilitado" checkbox

## User Experience

### Settings Page
1. Each Shelly device card now shows two checkboxes:
   - "Dispositivo habilitado" (blue) - enables/disables device
   - "Invertido (off → on)" (orange) - controls relay sequence

2. Default behavior for new devices:
   - Both checkboxes are checked
   - Device is enabled and uses inverted sequence

3. Changing the setting:
   - Uncheck "Invertido" to use normal sequence (on → off)
   - Check "Invertido" to use inverted sequence (off → on)
   - Save changes with "Guardar Dispositivos Shelly" button

### Access Control
When using the access control system:
- Entrada (Entry): Executes "open" action
  - Inverted: OFF (single call - barrera opens)
  - Normal: ON (single call)
- Salida (Exit): Executes "close" action
  - Inverted: ON (single call - barrera closes)
  - Normal: OFF (single call)

The appropriate state is mapped based on the device's `invert_sequence` setting. No double signals or sequences are used.

## Benefits

1. **Flexibility**: Each device can have its own sequence configuration
2. **Backward Compatible**: Default inverted behavior matches existing system expectations
3. **Clear Labeling**: UI clearly shows what "Invertido" means (off → on)
4. **Comprehensive Logging**: Every action logs the sequence being used for debugging
5. **Type Safety**: All values properly cast to integers to prevent type issues

## Migration Path

1. Run the SQL migration to add the column
2. Existing devices will automatically have `invert_sequence = 1` (inverted/default)
3. No changes needed to existing device configurations
4. Users can optionally modify the setting per device as needed

## Technical Notes

- Checkbox value is properly handled in PHP: `isset($d['invert_sequence']) ? 1 : 0`
- Unchecked checkboxes don't send values, so we check `isset()` to determine state
- Default value in INSERT ensures new devices have inverted sequence
- Service layer reads the flag and applies logic accordingly
- No changes needed to calling code (AccessController, etc.)

## Validation

All modified PHP files pass syntax validation:
- ✅ app/helpers/ShellyAPI.php
- ✅ app/models/ShellyDevice.php
- ✅ app/services/ShellyActionService.php
- ✅ app/controllers/SettingsController.php
- ✅ app/views/settings/index.php (PHP templating)

See `INVERT_SEQUENCE_TESTING.md` for complete testing procedures.
