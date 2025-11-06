# Implementación v1.4.0 - HikVision Devices y Canales Shelly

## Resumen de Cambios

Esta actualización agrega soporte para múltiples dispositivos HikVision (cámaras LPR y lectores de código de barras) y mejora la configuración de dispositivos Shelly con canales separados para entrada y salida.

### Características Principales

1. **Auto-llenado de Litros en Salida**
   - El campo "Litros Suministrados" se rellena automáticamente con la capacidad total de la unidad

2. **Gestión de Dispositivos HikVision**
   - Soporte para múltiples cámaras LPR (Lectura de Placas)
   - Soporte para lectores de código de barras
   - Configuración individual de cada dispositivo
   - Área/ubicación asignable a cada dispositivo

3. **Canales Separados para Shelly**
   - Canal de entrada: activa un pulso de 5 segundos para apertura
   - Canal de salida: activa otro canal para cierre
   - Duración de pulso configurable (por defecto 5000 ms)

4. **Integración con Lector de Código de Barras**
   - Endpoint API para integración con lectores HikVision
   - Apertura/cierre automático de barrera al escanear código de barras
   - Registro automático de salida al escanear ticket

## Migración de Base de Datos

### Script SQL

Ejecutar el archivo: `config/update_hikvision_shelly_channels.sql`

```bash
mysql -u usuario -p dunas_access_control < config/update_hikvision_shelly_channels.sql
```

### Cambios en la Base de Datos

#### Nueva Tabla: `hikvision_devices`

```sql
CREATE TABLE hikvision_devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  device_type ENUM('camera_lpr', 'barcode_reader'),
  api_url VARCHAR(255) NOT NULL,
  username VARCHAR(100) NULL,
  password VARCHAR(255) NULL,
  verify_ssl TINYINT NOT NULL DEFAULT 0,
  area VARCHAR(100) NULL,
  is_enabled TINYINT NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Modificaciones a `shelly_devices`

Nuevos campos agregados:
- `entry_channel` (TINYINT): Canal para apertura/entrada
- `exit_channel` (TINYINT): Canal para cierre/salida
- `pulse_duration_ms` (INT): Duración del pulso en milisegundos

## Archivos Nuevos

1. **Modelo**: `app/models/HikvisionDevice.php`
   - Gestión de dispositivos HikVision
   - CRUD para múltiples dispositivos
   - Validación y prueba de conexión

2. **Migración SQL**: `config/update_hikvision_shelly_channels.sql`
   - Creación de tabla `hikvision_devices`
   - Modificación de tabla `shelly_devices`
   - Migración de datos existentes

## Archivos Modificados

### Controladores

1. **app/controllers/AccessController.php**
   - Método `barcodeReader()`: Nuevo endpoint para integración con lectores de código de barras
   - Procesamiento automático de entrada/salida mediante escaneo de código

2. **app/controllers/SettingsController.php**
   - Método `saveHikvisionDevices()`: Guardar configuración de dispositivos HikVision
   - Actualización de método `index()`: Cargar dispositivos HikVision
   - Actualización de método `saveShellyDevices()`: Guardar nuevos campos de canales

### Modelos

1. **app/models/ShellyDevice.php**
   - Actualizado `upsertBatch()`: Soporte para nuevos campos de canales

### Helpers

1. **app/helpers/HikvisionAPI.php**
   - Método `readLicensePlate()`: Actualizado para soportar múltiples dispositivos
   - Método `readFromDevice()`: Leer desde un dispositivo específico
   - Método `readLicensePlateLegacy()`: Compatibilidad con configuración antigua
   - Método `readBarcode()`: Leer código de barras desde lector HikVision
   - Método `readBarcodeFromDevice()`: Leer desde dispositivo específico
   - Método `parseBarcodeResponse()`: Parsear respuesta de lector de código de barras

### Servicios

1. **app/services/ShellyActionService.php**
   - Método `execute()`: Actualizado para usar canales separados según modo (open/close)
   - Método `getChannelForMode()`: Determinar canal según modo de operación
   - Método `executePulse()`: Ejecutar pulso en canal especificado

### Vistas

1. **app/views/access/exit.php**
   - Auto-llenado del campo `liters_supplied` con capacidad de la unidad

2. **app/views/settings/index.php**
   - Nueva sección: Dispositivos HikVision
   - Actualización de sección Shelly: Canales de entrada/salida y duración de pulso
   - JavaScript para gestión de dispositivos HikVision

## Configuración

### Dispositivos HikVision

Para configurar dispositivos HikVision:

1. Ir a **Configuraciones del Sistema** → **Dispositivos HikVision**
2. Hacer clic en **"Nuevo dispositivo +"**
3. Completar la información:
   - **Nombre**: Nombre descriptivo del dispositivo
   - **Tipo**: Cámara LPR o Lector de Código de Barras
   - **URL de API**: URL base del dispositivo (ej: `http://192.168.1.100`)
   - **Usuario**: Usuario para autenticación (opcional)
   - **Contraseña**: Contraseña para autenticación (opcional)
   - **Área**: Ubicación física del dispositivo
   - **Verificar SSL**: Marcar si se debe verificar certificado SSL
4. Hacer clic en **"Guardar Dispositivos HikVision"**

#### Tipos de Dispositivos

**Cámara LPR (Lectura de Placas)**
- Modelo compatible: IDS-2CD7A46G0/P-IZHS(C)
- Lectura automática de placas vehiculares
- Detección de discrepancias con placa registrada
- Endpoint: `/ISAPI/Traffic/channels/1/vehicleDetect/plates`

