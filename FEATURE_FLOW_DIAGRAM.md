# Plate Comparison Feature - Flow Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        DUNAS System                              │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   ANPR Camera   │───▶│ detected_plates  │◀───│ compare_plate   │
│                 │    │     (Table)      │    │  API Endpoint   │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                              │                        ▲
                              │                        │
                              ▼                        │
                       ┌──────────────┐                │
                       │    units     │                │
                       │   (Table)    │                │
                       └──────────────┘                │
                                                       │
┌──────────────────────────────────────────────────────┘
│
│  JavaScript Libraries & UI Views
│
├─ plate-compare.js (shared library)
│  ├─ comparePlate()
│  └─ renderPlateComparison()
│
├─ create.php (Registrar Entrada)
│  └─ Auto-compare on unit selection
│
└─ quick_registration.php (Registro Rápido)
   └─ Real-time compare as user types
```

---

## Flow Diagram: Registrar Entrada (Create Entry)

```
User Action                  System Response                 Database
───────────                  ───────────────                 ────────

1. Select Client
      │
      ├──────────────────▶ Load units for client
      │                                                    
2. Select Unit              Display unit plate             
      │                    in "Placa Guardada"            
      │                           │
      │                           ▼
      │                    Call compare_plate.php
      │                    with unit_id
      │                           │
      │                           ▼                        ┌──────────────┐
      │                    Query latest detection     ───▶│detected_plates│
      │                           │                        └──────────────┘
      │                           ▼
      │                    Query unit plate            ───▶┌──────────────┐
      │                           │                        │    units     │
      │                           ▼                        └──────────────┘
      │                    Normalize both plates
      │                    (TextUtils::normalizePlate)
      │                           │
      │                           ▼
      │                    Compare normalized plates
      │                           │
      │                           ├─ Match? ──▶ is_match = 1
      │                           │                │
      │                           └─ No Match ──▶ is_match = 0
      │                                            │
      │                    Update detected_plates  ▼        ┌──────────────┐
      │                    with match result      ────────▶│detected_plates│
      │                                                     │ UPDATE query │
      │                           │                        └──────────────┘
      │                           ▼
      │                    Return JSON response
      │                    { detected, unit_plate, 
      │                      is_match, captured_at }
      │                           │
      │                           ▼
      │                    JavaScript updates UI:
      │                    - Display detected plate
      │                    - Show match status
      │                    - Apply border color:
      │                      • Green = match
      │                      • Gray = no match
      │
      │◀───────────────────────────┘
      │
3. View Comparison
   Result
      │
      │                    Every 8 seconds:
      │                    ├─ Auto-refresh comparison
      │                    └─ Update UI if changed
      │
4. Continue with
   registration
```

---

## Flow Diagram: Registro Rápido (Quick Registration)

```
User Action                  System Response                 Database
───────────                  ───────────────                 ────────

1. Type plate number
   (e.g., "ABC")
      │
      │◀───────────────────▶ Input debounce (500ms)
      │
2. After debounce           Show comparison box
   (3+ characters)          Set "Placa Ingresada"
      │                           │
      │                           ▼
      │                    Call compare_plate.php
      │                    with unit_plate
      │                           │
      │                           ▼                        ┌──────────────┐
      │                    Query latest detection     ───▶│detected_plates│
      │                           │                        └──────────────┘
      │                           ▼
      │                    Normalize both plates
      │                    (client-side + server-side)
      │                           │
      │                           ▼
      │                    Compare normalized plates
      │                           │
      │                           ├─ Match? ──▶ is_match = 1
      │                           │                │
      │                           └─ No Match ──▶ is_match = 0
      │                                            │
      │                    Update detected_plates  ▼        ┌──────────────┐
      │                    (if unit exists)       ────────▶│detected_plates│
      │                                                     └──────────────┘
      │                           │
      │                           ▼
      │                    Return JSON response
      │                           │
      │                           ▼
      │                    JavaScript updates UI:
      │                    - Display detected plate
      │                    - Show match status
      │                    - Apply border color
      │
      │◀───────────────────────────┘
      │
