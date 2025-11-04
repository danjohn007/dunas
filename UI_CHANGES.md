# UI Changes - Invertido Checkbox

## Settings Page Changes

### Before
Device configuration cards showed only:
- Device name
- Auth token
- Device ID
- Server host
- Active channel (radio buttons)
- ✓ Dispositivo habilitado (checkbox)

### After
Device configuration cards now show:
- Device name
- Auth token
- Device ID
- Server host
- Active channel (radio buttons)
- ✓ Dispositivo habilitado (checkbox) - **BLUE**
- ✓ Invertido (off → on) (checkbox) - **ORANGE** ← NEW

## Visual Changes

### Existing Device Card
```
┌─────────────────────────────────────────────────┐
│  Shelly Device Card                        [×]  │
│                                                  │
│  Token: ••••••••••••••••••••                    │
│  Device ID: 34987A67DA6C                        │
│  Server: shelly-208-eu.shelly.cloud            │
│  Name: Abrir/Cerrar                            │
│                                                  │
│  Puerto activo:  ○ 0  ○ 1  ○ 2  ○ 3           │
│                                                  │
│  ☑ Dispositivo habilitado      (blue)          │
│  ☑ Invertido (off → on)        (orange) ← NEW  │
└─────────────────────────────────────────────────┘
```

### New Device Template
When clicking "Nuevo dispositivo +", both checkboxes are **checked by default**:
- ☑ Dispositivo habilitado (enabled)
- ☑ Invertido (off → on) (inverted sequence)

## Color Scheme
- **Blue** (`text-blue-600`) - Used for "Dispositivo habilitado" checkbox
- **Orange** (`text-orange-600`) - Used for "Invertido (off → on)" checkbox

This color distinction helps users quickly identify:
- Blue = Enable/disable device functionality
- Orange = Relay sequence configuration

## User Interaction

### To Use Inverted Sequence (off → on) - Default
1. Keep "Invertido (off → on)" checkbox **checked**
2. Save the device configuration
3. Entry actions will execute: OFF → ON
4. Exit actions will execute: ON → OFF

### To Use Normal Sequence (on → off)
1. **Uncheck** "Invertido (off → on)" checkbox
2. Save the device configuration
3. Entry actions will execute: ON → OFF
4. Exit actions will execute: OFF → ON

## Form Data

### HTML Structure
```html
<div class="space-y-2">
    <label class="flex items-center">
        <input type="checkbox" 
               name="devices[0][is_enabled]" 
               value="1" 
               checked
               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
        <span class="text-sm text-gray-700">Dispositivo habilitado</span>
    </label>
    
    <label class="flex items-center">
        <input type="checkbox" 
               name="devices[0][invert_sequence]" 
               value="1" 
               checked
               class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 mr-2">
        <span class="text-sm text-gray-700">Invertido (off → on)</span>
    </label>
</div>
```

### POST Data
When form is submitted:
- Checked: `devices[0][invert_sequence] = "1"`
- Unchecked: Field is not sent (handled by `isset()` check)

## Responsive Design
The checkboxes maintain the existing responsive layout:
- Mobile: Stacked vertically with proper spacing
- Desktop: Same vertical stack for consistency
- Both maintain proper touch targets for mobile devices

## Accessibility
- Labels are properly associated with checkboxes
- Text clearly indicates what checking means: "Invertido (off → on)"
- Color is not the only differentiator (text labels are present)
- Standard HTML checkbox semantics for screen readers