**Lector de Código de Barras**
- Lectura de códigos de barras/QR para apertura de barrera
- Integración con tickets de acceso
- Endpoint: `/ISAPI/AccessControl/AcsEvent?format=json`

### Dispositivos Shelly

Para configurar canales en dispositivos Shelly:

1. Ir a **Configuraciones del Sistema** → **Dispositivos Shelly Cloud**
2. Editar un dispositivo existente o crear uno nuevo
3. Configurar:
   - **Canal de Entrada**: Canal a activar al registrar entrada (pulso de 5 segundos)
   - **Canal de Salida**: Canal a activar al registrar salida
   - **Duración Pulso**: Duración del pulso en milisegundos (por defecto 5000 ms)
4. Hacer clic en **"Guardar Dispositivos Shelly"**

#### Ejemplo de Configuración

**Entrada (Apertura)**:
- Canal: 0
- Acción: Pulso de 5 segundos (configurable)
- Se activa al registrar entrada de unidad

**Salida (Cierre)**:
- Canal: 1
- Acción: Activación del canal
- Se activa al registrar salida de unidad

## API Endpoints

### Lector de Código de Barras

**Endpoint**: `POST /access/barcodeReader`

Parámetros:
- `barcode` (opcional): Código de barras leído
- `deviceId` (opcional): ID del dispositivo HikVision

Respuesta exitosa:
```json
{
  "success": true,
  "message": "Salida registrada exitosamente con 10,000 litros.",
  "action": "exit",
  "barrier_closed": true,
  "access": {
    "id": 123,
    "ticket_code": "1234",
    ...
  }
}
```

Respuesta de error:
```json
{
  "success": false,
  "message": "Código de barras no válido o no encontrado",
  "barcode": "1234"
}
```

## Lógica de Negocio

### Registro de Entrada

1. Se selecciona unidad, chofer y cliente
2. Se crea registro de acceso con estado `in_progress`
3. HikVision lee placa automáticamente (si está configurado)
4. Se compara placa leída con placa registrada
5. Se marca discrepancia si hay diferencia
6. Se activa **canal de entrada** de Shelly con pulso de 5 segundos
7. Se genera ticket con código de barras

### Registro de Salida

**Método Manual**:
1. Se busca el acceso por ticket
2. Se ingresa cantidad de litros (auto-llenado con capacidad de unidad)
3. Se registra salida con estado `completed`
4. Se activa **canal de salida** de Shelly

**Método con Código de Barras**:
1. Lector HikVision escanea código de barras del ticket
2. Sistema busca acceso por código
3. Se registra salida automáticamente con capacidad máxima
4. Se activa **canal de salida** de Shelly
5. Barrera se cierra automáticamente

## Compatibilidad

### Migración desde v1.3.0

La actualización es compatible con versiones anteriores:

1. **Configuración legacy de HikVision**: Se mantiene en tabla `settings` y se migra automáticamente
2. **Dispositivos Shelly existentes**: Se actualizan con valores por defecto para nuevos campos
3. **Datos de acceso**: No se ven afectados

### Valores por Defecto

Al ejecutar la migración:
- `entry_channel` = valor de `active_channel`
- `exit_channel` = calculado basado en `active_channel` (canal diferente)
- `pulse_duration_ms` = 5000 (5 segundos)

## Pruebas

### Verificar Migración

```sql
-- Verificar tabla hikvision_devices
SELECT * FROM hikvision_devices;

-- Verificar nuevos campos en shelly_devices
SELECT id, name, entry_channel, exit_channel, pulse_duration_ms 
FROM shelly_devices;
```

### Probar Endpoints

**Lector de Código de Barras**:
```bash
curl -X POST http://localhost/access/barcodeReader \
  -d "barcode=1234"
```

### Validar Configuración

1. Acceder a **Configuraciones del Sistema**
2. Verificar sección **Dispositivos HikVision** visible
3. Verificar sección **Dispositivos Shelly** con nuevos campos
4. Agregar un dispositivo de prueba y guardar
5. Verificar en base de datos que se guardó correctamente

## Solución de Problemas

### Dispositivos HikVision no responden

1. Verificar URL de API correcta
2. Verificar credenciales de autenticación
3. Verificar conectividad de red
4. Revisar logs de error en servidor

### Canales Shelly no funcionan

1. Verificar que los canales estén correctamente configurados
2. Verificar que los canales físicos existan en el dispositivo
3. Probar cada canal individualmente desde la configuración
4. Verificar logs para ver qué canal se está activando

### Auto-llenado de litros no funciona

1. Verificar que la unidad tenga `capacity_liters` configurado
2. Limpiar caché del navegador
3. Verificar que se esté usando la versión actualizada de `exit.php`

## Roadmap Futuro

- [ ] Integración con múltiples lectores de código de barras simultáneos
- [ ] Dashboard de estado de dispositivos HikVision
- [ ] Alertas por dispositivos desconectados
- [ ] Historial de lecturas por dispositivo
- [ ] Configuración de canales por área/ubicación
- [ ] Soporte para otros fabricantes de cámaras LPR

## Documentación Relacionada

- [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md) - Guía de instalación general
- [UPDATE_GUIDE.md](UPDATE_GUIDE.md) - Guía de actualización
- [SHELLY_MULTI_DEVICE_GUIDE.md](SHELLY_MULTI_DEVICE_GUIDE.md) - Guía de dispositivos Shelly
- [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - Resumen del proyecto
