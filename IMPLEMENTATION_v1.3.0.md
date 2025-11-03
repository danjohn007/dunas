# Implementación v1.3.0 - Sistema de Control de Acceso con IoT

## Resumen Ejecutivo

Esta versión implementa mejoras críticas en el sistema de gestión de unidades y choferes, estableciendo relaciones obligatorias entre entidades y simplificando los procesos de registro. Adicionalmente, integra tecnología de reconocimiento automático de placas mediante cámaras Hikvision.

## Objetivos Cumplidos

### 1. Relaciones Obligatorias en Unidades de Transporte ✅

**Problema Original:**
- Las unidades se registraban independientemente sin relación a clientes o choferes
- Los campos de Año y Número de Serie eran obligatorios, dificultando registros rápidos

**Solución Implementada:**
- Cada unidad debe relacionarse obligatoriamente con un **cliente** y un **chofer** previamente registrados
- Los campos **Año** y **Número de Serie** ahora son opcionales
- Permite trazabilidad completa de quién opera cada unidad

**Archivos Modificados:**
- `app/models/Unit.php`: Agregadas relaciones con clientes y choferes
- `app/controllers/UnitController.php`: Validación actualizada
- `app/views/units/create.php`: Selectores para cliente y chofer
- `app/views/units/edit.php`: Selectores para cliente y chofer
- `config/update_1.3.0.sql`: Alteraciones de tabla `units`

### 2. Relaciones Obligatorias en Choferes ✅

**Problema Original:**
- Los choferes se registraban sin relación a clientes
- Número de Licencia y Vencimiento eran obligatorios, dificultando registros urgentes

**Solución Implementada:**
- Cada chofer debe relacionarse obligatoriamente con un **cliente** previamente registrado
- Los campos **Número de Licencia** y **Vencimiento de Licencia** ahora son opcionales
- Facilita el registro rápido cuando no se cuenta con documentación completa

**Archivos Modificados:**
- `app/models/Driver.php`: Agregada relación con clientes
- `app/controllers/DriverController.php`: Validación actualizada
- `app/views/drivers/create.php`: Selector para cliente
- `app/views/drivers/edit.php`: Selector para cliente
- `config/update_1.3.0.sql`: Alteraciones de tabla `drivers`

### 3. Registro Rápido Mejorado ✅

**Problema Original:**
- No se reutilizaba información de registros anteriores
- No se podía cambiar de chofer fácilmente para la misma unidad
- Registro de nueva unidad requería datos completos en un solo paso

**Solución Implementada:**

#### Cuando la Placa Existe:
- Precarga información del **último registro de entrada**
- Muestra cliente y chofer previos
- Permite **cambiar de chofer** si hay múltiples choferes del mismo cliente
- Selector desplegable con todos los choferes disponibles

#### Cuando la Placa NO Existe:
- Formulario inteligente con opciones expandibles
- Checkbox **"Registrar nuevo cliente"**: Despliega campos de cliente solo cuando es necesario
- Checkbox **"Registrar nuevo chofer"**: Despliega campos de chofer solo cuando es necesario
- Mantiene campos opcionales (año, serie, licencia) para agilizar el registro

**Archivos Modificados:**
- `app/views/access/quick_registration.php`: Vista completamente rediseñada con JavaScript mejorado
- `app/controllers/AccessController.php`: Métodos `searchUnit()` y `quickEntry()` mejorados
- `app/models/AccessLog.php`: Nuevo método `getLastEntryByPlate()`
- `app/models/Unit.php`: Nuevo método `getDriversByClient()`

### 4. Integración con Cámara Hikvision ✅

**Problema Original:**
- Verificación manual de placas propensa a errores
- No había registro de la placa leída automáticamente

**Solución Implementada:**
- Integración con API de Hikvision (ISAPI)
- Lectura automática de placas al registrar entrada
- Almacenamiento de la lectura en campo `license_plate_reading`
- **Detección de discrepancias**: Compara placa registrada vs placa leída
- Indicador visual de discrepancias en interfaz
- Sistema continúa funcionando si la cámara no está disponible