3. Continue typing          Re-trigger comparison
   more characters          after debounce
      │
      │                    Every 8 seconds:
      │                    ├─ Auto-refresh comparison
      │                    └─ Update UI if changed
      │
4. Search unit and
   continue registration
```

---

## Data Flow: Plate Normalization

```
Input Plate          Normalization Steps               Output
───────────          ───────────────────               ──────

"ABC-123"       ──▶  1. trim()                    
                     2. strtoupper()        ──▶  "ABC-123"
                     3. Remove [^A-Z0-9]    ──▶  "ABC123"

"abc 123"       ──▶  1. trim()
                     2. strtoupper()        ──▶  "ABC 123"
                     3. Remove [^A-Z0-9]    ──▶  "ABC123"

"AB C-1 23"     ──▶  1. trim()
                     2. strtoupper()        ──▶  "AB C-1 23"
                     3. Remove [^A-Z0-9]    ──▶  "ABC123"

Result: All three inputs normalize to "ABC123" → MATCH ✓
```

---

## API Request/Response Flow

### Request
```
POST /public/api/compare_plate.php
Content-Type: application/x-www-form-urlencoded

# Option 1: By unit ID
unit_id=123

# Option 2: By plate text
unit_plate=ABC-123
```

### Processing
```
1. Authenticate user (Auth::isLoggedIn())
   │
   ├─ Not logged in? → Return 401
   └─ Logged in? → Continue
   
2. Get latest detection
   │
   ├─ No detection? → Return { detected: null }
   └─ Has detection? → Continue
   
3. Get unit plate
   │
   ├─ By unit_id? → Query units table
   ├─ By unit_plate? → Use provided value
   └─ None provided? → Return { is_match: false }
   
4. Normalize both plates
   │
   └─ TextUtils::normalizePlate()
   
5. Compare normalized plates
   │
   ├─ Equal? → is_match = true
   └─ Different? → is_match = false
   
6. Update detected_plates table
   │
   └─ SET is_match = ?, unit_id = ?
   
7. Return JSON response
```

### Response
```json
{
  "success": true,
  "detected": "ABC123",
  "unit_plate": "ABC-123",
  "is_match": true,
  "captured_at": "2025-01-15 10:30:00"
}
```

---

## UI Update Flow

```
JSON Response          JavaScript Processing          UI Updates
─────────────          ─────────────────────          ──────────

{ detected, 
  unit_plate,      ──▶  PlateCompare
  is_match,              .renderPlateComparison()
  captured_at }                   │
                                  ├──▶ Update #plate-detected-text
                                  │    with detected value
                                  │
                                  ├──▶ Update #plate-compare-status
                                  │    "Coincide ✔" or "No coincide"
                                  │
                                  └──▶ Update #plate-compare-box
                                       Add class:
                                       • .match-ok (green) if match
                                       • .match-bad (gray) if no match
```

---

## Error Handling Flow

```
Error Scenario              System Response              User Sees
──────────────              ───────────────              ─────────

No authentication      ──▶  HTTP 401                ──▶  [Redirected to login]
                           { success: false,
                             error: "No autenticado" }

No detections         ──▶  HTTP 200                ──▶  "Sin detección"
                           { success: true,
                             detected: null }

Database error        ──▶  HTTP 500                ──▶  "Error"
                           { success: false,             "No se pudo comparar"
                             error: "..." }

Network timeout       ──▶  JavaScript catch        ──▶  "Error"
                                                         "No se pudo comparar"
