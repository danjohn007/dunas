# Guía de Pruebas - v1.4.0

## Resumen

Esta guía describe las pruebas necesarias para verificar la correcta implementación de las nuevas funcionalidades de la versión 1.4.0.

## Pre-requisitos

1. Base de datos actualizada con script de migración
2. Acceso al panel de administración
3. (Opcional) Dispositivos HikVision para pruebas de integración
4. (Opcional) Dispositivos Shelly para pruebas de integración

## 1. Migración de Base de Datos

### Prueba 1.1: Verificar Ejecución de Script SQL

**Objetivo**: Confirmar que el script de migración se ejecutó correctamente.

**Pasos**:
```sql
USE dunas_access_control;

-- Verificar tabla hikvision_devices existe
SHOW TABLES LIKE 'hikvision_devices';

-- Verificar estructura de hikvision_devices
DESCRIBE hikvision_devices;

-- Verificar nuevos campos en shelly_devices
DESCRIBE shelly_devices;

-- Verificar que existen los campos: entry_channel, exit_channel, pulse_duration_ms
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'shelly_devices' 
  AND TABLE_SCHEMA = 'dunas_access_control'
  AND COLUMN_NAME IN ('entry_channel', 'exit_channel', 'pulse_duration_ms');
```

**Resultado Esperado**:
- Tabla `hikvision_devices` existe
- Campos nuevos en `shelly_devices` existen
- Datos de dispositivos Shelly existentes tienen valores en nuevos campos

### Prueba 1.2: Verificar Migración de Datos

**Objetivo**: Confirmar que los datos existentes se migraron correctamente.

**Pasos**:
```sql
-- Verificar dispositivos Shelly tienen valores en nuevos campos
SELECT id, name, active_channel, entry_channel, exit_channel, pulse_duration_ms 
FROM shelly_devices;

-- Verificar dispositivos HikVision migrados desde settings
SELECT * FROM hikvision_devices;
```

**Resultado Esperado**:
- Todos los dispositivos Shelly tienen valores no nulos en `entry_channel`, `exit_channel`, `pulse_duration_ms`
- Si había configuración de HikVision en settings, ahora existe en `hikvision_devices`

## 2. Configuración de Dispositivos HikVision

### Prueba 2.1: Acceder a Sección HikVision

**Objetivo**: Verificar que la sección de dispositivos HikVision es accesible.

**Pasos**:
1. Iniciar sesión como administrador
2. Ir a **Configuraciones del Sistema**
3. Desplazarse hasta la sección **Dispositivos HikVision**

**Resultado Esperado**:
- Sección **Dispositivos HikVision** visible
- Botón **"Nuevo dispositivo +"** disponible
- Lista de dispositivos existentes (si los hay)

### Prueba 2.2: Agregar Cámara LPR

**Objetivo**: Verificar que se puede agregar una cámara LPR.

**Pasos**:
1. Clic en **"Nuevo dispositivo +"**
2. Completar formulario:
   - Nombre: "Cámara Entrada Principal"
   - Tipo: Cámara LPR (Lectura de Placas)
   - URL: `http://192.168.1.100` (o IP de prueba)
   - Usuario: `admin`
   - Contraseña: `admin123`
   - Área: "Entrada Principal"
   - Marcar "Dispositivo habilitado"
3. Clic en **"Guardar Dispositivos HikVision"**

**Resultado Esperado**:
- Mensaje de éxito: "Dispositivos HikVision guardados exitosamente"
- Dispositivo aparece en la lista
- Datos guardados correctamente en base de datos

**Verificación en BD**:
```sql
SELECT * FROM hikvision_devices WHERE name = 'Cámara Entrada Principal';
```

### Prueba 2.3: Agregar Lector de Código de Barras

**Objetivo**: Verificar que se puede agregar un lector de código de barras.

**Pasos**:
1. Clic en **"Nuevo dispositivo +"**
2. Completar formulario:
   - Nombre: "Lector Salida"
   - Tipo: Lector de Código de Barras
   - URL: `http://192.168.1.101`
   - Usuario: `admin`
   - Contraseña: `admin123`
   - Área: "Salida Principal"
   - Marcar "Dispositivo habilitado"
3. Clic en **"Guardar Dispositivos HikVision"**

**Resultado Esperado**:
- Dispositivo guardado exitosamente
- Tipo de dispositivo es `barcode_reader` en base de datos

