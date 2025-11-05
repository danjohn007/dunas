# Implementación de Ejecución Simultánea y Campo Área

## Resumen
Esta implementación agrega soporte para:
1. **Campo Área**: Campo informativo de texto para cada dispositivo Shelly
2. **Dispositivo Simultáneo**: Checkbox que permite ejecutar múltiples dispositivos con la misma acción simultáneamente

## Cambios Realizados

### 1. Base de Datos
**Archivo**: `config/update_area_simultaneous.sql`
- Agrega columna `area` (VARCHAR(100)) a la tabla `shelly_devices`
- Agrega columna `is_simultaneous` (TINYINT) a la tabla `shelly_devices`

### 2. Interfaz de Usuario
**Archivo**: `app/views/settings/index.php`
- Cambio de etiqueta "Nombre" → "Acción"
- Nuevo campo "Área" (texto libre)
- Nuevo checkbox "Dispositivo simultáneo"
- Cambios aplicados tanto en la vista de dispositivos existentes como en el template para nuevos dispositivos

### 3. Controlador
**Archivo**: `app/controllers/SettingsController.php`
- Método `saveShellyDevices()` actualizado para mapear los campos `area` e `is_simultaneous`
- Los valores se sanitizan y validan antes de guardar

### 4. Modelo ShellyDevice
**Archivo**: `app/models/ShellyDevice.php`
- Método `upsertBatch()` actualizado para incluir `area` e `is_simultaneous`
- Columnas incluidas tanto en UPDATE como en INSERT

### 5. Modelo ShellyAction
**Archivo**: `app/models/ShellyAction.php`
- Nuevo método `resolveAllByAction($db, $code)`: Retorna todos los dispositivos habilitados que tienen una acción específica
- Incluye el campo `is_simultaneous` en los resultados

### 6. Servicio ShellyActionService
**Archivo**: `app/services/ShellyActionService.php`
- Método `execute()` completamente refactorizado para soportar ejecución simultánea
- **Lógica de ejecución**:
  1. Obtiene todos los dispositivos con la acción solicitada
  2. Filtra dispositivos con `is_simultaneous=1`
  3. Si hay dispositivos simultáneos → ejecuta en TODOS los simultáneos
  4. Si NO hay dispositivos simultáneos → ejecuta solo en el primero (compatibilidad)
- Logs mejorados que incluyen: `device_id`, `channel`, `simul`, `invert`

## Lógica de Ejecución Simultánea

### Comportamiento
```php
// Si hay ≥1 dispositivo con is_simultaneous=1 para la acción "abrir_cerrar"
→ Ejecuta en TODOS los dispositivos con is_simultaneous=1

// Si NO hay ningún dispositivo con is_simultaneous=1
→ Ejecuta solo en el primer dispositivo (por sort_order)
```

### Ejemplo
**Escenario 1: Dos dispositivos con misma acción**
- Dispositivo A: acción="abrir_cerrar", is_simultaneous=1
- Dispositivo B: acción="abrir_cerrar", is_simultaneous=1
- **Resultado**: Ambos se ejecutan simultáneamente

**Escenario 2: Un dispositivo sin is_simultaneous**
- Dispositivo A: acción="abrir_cerrar", is_simultaneous=0
- Dispositivo B: acción="abrir_cerrar", is_simultaneous=0
- **Resultado**: Solo se ejecuta el dispositivo A (primero por orden)

**Escenario 3: Mixto**
- Dispositivo A: acción="abrir_cerrar", is_simultaneous=0
- Dispositivo B: acción="abrir_cerrar", is_simultaneous=1
- Dispositivo C: acción="abrir_cerrar", is_simultaneous=1
- **Resultado**: Se ejecutan B y C simultáneamente (A se ignora)

## Migración de Base de Datos

Para aplicar los cambios en una base de datos existente, ejecutar:

```sql
SOURCE config/update_area_simultaneous.sql;
```

O manualmente:

```sql
ALTER TABLE shelly_devices 
ADD COLUMN area VARCHAR(100) NOT NULL DEFAULT '' 
AFTER server_host;

ALTER TABLE shelly_devices 
ADD COLUMN is_simultaneous TINYINT NOT NULL DEFAULT 0 
AFTER invert_sequence;
```

## Compatibilidad

- **Retrocompatibilidad**: 100% compatible con configuraciones existentes
- Dispositivos existentes tendrán `is_simultaneous=0` por defecto
- Campo `area` será vacío por defecto
- La lógica antigua (un solo dispositivo) sigue funcionando si no se activa el checkbox

## Testing

Para probar la funcionalidad:

1. Aplicar la migración SQL
2. Ir a Configuraciones → Dispositivos Shelly Cloud
3. Agregar/editar dispositivos con:
   - Acción: "Abrir/Cerrar" o "Vacío"
   - Área: Texto informativo (ej: "Entrada principal")
   - Dispositivo simultáneo: Marcar si debe ejecutarse junto con otros
4. Crear múltiples dispositivos con misma acción y marcar como simultáneos
5. Probar ejecución desde Control de Acceso
6. Verificar logs para confirmar qué dispositivos se ejecutaron

## Logs

El servicio genera logs detallados en cada ejecución:

```
ShellyActionService::execute() - abrir_cerrar open - device=ABC123 channel=0 simul=1 invert=1
ShellyActionService::execute() - abrir_cerrar open - device=DEF456 channel=1 simul=1 invert=0
```

Esto permite verificar:
- Qué dispositivos se ejecutaron
- En qué canal
- Si eran simultáneos
- Si estaban invertidos
