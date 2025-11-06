# Guía de Implementación: Corrección de Doble Pulso y Medio Pulso

## 📋 Resumen de Cambios

Esta implementación resuelve dos problemas críticos en el control de barreras:

1. **Doble pulso en entrada**: Múltiples llamadas al relay causaban pulsos duplicados
2. **Medio pulso en salida**: El relay no completaba el ciclo completo ON→OFF

## 🔧 Cambios Implementados

### 1. Nuevo Cliente Shelly Cloud (`ShellyCloudClient.php`)

**Ubicación**: `app/services/ShellyCloudClient.php`

**Características**:
- Cliente mejorado para Shelly Cloud REST API
- Método `pulse()` atómico que garantiza ciclo completo (ON→OFF o OFF→ON)
- Soporte para `invert_sequence` configurable
- Límite de duración de 10 segundos para evitar timeouts

**Uso**:
```php
$client = new ShellyCloudClient($server, $deviceId, $authKey);
$result = $client->pulse($relayId, $durationMs, $invertSequence);
```

### 2. Sistema de Locking e Idempotencia (`ShellyLockHelper.php`)

**Ubicación**: `app/helpers/ShellyLockHelper.php`

**Características**:
- **`withPortLock()`**: Usa MySQL GET_LOCK para serializar operaciones en el mismo relay
- **`logPulse()`**: Registra pulsos en base de datos con constraint UNIQUE
- **`pulseExists()`**: Verifica si un pulso ya fue ejecutado
- **`cleanOldLogs()`**: Limpia registros antiguos (> 30 días)

**Ventajas**:
- Evita dobles pulsos incluso con llamadas simultáneas
- Idempotencia garantizada por constraint UNIQUE en BD
- Lock de 2 segundos por defecto (configurable)

### 3. Tabla de Log de Pulsos (`io_pulse_log`)

**Migration**: `config/update_pulse_idempotency.sql`

**Estructura**:
```sql
CREATE TABLE io_pulse_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  action ENUM('entry','exit') NOT NULL,
  relay_id INT NOT NULL,
  correlation VARCHAR(64) NOT NULL,
  device_id VARCHAR(64) NULL,
  duration_ms INT NULL,
  success TINYINT NOT NULL DEFAULT 1,
  error_message TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_pulse (relay_id, correlation)
);
```

**Correlation IDs**:
- Entrada: `"access:{$accessId}:entry"`
- Salida: `"access:{$accessId}:exit"`

### 4. Servicio Actualizado (`ShellyActionService.php`)

**Cambios**:
- Acepta parámetro `$correlationId` para idempotencia
- Verifica si el pulso ya existe antes de ejecutar
- Envuelve ejecución en `withPortLock()` para serialización
- Registra todos los pulsos en `io_pulse_log`
- Usa `ShellyCloudClient` por defecto (con fallback a `ShellyAPI`)

### 5. Controlador Actualizado (`AccessController.php`)

**Cambios**:
- Genera correlation IDs únicos por operación
- Pasa correlation ID a `ShellyActionService::execute()`
- Un solo punto de invocación por operación (servidor)

### 6. Frontend Actualizado

**Archivos**: `app/views/access/create.php`, `app/views/access/exit.php`

**Cambios**:
- **ELIMINADO**: Llamadas JavaScript a `window.shellyControl.openBarrier()`
- **AGREGADO**: Deshabilita botón inmediatamente al enviar formulario
- **RESULTADO**: Sin doble llamadas (JS + servidor)

## 📝 Instalación

### Paso 1: Aplicar Migración de Base de Datos

```bash
mysql -u [usuario] -p dunas_access_control < config/update_pulse_idempotency.sql
```

O desde la aplicación MySQL:
```sql
USE dunas_access_control;
SOURCE /path/to/config/update_pulse_idempotency.sql;
```

### Paso 2: Verificar Tabla Creada

```sql
DESCRIBE io_pulse_log;
SELECT COUNT(*) FROM io_pulse_log; -- Debe retornar 0 inicialmente
```

### Paso 3: No Requiere Configuración Adicional

Los cambios son transparentes y usan la configuración existente de Shelly.

## 🧪 Pruebas

### Prueba 1: Entrada con Pulso Único

**Objetivo**: Verificar que solo se ejecuta un pulso en entrada.

**Pasos**:
1. Ir a "Registrar Entrada"
2. Seleccionar cliente, unidad y chofer
3. Hacer clic en "Registrar Entrada"
4. **NO** hacer doble clic (el botón debe deshabilitarse inmediatamente)

**Verificación**:
```sql
-- Debe haber exactamente 1 registro para este acceso
SELECT * FROM io_pulse_log WHERE correlation LIKE 'access:%:entry' ORDER BY id DESC LIMIT 5;
```

**Resultado esperado**:
- Barrera se abre una sola vez
- Solo 1 registro en `io_pulse_log` para este access ID
- Estado del relay muestra OFF al final (ciclo completo)