### Prueba 2.4: Editar Dispositivo HikVision

**Objetivo**: Verificar que se puede editar un dispositivo existente.

**Pasos**:
1. Modificar el nombre de un dispositivo existente
2. Cambiar el área
3. Clic en **"Guardar Dispositivos HikVision"**

**Resultado Esperado**:
- Cambios guardados correctamente
- Datos actualizados en base de datos

### Prueba 2.5: Eliminar Dispositivo HikVision

**Objetivo**: Verificar que se puede eliminar un dispositivo.

**Pasos**:
1. Clic en el botón X rojo de un dispositivo
2. Confirmar eliminación en el diálogo
3. Clic en **"Guardar Dispositivos HikVision"**

**Resultado Esperado**:
- Dispositivo eliminado de la lista
- Registro eliminado de base de datos

## 3. Configuración de Canales Shelly

### Prueba 3.1: Verificar Nuevos Campos Shelly

**Objetivo**: Confirmar que los campos de canales están visibles.

**Pasos**:
1. Ir a **Configuraciones del Sistema**
2. Desplazarse hasta **Dispositivos Shelly Cloud**
3. Verificar dispositivos existentes o crear uno nuevo

**Resultado Esperado**:
- Campo **"Canal de Entrada (Apertura)"** visible
- Campo **"Canal de Salida (Cierre)"** visible
- Campo **"Duración Pulso (ms)"** visible
- Descripción de cada campo presente

### Prueba 3.2: Configurar Canales Diferentes

**Objetivo**: Verificar que se pueden configurar canales diferentes para entrada y salida.

**Pasos**:
1. Editar un dispositivo Shelly existente o crear uno nuevo
2. Configurar:
   - Canal de Entrada: 0
   - Canal de Salida: 1
   - Duración Pulso: 5000 ms
3. Clic en **"Guardar Dispositivos Shelly"**

**Resultado Esperado**:
- Configuración guardada exitosamente
- Valores correctos en base de datos

**Verificación en BD**:
```sql
SELECT id, name, entry_channel, exit_channel, pulse_duration_ms 
FROM shelly_devices 
WHERE name = '[nombre del dispositivo]';
```

### Prueba 3.3: Modificar Duración de Pulso

**Objetivo**: Verificar que se puede cambiar la duración del pulso.

**Pasos**:
1. Editar un dispositivo Shelly
2. Cambiar duración de pulso a 3000 ms
3. Guardar cambios

**Resultado Esperado**:
- Nueva duración guardada
- Campo acepta valores entre 100 y 60000 ms

## 4. Auto-llenado de Litros en Salida

### Prueba 4.1: Verificar Auto-llenado

**Objetivo**: Confirmar que el campo de litros se auto-llena con la capacidad de la unidad.

**Pasos**:
1. Crear un registro de entrada con una unidad de capacidad conocida (ej: 10,000 L)
2. Ir a **Control de Acceso**
3. Seleccionar el acceso en progreso
4. Clic en **"Registrar Salida"**
5. Verificar el campo **"Litros Suministrados"**

**Resultado Esperado**:
- Campo **"Litros Suministrados"** ya contiene el valor de la capacidad de la unidad
- Valor puede ser modificado manualmente
- Máximo permitido es la capacidad de la unidad

### Prueba 4.2: Modificar Valor Auto-llenado

**Objetivo**: Verificar que el valor auto-llenado puede ser modificado.

**Pasos**:
1. En formulario de salida con valor auto-llenado
2. Cambiar el valor a uno diferente (menor que la capacidad)
3. Clic en **"Registrar Salida"**

**Resultado Esperado**:
- Salida registrada con el valor modificado
- No se usa el valor auto-llenado si fue cambiado

## 5. Integración con API

### Prueba 5.1: Endpoint de Lector de Código de Barras - Ticket Válido

**Objetivo**: Verificar que el endpoint procesa correctamente un código de barras válido.

**Pre-requisito**: Tener un acceso activo con código de ticket conocido (ej: "1234")

**Pasos**:
```bash
curl -X POST http://localhost/access/barcodeReader \
  -d "barcode=1234" \
  -H "Content-Type: application/x-www-form-urlencoded"
```

**Resultado Esperado**:
```json
{
  "success": true,
  "message": "Salida registrada exitosamente con 10,000 litros.",
  "action": "exit",
  "barrier_closed": true,
  "access": { ... }
}
```

