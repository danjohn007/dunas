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

4. ✅ **Corrección de duplicados de QR**
   - El código de generación de QR ya incluye validación para evitar duplicados
   - La generación de códigos QR usa timestamps únicos

## Pasos de Instalación

### 1. Aplicar Migración de Base de Datos

Ejecutar el siguiente script SQL en la base de datos:

```bash
mysql -u root -p nombre_base_datos < config/fix_vouchers_client_field.sql
```

O ejecutar manualmente:

```sql
-- Add client_id column to vouchers table
ALTER TABLE `vouchers` 
ADD COLUMN `client_id` int(11) DEFAULT NULL AFTER `created_by`,
ADD KEY `idx_client_id` (`client_id`),
ADD CONSTRAINT `fk_vouchers_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL;

-- Add comment to column
ALTER TABLE `vouchers` MODIFY COLUMN `client_id` int(11) DEFAULT NULL COMMENT 'ID del cliente asociado al vale';
```

### 2. Verificar Cambios

```sql
-- Verificar estructura de la tabla
DESCRIBE vouchers;

-- Debería mostrar:
-- - client_id (int, NULL, after created_by)
-- - Índice idx_client_id
-- - Foreign key fk_vouchers_client
```

### 3. Probar Funcionalidad

1. **Crear Vales:**
   - Ir a Vales > Generar Vales
   - Verificar que el campo "Seleccionar Cliente" sea visible y requerido
   - Seleccionar un cliente
   - Llenar los demás campos (Serie, Folio Inicial, Cantidad, Capacidad)
   - Generar vales
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
  - Incluir información del cliente en consultas (JOIN con tabla clients)

### Controladores
- `app/controllers/VoucherController.php`
  - Actualizado método `create()` para cargar lista de clientes
  - Actualizado método `store()` para validar y guardar `client_id`
  - Agregado método `printSingle()` para imprimir vales individuales
  - Corregida URL de redirección a `printBatch`

### Vistas
- `app/views/vouchers/create.php`
  - Agregado campo select para "Seleccionar Cliente" (requerido)
  
- `app/views/vouchers/print_batch.php`
  - Actualizado para mostrar nombre de cliente en campo "EMPRESA"
  - Actualizado para mostrar teléfono de cliente en campo "TELÉFONO"

- `app/views/vouchers/view.php`
  - Corregida URL para imprimir vale individual (`printSingle`)

### Migraciones
- `config/fix_vouchers_client_field.sql` (nuevo)
  - Script SQL para agregar columna `client_id` a tabla `vouchers`

## Validaciones Implementadas

1. El campo `client_id` es **obligatorio** al generar vales
2. El sistema valida que el cliente exista antes de crear vales
3. Si no se selecciona un cliente, se muestra el mensaje: "Todos los campos son requeridos, incluyendo la selección de cliente."

## Notas Importantes

- **Vales existentes:** Los vales creados antes de esta actualización no tendrán `client_id` asignado (será NULL)
- **Integridad referencial:** Si se elimina un cliente, el `client_id` de sus vales se establecerá en NULL (ON DELETE SET NULL)
- **Impresión:** La información del cliente solo se mostrará en vales que tengan un cliente asignado

## Solución de Problemas

### Error 404 en páginas de impresión
- **Causa:** URLs con guiones en lugar de camelCase
- **Solución:** URLs actualizadas a `/vouchers/printBatch` y `/vouchers/printSingle/{id}`

### Campo "Seleccionar Cliente" no aparece
- **Causa:** No se cargaron los clientes en el controlador
- **Solución:** El método `create()` ahora carga la lista de clientes activos

### Información del cliente no aparece en vales impresos
- **Causa:** Falta la relación con la tabla clients en consultas
- **Solución:** Se agregaron LEFT JOIN con tabla clients en métodos del modelo

### Error de duplicado de QR
- **Causa:** Código QR vacío o duplicado
- **Solución:** El método `generateUniqueQRCode()` genera códigos únicos con timestamp