**Modelo de Cámara Soportado:**
- IDS-2CD7A46G0/P-IZHS(C)
- Otros modelos Hikvision compatibles con ISAPI

**Archivos Creados:**
- `app/helpers/HikvisionAPI.php`: Clase completa para interacción con cámara
  - `readLicensePlate()`: Lee placa actual
  - `hasDiscrepancy()`: Compara placas
  - `testConnection()`: Prueba conectividad
  - `parseHikvisionResponse()`: Parsea XML de respuesta

**Archivos Modificados:**
- `app/models/AccessLog.php`: Campos para lectura de placa y discrepancia
- `app/controllers/AccessController.php`: Integración en `create()` y `quickEntry()`
- `config/update_1.3.0.sql`: Campos en tabla `access_logs`

## Cambios en la Base de Datos

### Tabla `units`

```sql
ALTER TABLE units 
ADD COLUMN client_id INT NULL,
ADD COLUMN driver_id INT NULL,
MODIFY COLUMN year INT NULL,
MODIFY COLUMN serial_number VARCHAR(100) NULL;
```

**Nuevas Relaciones:**
- `FOREIGN KEY (client_id) REFERENCES clients(id)`
- `FOREIGN KEY (driver_id) REFERENCES drivers(id)`

### Tabla `drivers`

```sql
ALTER TABLE drivers
ADD COLUMN client_id INT NULL,
MODIFY COLUMN license_number VARCHAR(50) NULL,
MODIFY COLUMN license_expiry DATE NULL;
```

**Nueva Relación:**
- `FOREIGN KEY (client_id) REFERENCES clients(id)`

### Tabla `access_logs`

```sql
ALTER TABLE access_logs
ADD COLUMN license_plate_reading VARCHAR(20) NULL,
ADD COLUMN plate_discrepancy BOOLEAN DEFAULT FALSE;
```

### Tabla `settings`

```sql
INSERT INTO settings (setting_key, setting_value) VALUES
('hikvision_api_url', ''),
('hikvision_username', ''),
('hikvision_password', '');
```

## Arquitectura de la Solución

### Capa de Modelos

**Unit.php** - Gestión de Unidades
- `getAll()`: Incluye joins con clients y drivers
- `getById()`: Incluye información de cliente y chofer
- `create()`: Requiere client_id y driver_id
- `update()`: Actualiza relaciones
- `getDriversByClient()`: Lista choferes de un cliente

**Driver.php** - Gestión de Choferes
- `getAll()`: Incluye join con clients
- `getById()`: Incluye información de cliente
- `create()`: Requiere client_id
- `update()`: Actualiza relación con cliente

**AccessLog.php** - Registros de Acceso
- `create()`: Almacena lectura de placa y discrepancia
- `getLastEntryByPlate()`: Obtiene último registro por placa

### Capa de Controladores

**UnitController.php**
- Validación modificada: client_id y driver_id obligatorios
- Year y serial_number removidos de validación obligatoria
- Lista de clientes y drivers pasada a vistas

**DriverController.php**
- Validación modificada: client_id obligatorio
- License_number y license_expiry removidos de validación
- Lista de clientes pasada a vistas

**AccessController.php**
- `searchUnit()`: Retorna unidad, último registro y choferes disponibles
- `quickEntry()`: Crea cliente/chofer si es necesario, integra Hikvision
- `create()`: Integra lectura de Hikvision con indicador de discrepancia

### Capa de Helpers

**HikvisionAPI.php** - Nuevo
- Comunicación HTTP con cámara Hikvision
- Autenticación Digest
- Parseo de respuestas XML
- Manejo de errores y timeouts
- Detección de discrepancias

### Capa de Vistas

**units/create.php & units/edit.php**
- Selectores para cliente y chofer
- Campos año y serie sin asterisco de obligatorio
- Validación JavaScript del lado del cliente

**drivers/create.php & drivers/edit.php**
- Selector para cliente
- Campos de licencia sin asterisco de obligatorio

