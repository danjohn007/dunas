# Cambios Visuales en la UI - Vista Previa

## Antes vs Después

### ANTES (Vista Anterior)
```
┌─────────────────────────────────────────────────────────────┐
│  Dispositivos Shelly Cloud                                  │
│                                           [+ Nuevo dispositivo]│
├─────────────────────────────────────────────────────────────┤
│  Dispositivo #1                                        [✕]  │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Token de Autenticación: [********************]  👁  │   │
│  │ Device ID: [34987A67DA6C]                          │   │
│  │ Servidor Cloud: [shelly-208-eu.shelly.cloud]       │   │
│  │ Nombre: [Abrir/Cerrar ▼]                           │   │  ← ANTES: "Nombre"
│  │                                                      │   │
│  │ Puerto activo: ○ 0  ○ 1  ○ 2  ○ 3                 │   │
│  │                                                      │   │
│  │ ☑ Dispositivo habilitado                           │   │
│  │ ☑ Invertido (off → on)                            │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

### DESPUÉS (Nueva Vista)
```
┌─────────────────────────────────────────────────────────────┐
│  Dispositivos Shelly Cloud                                  │
│                                           [+ Nuevo dispositivo]│
├─────────────────────────────────────────────────────────────┤
│  Dispositivo #1                                        [✕]  │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Token de Autenticación: [********************]  👁  │   │
│  │ Device ID: [34987A67DA6C]                          │   │
│  │ Servidor Cloud: [shelly-208-eu.shelly.cloud]       │   │
│  │ Acción: [Abrir/Cerrar ▼]                           │   │  ← AHORA: "Acción"
│  │ Área: [Entrada principal___________________]        │   │  ← NUEVO CAMPO
│  │                                                      │   │
│  │ Puerto activo: ○ 0  ○ 1  ○ 2  ○ 3                 │   │
│  │                                                      │   │
│  │ ☑ Dispositivo habilitado                           │   │
│  │ ☑ Invertido (off → on)                            │   │
│  │ ☑ Dispositivo simultáneo                           │   │  ← NUEVO CHECKBOX
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Detalle de Cambios UI

### 1. Campo "Acción" (antes "Nombre")
**Ubicación**: Entre "Servidor Cloud" y "Área"

**Código HTML**:
```html
<label class="block text-sm font-medium text-gray-700 mb-2">
    Acción
</label>
<select name="devices[i][action_code]" 
        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    <option value="abrir_cerrar">Abrir/Cerrar</option>
    <option value="vacio">Vacío</option>
</select>
```

**Opciones**:
- Abrir/Cerrar (valor: `abrir_cerrar`)
- Vacío (valor: `vacio`)

---

### 2. Campo "Área" (NUEVO)
**Ubicación**: Después de "Acción", antes de "Puerto activo"

**Código HTML**:
```html
<label class="block text-sm font-medium text-gray-700 mb-2">
    Área
</label>
<input type="text" name="devices[i][area]" 
       value=""
       placeholder="Ej: Entrada principal"
       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
```

**Características**:
- Campo de texto libre
- No requerido (opcional)
- Placeholder: "Ej: Entrada principal"
- Valor se guarda en DB como VARCHAR(100)
- Solo informativo, no afecta lógica

**Ejemplos de uso**:
- "Entrada principal"
- "Salida de emergencia"
- "Puerta trasera"
- "Zona de carga"

---

### 3. Checkbox "Dispositivo simultáneo" (NUEVO)
**Ubicación**: Después de "Invertido (off → on)"

**Código HTML**:
```html
<label class="flex items-center">
    <input type="checkbox" name="devices[i][is_simultaneous]" 
           value="1"
           class="rounded border-gray-300 text-green-600 focus:ring-green-500 mr-2">
    <span class="text-sm text-gray-700">Dispositivo simultáneo</span>
</label>
```

**Características**:
- Checkbox estándar
- Color verde (text-green-600)
- Por defecto NO marcado
- Valor en DB: 1 (marcado) o 0 (no marcado)

**Comportamiento**:
- ✅ Marcado: Se ejecuta con otros dispositivos simultáneos de la misma acción
- ❌ No marcado: Se ejecuta solo si no hay ningún simultáneo

---

## Layout de Grid

La nueva disposición de campos usa un grid de 2 columnas:

```
┌───────────────────────────────────────────────┐
│  Row 1:  [Token Auth]    [Device ID]         │
│  Row 2:  [Server Host]   [Acción]            │
│  Row 3:                  [Área]              │  ← Nueva fila
└───────────────────────────────────────────────┘
```

## Colores y Estilos

### Campo "Área"
- **Border**: gray-300
- **Focus**: blue-500 (border y ring)
- **Text**: Por defecto (negro)

