# Guía de Actualización v1.3.0

## Resumen de Cambios

Esta actualización implementa mejoras significativas en el sistema de registro de unidades, choferes y accesos, así como la integración con cámaras de lectura de placas Hikvision.

## Cambios Principales

### 1. Relaciones Obligatorias en el Registro de Unidades

**Antes:** Las unidades se registraban independientemente, requiriendo año y número de serie.

**Ahora:** 
- Las unidades deben estar relacionadas a un **cliente** y un **chofer** previamente registrado
- Los campos **Año** y **Número de Serie** ahora son **opcionales**
- Cada unidad pertenece a un cliente específico
- Cada unidad tiene un chofer asignado por defecto

### 2. Relaciones Obligatorias en el Registro de Choferes

**Antes:** Los choferes se registraban requiriendo número de licencia y fecha de vencimiento.

**Ahora:**
- Los choferes deben estar relacionados a un **cliente** previamente registrado
- Los campos **Número de Licencia** y **Vencimiento de Licencia** ahora son **opcionales**
- Cada chofer pertenece a un cliente específico

### 3. Registro Rápido Mejorado

**Funcionalidad cuando la placa EXISTE:**
- Precarga la información del último registro de entrada
- Muestra el cliente asociado a la unidad
- Permite **cambiar de chofer** si existen más choferes relacionados con el mismo cliente
- Facilita el registro rápido con datos históricos

**Funcionalidad cuando la placa NO EXISTE:**
- Carga el formulario de registro de unidad
- Relaciona obligatoriamente a un cliente y chofer
- Ofrece la opción de **"Registrar nuevo cliente"** desplegando los campos necesarios
- Ofrece la opción de **"Registrar nuevo chofer"** desplegando los campos necesarios
- Los campos no obligatorios (año, número de serie, licencia) permanecen opcionales

### 4. Integración con Cámara Hikvision

**Nueva Funcionalidad:**
- Lectura automática de placas mediante cámara Hikvision modelo IDS-2CD7A46G0/P-IZHS(C)
- Al registrar una entrada de unidad, el sistema lee la placa automáticamente
- Campo nuevo en registro: **"Lectura de cámara de placas"**
- **Indicador de discrepancia**: Detecta y marca cuando la placa leída difiere de la placa registrada
- Utiliza la API Web de Hikvision (ISAPI)

## Cambios en la Base de Datos

### Tabla `units` (Unidades)

**Nuevos campos:**
- `client_id` (INT, obligatorio): Relación con la tabla `clients`
- `driver_id` (INT, obligatorio): Relación con la tabla `drivers`

**Campos modificados:**
- `year` (INT, ahora NULL): Año opcional
- `serial_number` (VARCHAR(100), ahora NULL): Número de serie opcional

### Tabla `drivers` (Choferes)

**Nuevos campos:**
- `client_id` (INT, obligatorio): Relación con la tabla `clients`

**Campos modificados:**
- `license_number` (VARCHAR(50), ahora NULL): Número de licencia opcional
- `license_expiry` (DATE, ahora NULL): Vencimiento de licencia opcional

### Tabla `access_logs` (Registros de Acceso)

**Nuevos campos:**
- `license_plate_reading` (VARCHAR(20), NULL): Placa leída por la cámara Hikvision
- `plate_discrepancy` (BOOLEAN, default FALSE): Indica si hay discrepancia entre la placa registrada y la leída

### Tabla `settings` (Configuraciones)

