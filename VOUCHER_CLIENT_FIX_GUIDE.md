# Guía de Corrección del Módulo de Vales

## Fecha: 2026-02-04

## Resumen de Cambios

Esta actualización corrige los siguientes problemas en el módulo de vales:

1. ✅ **Errores 404 en páginas de impresión**
   - Se agregó el método `printSingle` al VoucherController
   - Se corrigieron las URLs de enrutamiento para usar camelCase (`printBatch`, `printSingle`)

2. ✅ **Campo "Seleccionar Cliente" requerido**
   - Se agregó la columna `client_id` a la tabla `vouchers`
   - Se actualizó el formulario de creación para incluir un dropdown de clientes (campo obligatorio)
   - Se validó que el campo no esté vacío antes de generar vales

3. ✅ **Información del cliente en vales impresos**
   - Los vales ahora muestran el nombre de la empresa del cliente
   - Los vales ahora muestran el teléfono del cliente

4. ✅ **Error de duplicado de código QR vacío**
   - Se agregó validación para prevenir generación de QR codes vacíos
   - Se agregó limpieza de datos existentes con QR codes inválidos
   - Se mejoraron mensajes de error para mostrar detalles específicos

## Pasos de Instalación

### 1. Aplicar Migraciones de Base de Datos (CRÍTICO)

Ejecutar los siguientes scripts SQL en orden:

```bash
# Primero: Limpiar duplicados y estructurar correctamente
mysql -u root -p nombre_base_datos < config/fix_vouchers_duplicates.sql

# Segundo: Arreglar esquema y constraint names
mysql -u root -p nombre_base_datos < config/fix_voucher_code_constraint.sql

# Tercero: Agregar campo client_id si no existe
mysql -u root -p nombre_base_datos < config/fix_vouchers_client_field.sql
```

O ejecutar manualmente en orden:

#### Script 1: Limpiar y estructurar (fix_vouchers_duplicates.sql)
```sql
-- Crear tabla si no existe
CREATE TABLE IF NOT EXISTS `vouchers` ( /* ... estructura completa ... */ );

-- IMPORTANTE: Limpiar vouchers con QR codes vacíos o inválidos
DELETE FROM `vouchers` WHERE `qr_code` = '' OR `qr_code` IS NULL OR LENGTH(`qr_code`) < 10;

-- Agregar constraints necesarios
-- (ver archivo completo para detalles)
```

#### Script 2: Arreglar constraint voucher_code (fix_voucher_code_constraint.sql - NUEVO)
```sql
-- Este script maneja variaciones de esquema en producción
-- Si la columna se llama 'voucher_code' la renombra a 'qr_code'
-- Limpia constraints antiguos
-- Asegura UNIQUE constraint correcto en qr_code
-- Agrega UNIQUE constraint en (serie, folio)
```

#### Script 3: Agregar campo de cliente (fix_vouchers_client_field.sql)
```sql
-- Add client_id column to vouchers table
ALTER TABLE `vouchers` 
ADD COLUMN `client_id` int(11) DEFAULT NULL AFTER `created_by`,
ADD KEY `idx_client_id` (`client_id`),
ADD CONSTRAINT `fk_vouchers_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL;
```

### 2. Verificar Cambios

```sql
-- Verificar estructura de la tabla
DESCRIBE vouchers;

-- Debería mostrar:
-- - id, serie, folio, qr_code, capacity, status, used_at, used_by_access_log_id
-- - created_by, client_id, created_at, updated_at
-- - Índice UNIQUE en qr_code
-- - Foreign keys para access_logs, users, clients

-- Verificar que no haya QR codes vacíos
SELECT COUNT(*) FROM vouchers WHERE qr_code = '' OR qr_code IS NULL;
-- Debe retornar 0