### Checkbox "Dispositivo simultáneo"
- **Color**: green-600 (checked)
- **Focus ring**: green-500
- **Border**: gray-300 (unchecked)

### Comparación con otros checkboxes
```
☑ Dispositivo habilitado    → Azul (blue-600)
☑ Invertido (off → on)       → Naranja (orange-600)
☑ Dispositivo simultáneo     → Verde (green-600)  ← NUEVO
```

## Responsive Design

El grid mantiene el diseño responsive existente:

**Desktop (md:grid-cols-2)**:
```
[Token Auth]        [Device ID]
[Server Host]       [Acción]
                    [Área]
```

**Mobile (grid-cols-1)**:
```
[Token Auth]
[Device ID]
[Server Host]
[Acción]
[Área]
```

## Ejemplo Completo de Formulario

```html
<!-- Dispositivo Shelly #1 -->
<div class="shelly-device-card bg-gray-50 border border-gray-300 rounded-lg p-6">
    <!-- Grid principal -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <!-- Token de Autenticación -->
        <div>
            <label>Token de Autenticación</label>
            <input type="password" name="devices[0][auth_token]" 
                   value="token123..." class="...">
        </div>
        
        <!-- Device ID -->
        <div>
            <label>Device ID</label>
            <input type="text" name="devices[0][device_id]" 
                   value="ABC123" class="...">
        </div>
        
        <!-- Servidor Cloud -->
        <div>
            <label>Servidor Cloud</label>
            <input type="text" name="devices[0][server_host]" 
                   value="shelly-208-eu.shelly.cloud" class="...">
        </div>
        
        <!-- ✨ NUEVO: Acción (antes "Nombre") -->
        <div>
            <label>Acción</label>
            <select name="devices[0][action_code]" class="...">
                <option value="abrir_cerrar">Abrir/Cerrar</option>
                <option value="vacio">Vacío</option>
            </select>
        </div>
        
        <!-- ✨ NUEVO: Área -->
        <div>
            <label>Área</label>
            <input type="text" name="devices[0][area]" 
                   placeholder="Ej: Entrada principal" class="...">
        </div>
    </div>
    
    <!-- Puerto activo (radios) -->
    <div class="mb-2">
        <label>Puerto activo:</label>
        <div class="flex space-x-6">
            <label><input type="radio" name="devices[0][active_channel]" value="0"> 0</label>
            <label><input type="radio" name="devices[0][active_channel]" value="1"> 1</label>
            <label><input type="radio" name="devices[0][active_channel]" value="2"> 2</label>
            <label><input type="radio" name="devices[0][active_channel]" value="3"> 3</label>
        </div>
    </div>
    
    <!-- Checkboxes -->
    <div class="space-y-2">
        <label class="flex items-center">
            <input type="checkbox" name="devices[0][is_enabled]" value="1" checked>
            <span>Dispositivo habilitado</span>
        </label>
        
        <label class="flex items-center">
            <input type="checkbox" name="devices[0][invert_sequence]" value="1" checked>
            <span>Invertido (off → on)</span>
        </label>
        
        <!-- ✨ NUEVO: Dispositivo simultáneo -->
        <label class="flex items-center">
            <input type="checkbox" name="devices[0][is_simultaneous]" value="1">
            <span>Dispositivo simultáneo</span>
        </label>
    </div>
</div>
```

## Estado Visual de los Elementos

### Estado Normal
```
Área: [________________________]  ← Border gris, fondo blanco
```

### Estado Focus
```
Área: [========================]  ← Border azul, ring azul brillante
      ↑ cursor aquí
```

### Estado con Valor
```
Área: [Entrada principal______]  ← Texto negro, fondo blanco
```

## Validación Visual

**Campo Área**:
- ✅ No hay validación de formato
- ✅ Acepta cualquier texto (hasta 100 caracteres en DB)
- ✅ Puede quedar vacío

**Checkbox Simultáneo**:
- ✅ Solo dos estados: marcado/no marcado
- ✅ No hay validación adicional
- ✅ Funciona independiente de otros checkboxes

## Notas de Implementación

1. **Preservación de datos**: Al recargar la página, los valores guardados se mantienen
2. **Template de nuevo dispositivo**: Incluye todos los campos nuevos con valores vacíos
3. **Compatibilidad**: Dispositivos antiguos muestran área vacía y simultáneo no marcado
4. **Accesibilidad**: Labels correctamente asociados con inputs

## Resultado Final

La UI ahora muestra claramente:
- 🔄 **Acción** del dispositivo (antes era confuso llamarlo "Nombre")
- 📍 **Área** para identificar ubicación (informativo)
- ⚡ **Dispositivo simultáneo** para ejecución coordinada

Todo con diseño limpio, consistente con el resto del sistema, y usando TailwindCSS.