### Prueba 2: Salida con Ciclo Completo

**Objetivo**: Verificar que el relay completa el ciclo ON→OFF.

**Pasos**:
1. Ir a "Registrar Salida" para un acceso en progreso
2. Ingresar litros suministrados
3. Hacer clic en "Registrar Salida"

**Verificación**:
```sql
-- Debe haber exactamente 1 registro de salida
SELECT * FROM io_pulse_log WHERE correlation LIKE 'access:%:exit' ORDER BY id DESC LIMIT 5;
```

**Resultado esperado**:
- Barrera se cierra completamente (ciclo ON→OFF)
- Solo 1 registro en `io_pulse_log` para este access ID
- Estado del relay muestra OFF al final

### Prueba 3: Idempotencia (Doble Clic)

**Objetivo**: Verificar que dobles clics no generan dobles pulsos.

**Pasos**:
1. Ir a "Registrar Entrada"
2. Llenar formulario
3. Hacer doble clic rápido en "Registrar Entrada" (antes de que se deshabilite)

**Verificación**:
```sql
-- Debe haber exactamente 1 registro incluso con doble clic
SELECT * FROM io_pulse_log WHERE correlation LIKE 'access:%:entry' ORDER BY id DESC LIMIT 5;
```

**Resultado esperado**:
- Solo se ejecuta 1 pulso (el primero)
- Solo 1 registro en `io_pulse_log`
- El segundo intento es bloqueado por lock o UNIQUE constraint

### Prueba 4: Logs del Sistema

**Verificar en logs del servidor** (`/var/log/apache2/error.log` o similar):

```bash
tail -f /var/log/apache2/error.log | grep -i shelly
```

**Buscar**:
- `ShellyCloudClient::pulse()` - Inicio de pulso
- `ShellyLockHelper - Lock 'shelly_port_X' acquired` - Lock obtenido
- `ShellyLockHelper - Pulse logged: access:X:entry` - Pulso registrado
- `ShellyLockHelper - Lock 'shelly_port_X' released` - Lock liberado

## 🔍 Troubleshooting

### Problema: "lock_timeout" en logs

**Causa**: Otro proceso tiene el lock del relay.

**Solución**:
```sql
-- Verificar locks activos
SELECT GET_LOCK('shelly_port_0', 0) AS can_lock;
-- Si retorna 0, hay un lock activo

-- Liberar lock manualmente (usar con precaución)
DO RELEASE_LOCK('shelly_port_0');
```

### Problema: "Duplicate entry" en logs

**Causa**: Intento de ejecutar el mismo pulso dos veces.

**Solución**: Esto es normal y esperado. Es el mecanismo de idempotencia funcionando correctamente.

### Problema: Pulso no se ejecuta

**Verificar**:
1. Configuración de Shelly en base de datos
2. Dispositivo habilitado: `SELECT * FROM shelly_devices WHERE is_enabled = 1;`
3. Conexión a Shelly Cloud: Verificar en logs si hay errores HTTP

## 🛡️ Seguridad

### Límite de Duración

Los pulsos están limitados a 10 segundos máximo para evitar:
- Timeouts prolongados del servidor
- Bloqueos indefinidos del relay

### Limpieza de Logs

Para mantener el rendimiento, limpiar logs antiguos periódicamente:

```sql
-- Eliminar registros de más de 30 días
DELETE FROM io_pulse_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

O usar el helper:
```php
ShellyLockHelper::cleanOldLogs($db, 30); // 30 días
```

## 📊 Monitoreo

### Query útiles para monitoreo:

```sql
-- Pulsos de hoy
SELECT action, COUNT(*) as total, SUM(success) as exitosos
FROM io_pulse_log
WHERE DATE(created_at) = CURDATE()
GROUP BY action;

-- Errores recientes
SELECT * FROM io_pulse_log
WHERE success = 0
ORDER BY created_at DESC
LIMIT 10;

-- Pulsos por relay
SELECT relay_id, COUNT(*) as total
FROM io_pulse_log
WHERE DATE(created_at) = CURDATE()
GROUP BY relay_id;
```

## ✅ Criterios de Aceptación

- ✅ **Entrada**: Solo 1 pulso observado en Shelly por registro
- ✅ **Salida**: Relay completa ciclo completo (enciende y apaga)
- ✅ **Doble clic**: No genera 2 pulsos (lock funciona)
- ✅ **Logs**: 1 registro por operación en `io_pulse_log`
- ✅ **Configuración**: Respeta puertos y duraciones de BD
- ✅ **Sin duplicados**: No hay llamadas duplicadas en el servidor

## 📚 Referencias

- Configuración de Shelly: `SHELLY_API.md`
- Migración a Cloud: `CLOUD_API_MIGRATION.md`
- Testing: `TESTING_v1.4.0.md`