### Prueba 5.2: Endpoint de Lector de Código de Barras - Ticket Inválido

**Objetivo**: Verificar manejo de código de barras inválido.

**Pasos**:
```bash
curl -X POST http://localhost/access/barcodeReader \
  -d "barcode=9999" \
  -H "Content-Type: application/x-www-form-urlencoded"
```

**Resultado Esperado**:
```json
{
  "success": false,
  "message": "Código de barras no válido o no encontrado",
  "barcode": "9999"
}
```

### Prueba 5.3: Endpoint sin Parámetros

**Objetivo**: Verificar que el endpoint intenta leer del dispositivo si no se proporciona código.

**Pre-requisito**: Tener al menos un lector de código de barras configurado

**Pasos**:
```bash
curl -X POST http://localhost/access/barcodeReader \
  -H "Content-Type: application/x-www-form-urlencoded"
```

**Resultado Esperado**:
- Intenta leer del dispositivo configurado
- Retorna error si no puede leer o no hay dispositivo configurado

## 6. Lectura de Placas con HikVision

### Prueba 6.1: Lectura Automática en Entrada (Manual)

**Objetivo**: Verificar que se lee la placa automáticamente al registrar entrada.

**Pre-requisito**: Tener cámara LPR configurada (puede ser mock/simulación)

**Pasos**:
1. Ir a **Registrar Entrada**
2. Seleccionar chofer, unidad y cliente
3. Observar consola del navegador para logs
4. Clic en **"Registrar Entrada"**

**Resultado Esperado**:
- Sistema intenta leer placa de cámara configurada
- Si lee placa, se almacena en `license_plate_reading`
- Si hay discrepancia, se marca en `plate_discrepancy`

### Prueba 6.2: Verificar Almacenamiento de Lectura

**Objetivo**: Confirmar que la lectura se almacena en base de datos.

**Pasos**:
```sql
SELECT id, ticket_code, license_plate_reading, plate_discrepancy 
FROM access_logs 
ORDER BY id DESC 
LIMIT 5;
```

**Resultado Esperado**:
- Registros recientes tienen `license_plate_reading` si cámara leyó algo
- `plate_discrepancy` es 1 si hubo diferencia con placa registrada

### Prueba 6.3: Detección de Discrepancia

**Objetivo**: Verificar que se detectan discrepancias entre placa leída y registrada.

**Escenario de Prueba**:
- Unidad registrada con placa: "ABC-123-X"
- Cámara lee: "ABC-124-X"

**Resultado Esperado**:
- `plate_discrepancy` = 1
- Mensaje de advertencia en la interfaz

## 7. Canales Shelly en Acción

### Prueba 7.1: Activación de Canal de Entrada

**Objetivo**: Verificar que se activa el canal correcto al registrar entrada.

**Configuración de Prueba**:
- Entry Channel: 0
- Exit Channel: 1
- Pulse Duration: 5000 ms

**Pasos**:
1. Registrar una nueva entrada
2. Observar logs del servidor

**Resultado Esperado**:
- Log indica activación de canal 0
- Pulso de 5 segundos ejecutado
- Mensaje en consola: "ShellyActionService::execute() - abrir_cerrar open - device=XXX channel=0"

### Prueba 7.2: Activación de Canal de Salida

**Objetivo**: Verificar que se activa el canal correcto al registrar salida.

**Pasos**:
1. Registrar una salida
2. Observar logs del servidor

**Resultado Esperado**:
- Log indica activación de canal 1
- Sin pulso (activación directa)
- Mensaje en consola: "ShellyActionService::execute() - abrir_cerrar close - device=XXX channel=1"

### Prueba 7.3: Duración de Pulso Personalizada

**Objetivo**: Verificar que se respeta la duración de pulso configurada.

**Pasos**:
1. Configurar dispositivo Shelly con pulse_duration_ms = 3000
2. Registrar entrada
3. Medir tiempo de activación

**Resultado Esperado**:
- Pulso dura aproximadamente 3 segundos
- No el valor por defecto de 5 segundos

## 8. Pruebas de Compatibilidad

### Prueba 8.1: Configuración Legacy de HikVision

**Objetivo**: Verificar que sistemas con configuración antigua siguen funcionando.

**Escenario**:
- Sistema tiene configuración de HikVision en tabla `settings`
- No hay dispositivos en tabla `hikvision_devices`