```

---

## Performance Optimization Points

```
┌──────────────────────────────────────────────────────────┐
│  Client-Side Optimizations                               │
├──────────────────────────────────────────────────────────┤
│  1. Input debounce (500ms) - Reduce API calls           │
│  2. Background refresh (8s) - Non-blocking               │
│  3. Conditional rendering - Only show when needed        │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│  Server-Side Optimizations                               │
├──────────────────────────────────────────────────────────┤
│  1. Query only latest detection (LIMIT 1)                │
│  2. Prepared statements (SQL optimization)               │
│  3. Update only necessary fields                         │
│  4. Minimal data transfer in JSON response               │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│  Database Optimizations (Recommended)                    │
├──────────────────────────────────────────────────────────┤
│  CREATE INDEX idx_detected_plates_captured_at            │
│      ON detected_plates (captured_at DESC);              │
│                                                           │
│  CREATE INDEX idx_units_plate_number                     │
│      ON units (plate_number);                            │
└──────────────────────────────────────────────────────────┘
```

---

## Security Layers

```
┌─────────────────────────────────────────────────────────┐
│  Layer 1: Authentication                                 │
│  ├─ Session validation                                   │
│  ├─ Auth::isLoggedIn() check                            │
│  └─ HTTP 401 if not authenticated                       │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Layer 2: Input Validation                              │
│  ├─ Type casting (int for unit_id)                      │
│  ├─ Empty check for unit_plate                          │
│  └─ TextUtils::normalizePlate() sanitization            │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Layer 3: SQL Injection Prevention                      │
│  ├─ Prepared statements (PDO)                           │
│  ├─ Parameter binding                                   │
│  └─ No direct SQL concatenation                         │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Layer 4: Error Handling                                │
│  ├─ Try-catch blocks                                    │
│  ├─ HTTP status codes                                   │
│  ├─ Generic error messages (no details leaked)          │
│  └─ Server-side logging                                 │
└─────────────────────────────────────────────────────────┘
```

---

## Testing Scenarios

```
┌────────────────────┬──────────────────────┬─────────────────┐
│  Scenario          │  Expected Behavior   │  Status         │
├────────────────────┼──────────────────────┼─────────────────┤
│  Exact match       │  Green border        │  ✅ Implemented │
│  ABC-123 = ABC123  │  "Coincide ✔"        │                 │
├────────────────────┼──────────────────────┼─────────────────┤
│  No match          │  Gray border         │  ✅ Implemented │
│  ABC-123 ≠ XYZ789  │  "No coincide"       │                 │
├────────────────────┼──────────────────────┼─────────────────┤
│  No detection      │  Shows "—"           │  ✅ Implemented │
│  (empty DB)        │  No comparison       │                 │
├────────────────────┼──────────────────────┼─────────────────┤
│  User not logged   │  Redirect to login   │  ✅ Implemented │
│  in                │  HTTP 401            │                 │
├────────────────────┼──────────────────────┼─────────────────┤
│  Database error    │  Show error message  │  ✅ Implemented │
│                    │  HTTP 500            │                 │
├────────────────────┼──────────────────────┼─────────────────┤
│  Network timeout   │  Show error message  │  ✅ Implemented │
│                    │  Graceful degradation│                 │
└────────────────────┴──────────────────────┴─────────────────┘
```

---

## Deployment Timeline

```
Phase 1: Pre-Deployment (Current)
├─ ✅ Code implementation
├─ ✅ Security scan (0 vulnerabilities)
├─ ✅ Syntax validation
├─ ✅ Documentation
└─ ✅ Ready for review

Phase 2: Deployment
├─ [ ] Code review approval
├─ [ ] Merge to main branch
├─ [ ] Deploy to production server
└─ [ ] Verify file permissions

Phase 3: Post-Deployment
├─ [ ] Test on production environment
├─ [ ] Train operators on new feature
├─ [ ] Monitor for errors
└─ [ ] (Optional) Add recommended indexes

Phase 4: Optimization (Future)
├─ [ ] Add database indexes
├─ [ ] Consider plate_number_norm column
├─ [ ] Add configuration options
└─ [ ] Add sound notifications
```

---

**Diagram Version:** 1.0
**Last Updated:** 2025-01-15
**Status:** Complete - Ready for Production
