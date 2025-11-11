# Plate Comparison Feature

## Overview
This feature implements real-time license plate comparison between detected plates (from ANPR cameras) and registered unit plates in the system.

## Components

### 1. API Endpoint
**File:** `/public/api/compare_plate.php`

**Purpose:** Compares the latest detected plate with a specified unit plate.

**Request Parameters:**
- `unit_id` (optional): ID of the unit to compare
- `unit_plate` (optional): Direct plate text to compare

**Response:**
```json
{
  "success": true,
  "detected": "ABC123",
  "unit_plate": "ABC-123",
  "is_match": true,
  "captured_at": "2025-01-15 10:30:00"
}
```

**Features:**
- Fetches the latest detected plate from `detected_plates` table
- Normalizes both plates using `TextUtils::normalizePlate()`
- Updates `detected_plates.is_match` and `unit_id` fields
- Returns comparison result

### 2. JavaScript Library
**File:** `/public/assets/js/plate-compare.js`

**Functions:**
- `comparePlate({ unitId, unitPlate, compareUrl })`: Calls the API endpoint
- `renderPlateComparison({ detected, unitPlate, isMatch, detectedEl, statusEl, containerEl })`: Updates UI

**Usage:**
```javascript
const data = await PlateCompare.comparePlate({ 
  unitId: 123, 
  compareUrl: '/api/compare_plate.php' 
});

PlateCompare.renderPlateComparison({
  detected: data.detected,
  unitPlate: data.unit_plate,
  isMatch: data.is_match,
  detectedEl: document.querySelector('#plate-detected-text'),
  statusEl: document.querySelector('#plate-compare-status'),
  containerEl: document.querySelector('#plate-compare-box')
});
```

### 3. Integration in "Registrar Entrada"
**File:** `/app/views/access/create.php`

**Features:**
- Automatically compares plates when a unit is selected
- Updates every 8 seconds
- Manual refresh button available
- Visual feedback: green border for match, gray for no match

**UI Elements:**
- `#plate-saved-text`: Displays registered unit plate
- `#plate-detected-text`: Displays detected plate
- `#plate-compare-status`: Shows match status
- `#plate-compare-box`: Container with border color feedback

### 4. Integration in "Registro Rápido"
**File:** `/app/views/access/quick_registration.php`

**Features:**
- Compares plate as user types (with debounce)
- Updates every 8 seconds
- Shows comparison when plate length >= 3 characters
- Same visual feedback as "Registrar Entrada"

## CSS Styles
**File:** `/app/views/layouts/main.php`

```css
#plate-compare-box.match-ok  { border-color: #16a34a !important; }  /* green */
#plate-compare-box.match-bad { border-color: #9ca3af !important; }  /* gray */
```

## Database Updates
The feature updates the following fields in `detected_plates` table:
- `is_match`: Set to 1 if plates match, 0 otherwise
- `unit_id`: Set to the matching unit's ID if match found

## Security
- Authentication required: All API endpoints check if user is logged in
- Uses prepared statements to prevent SQL injection
- Proper error handling with try-catch blocks

## Performance Considerations
- Debounce on input (500ms) to avoid excessive API calls
- Periodic refresh (8 seconds) runs in background without blocking UI
- Only updates latest detection record to minimize database writes

## Future Enhancements
1. Add database indexes for optimization:
   ```sql
   CREATE INDEX idx_detected_plates_captured_at ON detected_plates (captured_at DESC);
   CREATE INDEX idx_units_plate_number ON units (plate_number);
   ```

2. Consider adding a `plate_number_norm` column to units table for faster lookups

3. Add configuration option to adjust refresh interval

4. Add sound/notification when match is detected