**Pasos**:
1. Verificar tabla `settings` tiene claves de HikVision
2. Registrar entrada nueva
3. Verificar que intenta leer placa

**Resultado Esperado**:
- Sistema usa configuración legacy
- Funcionalidad de lectura de placas funciona
- No hay errores

### Prueba 8.2: Dispositivos Shelly Pre-existentes

**Objetivo**: Verificar que dispositivos Shelly configurados previamente funcionan.

**Pasos**:
1. Verificar dispositivos Shelly existentes antes de actualización
2. Usar control manual de barrera
3. Registrar entrada/salida

**Resultado Esperado**:
- Dispositivos siguen funcionando
- Usan canales migrados automáticamente
- No hay errores de configuración

## 9. Pruebas de Interfaz de Usuario

### Prueba 9.1: Responsividad de Formularios

**Objetivo**: Verificar que nuevos formularios son responsivos.

**Pasos**:
1. Abrir configuración en navegador de escritorio
2. Redimensionar ventana a tamaño móvil
3. Verificar formularios de HikVision y Shelly

**Resultado Esperado**:
- Formularios se adaptan al tamaño de pantalla
- Todos los campos son accesibles
- Botones visibles y funcionales

### Prueba 9.2: Validación de Formularios

**Objetivo**: Verificar validación de campos requeridos.

**Pasos HikVision**:
1. Intentar agregar dispositivo sin URL
2. Intentar guardar

**Resultado Esperado**:
- Navegador impide envío
- Mensaje de campo requerido

**Pasos Shelly**:
1. Intentar agregar dispositivo sin token o device ID
2. Intentar guardar

**Resultado Esperado**:
- Formulario no se envía
- Dispositivo se omite con mensaje de advertencia

## 10. Pruebas de Seguridad

### Prueba 10.1: Autenticación de Endpoints

**Objetivo**: Verificar que endpoints requieren autenticación apropiada.

**Pasos**:
```bash
# Sin sesión activa
curl -X POST http://localhost/access/barcodeReader \
  -d "barcode=1234"
```

**Resultado Esperado**:
- Endpoint `/access/barcodeReader` no requiere autenticación (diseñado para dispositivos)
- Otros endpoints requieren autenticación

### Prueba 10.2: Inyección SQL

**Objetivo**: Verificar protección contra inyección SQL.

**Pasos**:
1. Intentar agregar dispositivo con nombre: `'; DROP TABLE hikvision_devices; --`
2. Guardar

**Resultado Esperado**:
- Datos escapados correctamente
- No se ejecuta comando SQL malicioso
- Dispositivo guardado con nombre literal

## Checklist de Pruebas

### Base de Datos
- [ ] Script de migración ejecutado sin errores
- [ ] Tabla `hikvision_devices` creada
- [ ] Campos nuevos en `shelly_devices` agregados
- [ ] Datos migrados correctamente

### Configuración HikVision
- [ ] Sección visible en settings
- [ ] Agregar cámara LPR funciona
- [ ] Agregar lector de código de barras funciona
- [ ] Editar dispositivo funciona
- [ ] Eliminar dispositivo funciona

### Configuración Shelly
- [ ] Campos de canales visibles
- [ ] Canales se guardan correctamente
- [ ] Duración de pulso configurable
- [ ] Valores validados correctamente

### Funcionalidad
- [ ] Auto-llenado de litros funciona
- [ ] Lectura de placas funcional
- [ ] Detección de discrepancias funcional
- [ ] Canal de entrada se activa correctamente
- [ ] Canal de salida se activa correctamente
- [ ] Endpoint de código de barras funcional

### Integración
- [ ] API de lector de código de barras responde
- [ ] Registro de salida automático funciona
- [ ] Barrera se controla correctamente

### Compatibilidad
- [ ] Configuración legacy funciona
- [ ] Dispositivos existentes migrados
- [ ] No hay regresiones

### UI/UX
- [ ] Formularios responsivos
- [ ] Validación de campos funcional
- [ ] Mensajes de error claros
- [ ] Mensajes de éxito apropiados

## Reporte de Problemas

Si encuentra problemas durante las pruebas, documente:

1. **Descripción del problema**
2. **Pasos para reproducir**
3. **Resultado esperado**
4. **Resultado actual**
5. **Logs relevantes**
6. **Capturas de pantalla** (si aplica)

## Conclusión

Una vez completadas todas las pruebas exitosamente, el sistema está listo para producción.