-- Verificar constraints
SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_NAME = 'vouchers' AND TABLE_SCHEMA = DATABASE();
```

### 3. Probar Funcionalidad

1. **Crear Vales:**
   - Ir a Vales > Generar Vales
   - Verificar que el campo "Seleccionar Cliente" sea visible y requerido
   - Intentar generar sin seleccionar cliente → debe mostrar error
   - Seleccionar un cliente
   - Llenar los demás campos (Serie, Folio Inicial, Cantidad, Capacidad)
   - Generar vales
   - Verificar que NO aparezca el error de duplicados
   - Verificar redirección correcta a la página de impresión

2. **Imprimir Vales (Lote):**
   - Después de generar vales, verificar que se redirija a `/vouchers/printBatch`
   - Verificar que se muestren los vales con información del cliente
   - Verificar que el nombre de la empresa aparezca en el campo "EMPRESA"
   - Verificar que el teléfono del cliente aparezca en el campo "TELÉFONO"

3. **Imprimir Vale Individual:**
   - Ir a Vales > Ver detalle de un vale
   - Hacer clic en "Imprimir Vale"
   - Verificar que se abra `/vouchers/printSingle/{id}` en nueva ventana
   - Verificar que se muestre el vale con información del cliente

## Archivos Modificados

### Modelos
- `app/models/Voucher.php`
  - Agregado soporte para `client_id` en consultas
  - Actualizado método `create()` para incluir `client_id`
  - Actualizado método `generateBatch()` para incluir `client_id`
  - **NUEVO**: Validación en `generateUniqueQRCode()` para prevenir QR vacíos
  - Incluir información del cliente en consultas (JOIN con tabla clients)

### Controladores
- `app/controllers/VoucherController.php`
  - Actualizado método `create()` para cargar lista de clientes
  - Actualizado método `store()` para validar y guardar `client_id`
  - Agregado método `printSingle()` para imprimir vales individuales
  - Corregida URL de redirección a `printBatch`
  - **NUEVO**: Mensajes de error mejorados con detalles específicos

### Vistas
- `app/views/vouchers/create.php`
  - Agregado campo select para "Seleccionar Cliente" (requerido)
  
- `app/views/vouchers/print_batch.php`
  - Actualizado para mostrar nombre de cliente en campo "EMPRESA"
  - Actualizado para mostrar teléfono de cliente en campo "TELÉFONO"

- `app/views/vouchers/view.php`
  - Corregida URL para imprimir vale individual (`printSingle`)

### Migraciones
- `config/fix_vouchers_duplicates.sql` (NUEVO - CRÍTICO)
  - Script SQL para limpiar duplicados y estructura corrupta
  - Elimina vouchers con QR codes vacíos o inválidos
  - Crea tabla con estructura correcta si no existe
  - Agrega constraints faltantes de manera segura

- `config/fix_vouchers_client_field.sql`
  - Script SQL para agregar columna `client_id` a tabla `vouchers`

## Validaciones Implementadas

1. **En el modelo (`generateUniqueQRCode`):**
   - Serie no puede estar vacía
   - Folio debe ser mayor a 0
   - QR code generado no puede estar vacío
   - QR code debe tener longitud mínima de 10 caracteres
   - Verificación final de que el QR no exista en BD

2. **En el controlador (`store`):**
   - `client_id` es obligatorio
   - Serie debe ser solo letras A-Z (máx 10 caracteres)
   - Folio inicial debe ser >= 1
   - Cantidad debe estar entre 1 y 1000
   - Capacidad debe ser >= 1

3. **Mensajes de error mejorados:**
   - Ahora muestran el error específico de cada vale que falló
   - Indica exactamente qué serie/folio causó el problema

## Solución de Problemas

### Error: "Duplicate entry '' for key 'voucher_code'"
**Causa:** Datos existentes con QR codes vacíos o inválidos en la base de datos
**Solución:** 
1. Ejecutar `config/fix_vouchers_duplicates.sql` para limpiar datos corruptos
2. Reintentar generación de vales

### Error: "No se pudo generar ningún vale" con error específico mostrado
**Causa:** El nuevo sistema ahora muestra el error exacto
**Solución:** Leer el mensaje de error que indica la causa específica (ej: "Ya existe un vale con la serie X y folio Y")

### Campo "Seleccionar Cliente" no aparece
**Causa:** No se cargaron los clientes en el controlador o hay error en la vista
**Solución:** 
1. Verificar que existan clientes activos en la tabla `clients`
2. Verificar que los cambios en `VoucherController.php` y `create.php` estén aplicados

### Información del cliente no aparece en vales impresos
**Causa:** Falta la relación con la tabla clients en consultas
**Solución:** Verificar que las actualizaciones en `Voucher.php` estén aplicadas (LEFT JOIN con clients)

### Error: "Serie y folio son requeridos para generar el código QR"
**Causa:** Los campos serie o folio están vacíos o son inválidos
**Solución:** Verificar que el formulario esté enviando datos válidos

## Notas Importantes

- **CRÍTICO**: Ejecutar `fix_vouchers_duplicates.sql` ANTES de `fix_vouchers_client_field.sql`
- **Vales existentes:** Los vales creados antes de esta actualización no tendrán `client_id` asignado (será NULL)
- **Integridad referencial:** Si se elimina un cliente, el `client_id` de sus vales se establecerá en NULL (ON DELETE SET NULL)
- **Limpieza de datos:** El script de migración elimina automáticamente vouchers con QR codes inválidos
- **Impresión:** La información del cliente solo se mostrará en vales que tengan un cliente asignado

## Verificación Post-Instalación

Ejecutar las siguientes consultas para verificar que todo está correcto:

```sql
-- 1. Verificar que no haya QR codes vacíos
SELECT COUNT(*) as empty_qr FROM vouchers WHERE qr_code = '' OR qr_code IS NULL;
-- Resultado esperado: 0

-- 2. Verificar estructura de la tabla
SHOW CREATE TABLE vouchers;
-- Debe incluir: client_id, UNIQUE KEY idx_qr_code, foreign keys

-- 3. Verificar que los clientes existan
SELECT COUNT(*) as total_clients FROM clients WHERE status = 'active';
-- Debe ser > 0 para poder generar vales

-- 4. Probar generación de un vale de prueba
-- (usar la interfaz web para esto)
```
