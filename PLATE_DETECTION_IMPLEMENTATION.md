# Implementación de Detección de Placas y Botón de Recarga

## 📋 Resumen

Este documento describe la implementación del sistema de detección de placas vehiculares mediante el botón "Detectar Placa Nuevamente" en la vista de Registrar Entrada.

## 🎯 Funcionalidad

Cuando el usuario presiona el botón **"Detectar Placa Nuevamente"**:

1. Se ejecuta el script `public/api/mover_ftp_a_public.php` para mover imágenes desde FTP
2. Se leen las imágenes más recientes de la carpeta `public/uploads/hikvision/`
3. Se extrae la placa del nombre del archivo (formato: `TIMESTAMP_PLACA_VEHICLE_DETECTION_...jpg`)
4. Se normaliza la placa y se busca coincidencia en la tabla `units`
5. Se registra la detección en la tabla `detected_plates`
6. Se actualiza la interfaz con el resultado (coincide/no coincide)

## 🗂️ Archivos Creados

### 1. `public/api/mover_ftp_a_public.php`

Script para mover imágenes desde servidor FTP a carpeta local.

**Características:**
- Configurable mediante constantes (FTP_HOST, FTP_USER, FTP_PASS, etc.)
- Flag `FTP_ENABLED` (default: false) para habilitar/deshabilitar
- Descarga hasta 10 imágenes más recientes
- Filtra archivos con patrón `_VEHICLE_DETECTION_`

**Configuración:**
```php
define('FTP_HOST', 'localhost');
define('FTP_PORT', 21);
define('FTP_USER', 'hikvision');
define('FTP_PASS', '');
define('FTP_REMOTE_PATH', '/anpr/');
define('FTP_ENABLED', false); // Cambiar a true para habilitar
```

### 2. `public/api/detect_plate.php`

Endpoint principal que ejecuta el proceso completo de detección.

**Flujo:**
1. Verifica autenticación del usuario
2. Ejecuta `mover_ftp_a_public.php`
3. Lee archivos de `uploads/hikvision/`
4. Extrae placa usando regex: `/[_\/]([A-Z0-9]+)_VEHICLE_DETECTION/i`
5. Normaliza placa con `TextUtils::normalizePlate()`
6. Busca coincidencia en tabla `units`
7. Inserta detección en `detected_plates`
8. Retorna JSON con resultado

**Respuesta exitosa:**
```json
{
  "success": true,
  "plate_detected": "ABC123",
  "is_match": true,
  "matched_unit_id": 5,
  "matched_unit": {
    "id": 5,
    "plate_number": "ABC-123",
    "brand": "Kenworth",
    "model": "T800"
  },
  "detection_id": 42,
  "image_file": "20251110134110161_ABC123_VEHICLE_DETECTION_Hik__VEHICLE.jpg"
}
```

**Respuesta con error:**
```json
{
  "success": false,
  "error": "No se encontraron imágenes recientes en la carpeta de detección"
}
```

### 3. `app/models/DetectedPlates.php`

Modelo para gestionar detecciones de placas.

**Métodos:**
- `insert($db, $plate, $unitId, $confidence, $deviceId)`: Inserta nueva detección
- `getLatest($db)`: Obtiene la última detección registrada
- `findUnitByPlate($db, $normalizedPlate)`: Busca unidad por placa normalizada

### 4. Actualización de `app/views/access/create.php`

**Cambios:**
- Nueva función `loadPlateDetectionWithRefresh()` para el botón
- Mantiene función existente `loadPlateDetection()` para carga automática
- Actualización de UI con estados de carga y resultados

## 📁 Estructura de Directorios

```
/public/
 ├── api/
 │   ├── detect_plate.php          ← Nuevo endpoint
 │   ├── mover_ftp_a_public.php    ← Nuevo script FTP
 │   └── anpr/
 │       └── latest.php            ← Existente (sin cambios)
 └── uploads/
     └── hikvision/                ← Nueva carpeta para imágenes
         └── .gitkeep

/app/
 ├── models/
 │   └── DetectedPlates.php        ← Nuevo modelo
 ├── helpers/
 │   └── TextUtils.php             ← Existente (sin cambios)
 └── views/
     └── access/
         └── create.php            ← Actualizado
```

## 🔍 Patrón de Nombres de Archivo

Las imágenes de Hikvision deben seguir este formato:

```
YYYYMMDDHHMMSSMMM_PLACA_VEHICLE_DETECTION_EXTRAS.jpg
```

**Ejemplos:**
- `20251110134110161_ABC123_VEHICLE_DETECTION_Hik__VEHICLE.jpg`
- `20251110145230450_SIS987P_VEHICLE_DETECTION_Hik__VEHICLE.jpg`
- `20251110160015789_XYZ999_VEHICLE_DETECTION_Front.jpeg`