**access/quick_registration.php**
- Interfaz completamente rediseñada
- Pasos condicionales basados en existencia de placa
- Checkboxes para expandir formularios de nuevo cliente/chofer
- Selector dinámico de choferes cuando la unidad existe
- JavaScript con gestión de estado compleja

## Flujos de Datos

### Flujo 1: Registro Rápido - Placa Existente

```
Usuario ingresa placa → searchUnit()
↓
Sistema busca en BD
↓
Encuentra unidad con cliente_id y driver_id
↓
getLastEntryByPlate() obtiene último registro
↓
getDriversByClient() obtiene lista de choferes
↓
Vista muestra:
  - Datos de unidad
  - Cliente (precargado)
  - Selector de choferes (último seleccionado)
↓
Usuario selecciona chofer (o confirma el actual)
↓
quickEntry() crea registro
↓
HikvisionAPI::readLicensePlate()
↓
Compara con placa registrada
↓
Guarda lectura y discrepancia
↓
ShellyAPI::openBarrier()
↓
Redirige a ticket
```

### Flujo 2: Registro Rápido - Placa Nueva

```
Usuario ingresa placa → searchUnit()
↓
Sistema no encuentra unidad
↓
Vista muestra formularios expandibles
↓
Usuario marca "Registrar nuevo cliente"
↓
Completa datos de cliente
↓
Usuario marca "Registrar nuevo chofer"
↓
Completa datos de chofer (licencia opcional)
↓
Completa datos de unidad (año y serie opcionales)
↓
quickEntry() ejecuta:
  1. clientModel->create() si es nuevo
  2. driverModel->create() con client_id
  3. unitModel->create() con client_id y driver_id
  4. accessModel->create() con lectura de Hikvision
↓
HikvisionAPI::readLicensePlate()
↓
ShellyAPI::openBarrier()
↓
Redirige a ticket
```

## Consideraciones de Seguridad

### Validación de Entrada
- ✅ Validación en controlador antes de BD
- ✅ Validación en vista (HTML5 + JavaScript)
- ✅ Sanitización de inputs con `htmlspecialchars()`

### Relaciones de Base de Datos
- ✅ Claves foráneas con `ON DELETE RESTRICT` para prevenir eliminación accidental
- ✅ Índices en campos de relación para rendimiento

### API Hikvision
- ✅ Autenticación Digest
- ⚠️ SSL deshabilitado en desarrollo (habilitar en producción)
- ✅ Timeouts configurados (3s conexión, 5s total)
- ✅ Manejo de errores sin exponer credenciales

## Rendimiento

### Optimizaciones Implementadas

1. **Índices de Base de Datos**
   - `idx_client_id` en units
   - `idx_driver_id` en units
   - `idx_client_id` en drivers
   - `idx_license_plate_reading` en access_logs

2. **Consultas Optimizadas**
   - Uso de JOINs en lugar de consultas múltiples
   - Selección específica de campos necesarios

3. **Caching de Configuraciones**
   - Settings se cargan una vez por request

### Métricas Esperadas

- Búsqueda de unidad: < 100ms
- Registro rápido (placa existente): < 500ms
- Registro rápido (placa nueva): < 1s
- Lectura de placa Hikvision: < 3s (timeout configurado)

## Pruebas Recomendadas

### Casos de Prueba - Unidades

1. ✅ Crear unidad con cliente y chofer
2. ✅ Crear unidad sin año (debe permitir)
3. ✅ Crear unidad sin número de serie (debe permitir)
4. ✅ Intentar crear unidad sin cliente (debe fallar)
5. ✅ Intentar crear unidad sin chofer (debe fallar)
6. ✅ Editar unidad y cambiar cliente
7. ✅ Editar unidad y cambiar chofer

### Casos de Prueba - Choferes

1. ✅ Crear chofer con cliente
2. ✅ Crear chofer sin número de licencia (debe permitir)
3. ✅ Crear chofer sin vencimiento (debe permitir)
4. ✅ Intentar crear chofer sin cliente (debe fallar)
5. ✅ Editar chofer y cambiar cliente