**Nuevas configuraciones:**
- `hikvision_api_url`: URL de la cámara Hikvision (ej: http://192.168.1.100)
- `hikvision_username`: Usuario para autenticación
- `hikvision_password`: Contraseña para autenticación

## Instalación de la Actualización

### Paso 1: Respaldar la Base de Datos

```bash
mysqldump -u usuario -p dunas_access_control > backup_pre_v1.3.0.sql
```

### Paso 2: Ejecutar el Script de Actualización

```bash
mysql -u usuario -p dunas_access_control < config/update_1.3.0.sql
```

El script realizará:
1. Agregar las nuevas columnas a las tablas
2. Modificar las restricciones de campos existentes
3. Migrar datos existentes asignando relaciones por defecto
4. Agregar configuraciones de Hikvision
5. Validar la integridad de los datos

### Paso 3: Configurar la Cámara Hikvision (Opcional)

Si desea utilizar la lectura automática de placas:

1. Acceda a **Configuraciones del Sistema**
2. Configure los siguientes parámetros:
   - **URL de API Hikvision**: `http://[IP_CAMARA]` (ej: http://192.168.1.100)
   - **Usuario Hikvision**: Usuario con permisos ISAPI
   - **Contraseña Hikvision**: Contraseña del usuario

3. Pruebe la conexión usando el botón "Probar Conexión"

### Paso 4: Actualizar Archivos del Sistema

Reemplace los archivos modificados:

**Modelos:**
- `app/models/Unit.php`
- `app/models/Driver.php`
- `app/models/AccessLog.php`

**Controladores:**
- `app/controllers/UnitController.php`
- `app/controllers/DriverController.php`
- `app/controllers/AccessController.php`

**Vistas:**
- `app/views/units/create.php`
- `app/views/units/edit.php`
- `app/views/drivers/create.php`
- `app/views/drivers/edit.php`
- `app/views/access/quick_registration.php`

**Nuevos Helpers:**
- `app/helpers/HikvisionAPI.php`

## Configuración de la Cámara Hikvision

### Requisitos

- Cámara modelo IDS-2CD7A46G0/P-IZHS(C) o compatible
- Acceso a la red desde el servidor del sistema
- Usuario con permisos ISAPI habilitados

### Configuración en la Cámara

1. Acceda a la interfaz web de la cámara
2. Habilite el módulo **ANPR (Automatic Number Plate Recognition)**
3. Configure las reglas de detección de placas
4. Habilite la **API ISAPI**
5. Cree un usuario con permisos para acceder a `/ISAPI/Traffic/channels/1/vehicleDetect/plates`

### Endpoints Utilizados

El sistema utiliza los siguientes endpoints de la API Hikvision:

- **Detección de placas**: `/ISAPI/Traffic/channels/1/vehicleDetect/plates`
- **Información del dispositivo**: `/ISAPI/System/deviceInfo` (para pruebas)

### Autenticación

- Tipo: HTTP Digest Authentication
- Se recomienda usar HTTPS en producción
- En desarrollo, las verificaciones SSL están deshabilitadas

## Flujos de Trabajo Actualizados

### Registro de Nueva Unidad

1. Acceder a **Gestión de Unidades** > **Nueva Unidad**
2. Seleccionar **Cliente** (obligatorio)
3. Seleccionar **Chofer** (obligatorio)
4. Ingresar **Número de Placa** (obligatorio)
5. Ingresar **Capacidad en Litros** (obligatorio)
6. Ingresar **Marca** y **Modelo** (obligatorios)
7. Ingresar **Año** (opcional)
8. Ingresar **Número de Serie** (opcional)
9. Subir **Fotografía** (opcional)
10. Guardar

### Registro de Nuevo Chofer

1. Acceder a **Gestión de Choferes** > **Nuevo Chofer**
2. Seleccionar **Cliente** (obligatorio)
3. Ingresar **Nombre Completo** (obligatorio)
4. Ingresar **Teléfono** (obligatorio)
5. Ingresar **Número de Licencia** (opcional)
6. Ingresar **Vencimiento de Licencia** (opcional)
7. Subir **Fotografía** (opcional)
8. Guardar

### Registro Rápido de Entrada

**Placa Existente:**

1. Ingresar y buscar **Número de Placa**
2. El sistema muestra:
   - Información de la unidad
   - Cliente asociado
   - Último chofer usado
3. Seleccionar **Chofer** de la lista (si hay múltiples choferes del mismo cliente)
4. Confirmar registro
5. El sistema:
   - Lee la placa con la cámara Hikvision (si está configurada)
   - Detecta discrepancias
   - Abre la barrera automáticamente
   - Genera el ticket de entrada

**Placa No Existente:**

1. Ingresar y buscar **Número de Placa**
2. El sistema indica que la unidad no existe
3. Completar datos de la unidad (capacidad obligatoria, año y serie opcionales)
4. Marcar **"Registrar nuevo cliente"** (si aplica)
   - Completar datos del cliente
5. Marcar **"Registrar nuevo chofer"** (si aplica)
   - Completar datos del chofer (licencia opcional)
6. Confirmar registro
7. El sistema:
   - Crea el cliente (si es nuevo)
   - Crea el chofer (si es nuevo)
   - Crea la unidad con relaciones
   - Lee la placa con la cámara Hikvision
   - Abre la barrera automáticamente
   - Genera el ticket de entrada

## Validaciones y Reglas de Negocio

### Unidades

- ✅ Debe tener un cliente asociado
- ✅ Debe tener un chofer asociado
- ✅ Número de placa único y obligatorio
- ✅ Capacidad en litros obligatoria
- ✅ Marca y modelo obligatorios
- ⚠️ Año opcional
- ⚠️ Número de serie opcional

### Choferes

- ✅ Debe tener un cliente asociado
- ✅ Nombre completo obligatorio
- ✅ Teléfono obligatorio
- ⚠️ Número de licencia opcional
- ⚠️ Vencimiento de licencia opcional

### Lectura de Placas

- ⚠️ La lectura de placa es opcional (depende de configuración)
- ✅ Se marca discrepancia si la placa leída difiere de la registrada
- ✅ El registro continúa incluso si hay error en la lectura

## Migración de Datos Existentes

El script de actualización maneja automáticamente los datos existentes:

1. **Unidades sin cliente/chofer**: Se asigna el primer cliente y chofer activo
2. **Choferes sin cliente**: Se asigna el primer cliente activo
3. **Campos ahora opcionales**: Los valores NULL son válidos

**Importante**: Revise y actualice las relaciones después de la migración para reflejar las relaciones reales.

## Solución de Problemas

### Error: "Unidades sin cliente o driver"

**Causa**: Existen unidades sin relaciones después de la migración.

**Solución**:
```sql
-- Verificar unidades sin cliente
SELECT * FROM units WHERE client_id IS NULL;

-- Asignar cliente manualmente
UPDATE units SET client_id = [ID_CLIENTE] WHERE id = [ID_UNIDAD];
```

### Error: "Cámara Hikvision no responde"

**Causa**: Problemas de conectividad o configuración incorrecta.

**Solución**:
1. Verificar que la cámara esté encendida y en la red
2. Probar acceso desde navegador: `http://[IP_CAMARA]/ISAPI/System/deviceInfo`
3. Verificar usuario y contraseña en la configuración
4. Revisar logs del sistema para detalles del error

### La lectura de placa no funciona

**Causa**: La cámara no está configurada para ANPR o el endpoint es incorrecto.

**Solución**:
1. Verificar que el módulo ANPR esté habilitado en la cámara
2. Confirmar el endpoint correcto en la documentación de su modelo
3. Probar manualmente con curl:
   ```bash
   curl -u usuario:contraseña http://[IP_CAMARA]/ISAPI/Traffic/channels/1/vehicleDetect/plates
   ```

## Notas de Compatibilidad

- ✅ Compatible con MySQL 5.7+
- ✅ Compatible con PHP 7.4+
- ✅ Requiere extensión PHP: curl, simplexml
- ✅ Los datos existentes se migran automáticamente
- ⚠️ Revise las relaciones migradas después de la actualización

## Soporte

Para preguntas o problemas con esta actualización, consulte:
- Documentación técnica: `IMPLEMENTATION_v1.3.0.md`
- Guía de instalación: `INSTALLATION_GUIDE.md`
- Solución de problemas: `TROUBLESHOOTING.md`

---

**Versión**: 1.3.0  
**Fecha**: Noviembre 2024  
**Compatibilidad**: v1.2.0 y superiores