El regex extrae la placa (parte entre guiones bajos antes de `_VEHICLE_DETECTION`).

## 🔧 Configuración Inicial

### 1. Crear directorio para imágenes

```bash
mkdir -p public/uploads/hikvision
chmod 755 public/uploads/hikvision
```

### 2. Configurar FTP (opcional)

Editar `public/api/mover_ftp_a_public.php`:

```php
define('FTP_HOST', '192.168.1.100');    // IP de la cámara o servidor FTP
define('FTP_PORT', 21);
define('FTP_USER', 'admin');             // Usuario FTP
define('FTP_PASS', 'tu_password');       // Password FTP
define('FTP_REMOTE_PATH', '/anpr/');     // Ruta remota
define('FTP_ENABLED', true);             // Habilitar FTP
```

### 3. Verificar tabla `detected_plates`

La tabla debe existir en la base de datos (creada por `config/02_create_detected_plates.sql`):

```sql
CREATE TABLE IF NOT EXISTS detected_plates (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  plate_text      VARCHAR(20) NOT NULL,
  confidence      DECIMAL(5,2) NULL,
  captured_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  device_id       INT NULL,
  unit_id         INT NULL,
  is_match        TINYINT(1) DEFAULT 0,
  payload_json    JSON NULL,
  status          ENUM('new','processed') DEFAULT 'new',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_plate_text (plate_text),
  INDEX idx_captured_at (captured_at DESC),
  FOREIGN KEY (device_id) REFERENCES hikvision_devices(id) ON DELETE SET NULL,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

## 🧪 Pruebas

### Prueba Manual

1. **Colocar imagen de prueba:**
   ```bash
   # Crear archivo de prueba con nombre válido
   touch public/uploads/hikvision/20251110120000000_TEST123_VEHICLE_DETECTION_Test.jpg
   ```

2. **Acceder a la vista:**
   - Ir a `/access/create`
   - Seleccionar una unidad
   - Presionar "Detectar Placa Nuevamente"

3. **Verificar resultado:**
   - Debe mostrar la placa detectada (TEST123)
   - Debe indicar si coincide con la unidad seleccionada
   - Verificar en la base de datos: `SELECT * FROM detected_plates ORDER BY id DESC LIMIT 1;`

### Prueba con FTP Habilitado

1. Configurar FTP en `mover_ftp_a_public.php`
2. Colocar imágenes en el servidor FTP
3. Presionar botón y verificar que descarga imágenes

## 🔒 Seguridad

- ✅ Verificación de autenticación (requiere login)
- ✅ Verificación de roles (admin, supervisor, operator)
- ✅ Validación de entrada (regex para extracción de placa)
- ✅ Escape de salida JSON
- ✅ Manejo de errores con try-catch
- ✅ Logs de errores con `error_log()`

## 🐛 Troubleshooting

### Error: "No se encontraron imágenes recientes"

**Causa:** No hay archivos en `public/uploads/hikvision/` con el patrón esperado.

**Solución:**
1. Verificar que el directorio existe
2. Verificar que los archivos tienen el formato correcto
3. Verificar permisos del directorio (755 o superior)

### Error: "No se pudo extraer la placa"

**Causa:** El nombre del archivo no sigue el formato esperado.

**Solución:**
1. Verificar formato: `*_PLACA_VEHICLE_DETECTION_*.jpg`
2. La placa debe estar entre guiones bajos
3. Debe contener solo caracteres alfanuméricos

### FTP no descarga archivos

**Causa:** FTP deshabilitado o configuración incorrecta.

**Solución:**
1. Verificar `FTP_ENABLED = true`
2. Verificar credenciales FTP
3. Verificar conectividad de red
4. Revisar logs de error en servidor web

## 📊 Integración con Sistema Existente

Esta implementación es compatible con:

- ✅ Sistema ANPR existente (`HikvisionAnprService`)
- ✅ Endpoint `/api/anpr/latest.php` (sin cambios)
- ✅ Tabla `detected_plates` existente
- ✅ Helper `TextUtils` para normalización
- ✅ Sistema de autenticación y roles

La nueva funcionalidad coexiste con el sistema existente sin afectarlo.

## 🎨 Interfaz de Usuario

El botón "Detectar Placa Nuevamente" muestra:

- **Cargando:** Spinner + "Detectando..."
- **Éxito con coincidencia:** Placa en verde + "✔ Coincide con registro"
- **Éxito sin coincidencia:** Placa en amarillo + "⚠ No coincide"
- **Error:** Placa en rojo + mensaje de error

## 📝 Notas

- El script FTP está diseñado para ser seguro con `FTP_ENABLED = false` por defecto
- Las imágenes se ordenan por fecha de modificación (más recientes primero)
- La normalización de placas elimina espacios, guiones y caracteres especiales
- La comparación de placas es case-insensitive
