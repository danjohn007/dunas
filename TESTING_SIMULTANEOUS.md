# Plan de Pruebas - Ejecución Simultánea y Campo Área

## Pre-requisitos

1. Aplicar la migración de base de datos:
   ```bash
   mysql -u [user] -p [database] < config/update_area_simultaneous.sql
   ```

2. Verificar que las columnas fueron agregadas:
   ```sql
   DESCRIBE shelly_devices;
   -- Debe mostrar las columnas: area, is_simultaneous
   ```

## Casos de Prueba

### Test 1: Verificar UI - Campo Área
**Objetivo**: Confirmar que el campo "Área" aparece en la interfaz

**Pasos**:
1. Iniciar sesión como administrador
2. Ir a: Configuraciones → Dispositivos Shelly Cloud
3. Agregar un nuevo dispositivo o editar uno existente

**Resultado Esperado**:
- ✅ Campo "Área" visible entre "Servidor Cloud" y checkboxes
- ✅ Placeholder: "Ej: Entrada principal"
- ✅ Puede dejar vacío o ingresar texto

**Verificación en DB**:
```sql
SELECT id, name, area FROM shelly_devices;
-- El valor de 'area' debe coincidir con lo ingresado
```

---

### Test 2: Verificar UI - Checkbox Dispositivo Simultáneo
**Objetivo**: Confirmar que el checkbox "Dispositivo simultáneo" aparece

**Pasos**:
1. Ir a: Configuraciones → Dispositivos Shelly Cloud
2. Observar la sección de checkboxes

**Resultado Esperado**:
- ✅ Checkbox "Dispositivo simultáneo" visible (color verde)
- ✅ Aparece después de "Invertido (off → on)"
- ✅ Por defecto NO está marcado en nuevos dispositivos

**Verificación en DB**:
```sql
SELECT id, name, is_simultaneous FROM shelly_devices;
-- Dispositivos nuevos deben tener is_simultaneous = 0
-- Dispositivos marcados deben tener is_simultaneous = 1
```

---

### Test 3: Cambio de etiqueta "Nombre" a "Acción"
**Objetivo**: Confirmar el cambio de etiqueta

**Pasos**:
1. Ir a: Configuraciones → Dispositivos Shelly Cloud
2. Observar el select que antes decía "Nombre"

**Resultado Esperado**:
- ✅ Etiqueta dice "Acción" (no "Nombre")
- ✅ Select contiene opciones: "Abrir/Cerrar", "Vacío"
- ✅ Funcionalidad permanece igual

---

### Test 4: Guardar dispositivo con nuevos campos
**Objetivo**: Verificar que los campos se guardan correctamente

**Pasos**:
1. Agregar/editar un dispositivo con:
   - Acción: "Abrir/Cerrar"
   - Área: "Puerta Principal"
   - ✅ Dispositivo simultáneo (marcado)
   - ✅ Dispositivo habilitado (marcado)
2. Guardar
3. Recargar la página

**Resultado Esperado**:
- ✅ Área muestra "Puerta Principal"
- ✅ Checkbox "Dispositivo simultáneo" está marcado
- ✅ No hay errores en consola PHP

**Verificación en DB**:
```sql
SELECT * FROM shelly_devices WHERE area = 'Puerta Principal';
-- is_simultaneous = 1
-- is_enabled = 1
```

---

### Test 5: Ejecución normal (sin simultáneos)
**Objetivo**: Verificar que el comportamiento anterior sigue funcionando

**Setup**:
```sql
-- Tener 2 dispositivos con misma acción
-- Ninguno con is_simultaneous = 1
UPDATE shelly_devices SET is_simultaneous = 0;
```

**Pasos**:
1. Ir a: Control de Acceso
2. Registrar entrada (esto ejecuta acción "abrir_cerrar")
3. Revisar logs del servidor

**Resultado Esperado**:
- ✅ Solo se ejecuta 1 dispositivo (el primero por sort_order)
- ✅ Log muestra: `simul=0`

**Log Esperado**:
```
ShellyActionService::execute() - abrir_cerrar open - device=ABC123 channel=0 simul=0 invert=1
```

---

### Test 6: Ejecución simultánea (2 dispositivos)
**Objetivo**: Verificar ejecución simultánea con múltiples dispositivos

**Setup**:
```sql
-- Configurar 2 dispositivos con misma acción
-- Ambos con is_simultaneous = 1
INSERT INTO shelly_devices (name, auth_token, device_id, server_host, area, is_simultaneous, is_enabled, sort_order)
VALUES 
  ('Abrir/Cerrar', 'token1', 'device1', 'server1', 'Entrada A', 1, 1, 1),
  ('Abrir/Cerrar', 'token2', 'device2', 'server2', 'Entrada B', 1, 1, 2);

-- Crear acciones para ambos
INSERT INTO shelly_actions (device_id, code, label, action_kind, channel, is_default)
SELECT id, 'abrir_cerrar', 'Abrir/Cerrar', 'toggle', 0, 1
FROM shelly_devices WHERE device_id IN ('device1', 'device2');
```