### Casos de Prueba - Registro Rápido

1. ✅ Buscar placa existente → debe precargar datos
2. ✅ Cambiar chofer en placa existente
3. ✅ Buscar placa nueva → debe mostrar formularios
4. ✅ Registrar con nuevo cliente y nuevo chofer
5. ✅ Registrar sin llenar campos opcionales
6. ✅ Verificar que se guarda lectura de Hikvision
7. ✅ Verificar detección de discrepancia

### Casos de Prueba - Hikvision

1. ✅ Lectura exitosa de placa coincidente
2. ✅ Lectura exitosa de placa con discrepancia
3. ✅ Cámara no disponible (debe continuar sin error)
4. ✅ Cámara sin configurar (debe continuar sin error)
5. ✅ Timeout de lectura (debe continuar sin bloquear)

## Migración de Datos

### Estrategia Implementada

El script `update_1.3.0.sql` maneja automáticamente:

1. **Unidades existentes**
   - Asigna el primer cliente activo
   - Asigna el primer chofer activo
   - Permite valores NULL en año y serie

2. **Choferes existentes**
   - Asigna el primer cliente activo
   - Permite valores NULL en licencia

3. **Validación post-migración**
   ```sql
   SELECT 'Unidades sin cliente:', COUNT(*) FROM units WHERE client_id IS NULL;
   SELECT 'Unidades sin driver:', COUNT(*) FROM units WHERE driver_id IS NULL;
   SELECT 'Drivers sin cliente:', COUNT(*) FROM drivers WHERE client_id IS NULL;
   ```

### Acciones Post-Migración

**Importante**: Después de ejecutar el script:

1. Revisar y corregir relaciones asignadas automáticamente
2. Actualizar datos históricos con información correcta
3. Verificar que todas las unidades tienen client_id y driver_id
4. Verificar que todos los choferes tienen client_id

## Documentación Generada

1. **UPDATE_v1.3.0_GUIDE.md**: Guía completa de actualización
2. **IMPLEMENTATION_v1.3.0.md**: Este documento técnico
3. **config/update_1.3.0.sql**: Script de migración con comentarios

## Dependencias Agregadas

### PHP Extensions
- `curl`: Para comunicación con API Hikvision
- `simplexml`: Para parsear respuestas XML

### Librerías
- Ninguna (se usa PHP nativo)

## Configuración Requerida

### Base de Datos
```
MySQL 5.7+
InnoDB engine
utf8mb4 charset
```

### PHP
```
PHP 7.4+
Extensions: curl, simplexml, pdo_mysql
```

### Red
- Acceso a cámara Hikvision desde servidor
- Puerto 80/443 para API Hikvision

## Backlog y Mejoras Futuras

### Funcionalidades Adicionales Sugeridas

1. **Multi-cámara**: Soporte para múltiples cámaras
2. **Logs detallados**: Registro de todas las lecturas de placa
3. **Dashboard de discrepancias**: Vista dedicada para revisar discrepancias
4. **Notificaciones**: Alertas cuando hay discrepancia
5. **Reportes**: Estadísticas de lecturas exitosas/fallidas
6. **Interfaz de configuración**: Panel web para configurar Hikvision
7. **Test de conectividad**: Herramienta de diagnóstico en interfaz

### Optimizaciones Técnicas

1. Cache de consultas frecuentes
2. Lazy loading de imágenes
3. Compresión de respuestas HTTP
4. Paginación en selectores con muchos registros

## Conclusión

La versión 1.3.0 representa una evolución significativa del sistema, estableciendo bases sólidas para:

- Trazabilidad completa de operaciones
- Flexibilidad en registros rápidos
- Automatización mediante tecnología IoT
- Detección proactiva de inconsistencias

Todas las funcionalidades solicitadas han sido implementadas cumpliendo con los requisitos especificados y manteniendo la compatibilidad con el sistema existente.

---

**Versión**: 1.3.0  
**Fecha de Implementación**: Noviembre 2024  
**Desarrollador**: GitHub Copilot Agent  
**Estado**: ✅ Completado