**Pasos**:
1. Ir a: Control de Acceso
2. Registrar entrada
3. Revisar logs del servidor

**Resultado Esperado**:
- ✅ Ambos dispositivos se ejecutan
- ✅ Logs muestran 2 líneas (una por dispositivo)
- ✅ Ambos logs muestran `simul=1`

**Logs Esperados**:
```
ShellyActionService::execute() - abrir_cerrar open - device=device1 channel=0 simul=1 invert=1
ShellyActionService::execute() - abrir_cerrar open - device=device2 channel=0 simul=1 invert=1
```

---

### Test 7: Ejecución mixta (1 normal + 2 simultáneos)
**Objetivo**: Verificar que solo los simultáneos se ejecutan cuando hay al menos 1

**Setup**:
```sql
-- 3 dispositivos con misma acción
-- Solo 2 con is_simultaneous = 1
UPDATE shelly_devices SET is_simultaneous = 0 WHERE id = 1;
UPDATE shelly_devices SET is_simultaneous = 1 WHERE id IN (2, 3);
```

**Pasos**:
1. Ejecutar acción desde Control de Acceso
2. Revisar logs

**Resultado Esperado**:
- ✅ Solo los 2 dispositivos con `is_simultaneous=1` se ejecutan
- ✅ El dispositivo con `is_simultaneous=0` NO se ejecuta
- ✅ 2 líneas de log (no 3)

**Logs Esperados**:
```
ShellyActionService::execute() - abrir_cerrar open - device=device2 channel=0 simul=1 invert=...
ShellyActionService::execute() - abrir_cerrar open - device=device3 channel=0 simul=1 invert=...
```

---

### Test 8: Acciones diferentes
**Objetivo**: Verificar que la simultaneidad se aplica por acción

**Setup**:
```sql
-- Dispositivo A: acción "abrir_cerrar", is_simultaneous=1
-- Dispositivo B: acción "abrir_cerrar", is_simultaneous=1
-- Dispositivo C: acción "vacio", is_simultaneous=1
```

**Pasos**:
1. Ejecutar acción "abrir_cerrar"
2. Revisar logs

**Resultado Esperado**:
- ✅ Solo dispositivos A y B se ejecutan (misma acción)
- ✅ Dispositivo C NO se ejecuta (acción diferente)

---

### Test 9: Dispositivos deshabilitados
**Objetivo**: Verificar que dispositivos con is_enabled=0 no se ejecutan

**Setup**:
```sql
UPDATE shelly_devices SET is_enabled = 0 WHERE id = 2;
-- Dispositivo 2 tiene is_simultaneous=1 pero is_enabled=0
```

**Resultado Esperado**:
- ✅ Dispositivo 2 NO aparece en logs
- ✅ Solo dispositivos habilitados (is_enabled=1) se ejecutan

---

### Test 10: Diferentes canales y configuraciones
**Objetivo**: Verificar que cada dispositivo usa su propia configuración

**Setup**:
```sql
-- Dispositivo A: canal 0, invertido
-- Dispositivo B: canal 1, no invertido
-- Ambos: is_simultaneous=1, misma acción
```

**Resultado Esperado**:
- ✅ Logs muestran canales diferentes
- ✅ Logs muestran valores de invert diferentes
- ✅ Ambos se ejecutan

**Logs Esperados**:
```
ShellyActionService::execute() - abrir_cerrar open - device=A channel=0 simul=1 invert=1
ShellyActionService::execute() - abrir_cerrar open - device=B channel=1 simul=1 invert=0
```

---

## Pruebas de Regresión

### R1: Dispositivos existentes siguen funcionando
- Dispositivos creados antes de la migración deben seguir funcionando
- Por defecto tienen `is_simultaneous=0` → comportamiento anterior

### R2: Formulario de nuevo dispositivo
- Template de nuevo dispositivo incluye campos nuevos
- Valores por defecto correctos: area="", is_simultaneous=0

### R3: Validación de datos
- Campo área acepta texto libre
- Checkbox se guarda como 0 o 1
- No se rompen validaciones existentes

---

## Logs de Depuración

Ubicación de logs:
```bash
tail -f /var/log/apache2/error.log
# o
tail -f /var/log/php-fpm/error.log
```

Buscar líneas que contengan:
```
ShellyActionService::execute()
```

---

## Rollback

Si algo falla, revertir con:

```sql
ALTER TABLE shelly_devices DROP COLUMN is_simultaneous;
ALTER TABLE shelly_devices DROP COLUMN area;
```

Y revertir el código:
```bash
git revert [commit-hash]
```
