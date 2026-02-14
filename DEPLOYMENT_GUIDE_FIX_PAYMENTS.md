# Guía de Despliegue - Correcciones de Reportes Financieros y Pagos por Lote

## Fecha: 2026-02-14

## Resumen de Cambios

Esta actualización resuelve los problemas críticos en el sistema de pagos de vales:

### Problemas Resueltos:

1. ✅ **Pagos parciales no se reflejan en TOTALES** - Ahora todos los pagos parciales se incluyen en los totales del Reporte Financiero
2. ✅ **Pagos se aplican incorrectamente a todos los lotes** - Cada lote de vales consecutivos ahora mantiene seguimiento independiente de pagos
3. ✅ **Máximo pago se replica en todos los registros** - Cada lote mantiene su propio estado de pago sin afectar otros lotes del mismo cliente
4. ✅ **Impresión de vales** - Layout de impresión ya optimizado (sin teléfono, serie, folio)

### Mejoras Implementadas:

- **Pagos por Lote**: Sistema de pagos ahora identifica y rastrea cada lote independientemente usando `serie + folio_inicio + folio_fin`
- **Pagos Parciales**: Los pagos parciales se registran y reflejan correctamente en los totales
- **Independencia de Lotes**: Múltiples lotes del mismo cliente mantienen cuentas separadas
- **Totales Precisos**: Reporte Financiero ahora incluye todos los pagos (completos y parciales)

---

## Archivos Modificados

### Base de Datos:
- `migrations/fix_payment_per_batch.sql` - **NUEVO** - Migración principal

### Modelos:
- `app/models/VoucherPayment.php` - Soporte para campos de lote
- `app/controllers/ReportController.php` - Lógica de pagos por lote

### Vistas:
- `app/views/reports/vouchers_summary.php` - Modal de pagos con información de lote
- `app/views/reports/financial.php` - Visualización de pagos parciales

---

## Pasos de Despliegue

### Paso 1: Respaldo de Base de Datos ⚠️

**IMPORTANTE: Crear respaldo antes de continuar**

```bash
mysqldump -u [usuario] -p [nombre_db] > backup_antes_fix_pagos_$(date +%Y%m%d_%H%M%S).sql
```

### Paso 2: Aplicar Migración de Base de Datos

```bash
cd /ruta/al/proyecto
mysql -u [usuario] -p [nombre_db] < migrations/fix_payment_per_batch.sql
```

### Paso 3: Verificar Migración

Ejecutar en MySQL para verificar que la migración se aplicó correctamente:

```sql
-- 1. Verificar columnas agregadas a voucher_payments
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'voucher_payments'
  AND COLUMN_NAME IN ('serie', 'folio_inicio', 'folio_fin');

-- Resultado esperado: 3 filas con las columnas serie, folio_inicio, folio_fin

-- 2. Verificar índice compuesto
SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX)
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'voucher_payments'
  AND INDEX_NAME = 'idx_client_serie_folio'
GROUP BY INDEX_NAME;

-- Resultado esperado: índice con columnas client_id, serie, folio_inicio, folio_fin

-- 3. Verificar stored procedure
SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'update_voucher_payment_status_per_batch';

-- Resultado esperado: 1 fila con el procedimiento

-- 4. Verificar triggers
SHOW TRIGGERS WHERE `Table` = 'voucher_payments';

-- Resultado esperado: 3 triggers (insert, update, delete)
```

### Paso 4: Actualizar Código de la Aplicación

```bash
# Desde el repositorio
git pull origin copilot/fix-financial-report-issues

# O si ya tienes los archivos actualizados
# Solo asegúrate de que los archivos están en su lugar
```

### Paso 5: Limpiar Caché (si aplica)

```bash
# Si tu aplicación tiene caché, límpiala
rm -rf cache/*
# O según tu configuración
```

---

## Pruebas Post-Despliegue

### Test 1: Registro de Pago en un Solo Lote

1. Ir a **Reportes → Resumen de Vales Generados**
2. Buscar un cliente con un lote pendiente
3. Hacer clic en **"Registrar Pago"**
4. Verificar que el modal muestra:
   - Nombre del cliente
   - **Serie del lote** (ej: "L", "H", "MP")
   - **Rango de folios** (ej: "1000 - 1039")
   - Monto pendiente correcto
5. Registrar un **pago parcial** (menos del total)
6. Verificar que:
   - El pago se registró exitosamente
   - El monto pendiente se actualizó correctamente
   - Otros lotes del mismo cliente NO se afectaron

### Test 2: Múltiples Lotes del Mismo Cliente

**Escenario:**
- Cliente "Hotel Gran Plaza" tiene 3 lotes:
  - Lote 1: Serie "L", folios 1000-1039, total $80,000
  - Lote 2: Serie "H", folios 0010-0109, total $200,000
  - Lote 3: Serie "MP", folios 0100-0199, total $200,000

**Pasos:**
1. Registrar pago de $40,000 para Lote 1 (Serie L)
2. Verificar que:
   - ✅ Lote 1 muestra $40,000 pagado, $40,000 pendiente
   - ✅ Lote 2 sigue mostrando $0 pagado, $200,000 pendiente
   - ✅ Lote 3 sigue mostrando $0 pagado, $200,000 pendiente
3. Registrar pago de $200,000 para Lote 2 (Serie H)
4. Verificar que:
   - ✅ Lote 2 ahora muestra como PAGADO completamente
   - ✅ Lote 1 NO cambió (sigue parcialmente pagado)
   - ✅ Lote 3 NO cambió

### Test 3: Totales en Reporte Financiero

1. Ir a **Reportes → Reporte Financiero**
2. Seleccionar rango de fechas
3. Verificar sección "Vales Generados":
   - ✅ **Pagados**: Muestra vales marcados como pagados completamente
   - ✅ **Pagos Parciales**: Muestra suma de pagos parciales registrados (NUEVO)
   - ✅ **Pendientes**: Muestra vales pendientes
   - ✅ **Total**: Suma correcta de todo lo anterior
4. Verificar que **Total Ingresos** incluye pagos parciales

### Test 4: Resumen de Vales por Empresa

1. Ir a **Reportes → Resumen de Vales Generados**
2. Buscar empresa con múltiples lotes
3. Verificar que cada fila (lote) muestra:
   - Serie correcta
   - Rango de folios correcto
   - Montos pagado y pendiente independientes
   - Botón "Registrar Pago" solo si hay pendiente > 0

---

## Verificación de Funcionalidad

### Checklist de Verificación:

- [ ] La migración SQL se ejecutó sin errores
- [ ] Las columnas serie, folio_inicio, folio_fin existen en voucher_payments
- [ ] El stored procedure update_voucher_payment_status_per_batch existe
- [ ] Los 3 triggers (insert, update, delete) existen
- [ ] El modal de "Registrar Pago" muestra información del lote
- [ ] Se puede registrar un pago parcial
- [ ] Los pagos parciales se reflejan en el monto pendiente
- [ ] Múltiples lotes del mismo cliente son independientes
- [ ] El Reporte Financiero muestra sección "Pagos Parciales"
- [ ] Los totales incluyen pagos parciales
- [ ] No hay errores en logs de PHP
- [ ] No hay errores en logs de MySQL

---

## Rollback (En Caso de Problemas)

Si surge algún problema crítico, seguir estos pasos para revertir:

### 1. Restaurar Base de Datos

```bash
mysql -u [usuario] -p [nombre_db] < backup_antes_fix_pagos_[fecha].sql
```

### 2. Revertir Código

```bash
git checkout [commit_anterior]
```

### 3. Limpiar Caché

```bash
rm -rf cache/*
```

---

## Comportamiento del Sistema

### Antes de la Actualización ❌

**Problema:**
- Cliente tiene 2 lotes: Lote A ($100,000) y Lote B ($200,000)
- Se registra pago de $100,000
- **Error**: Sistema marca AMBOS lotes como pagados o aplica el máximo a ambos
- **Resultado**: Datos incorrectos, pérdida de seguimiento

### Después de la Actualización ✅

**Solución:**
- Cliente tiene 2 lotes: Lote A ($100,000) y Lote B ($200,000)
- Se registra pago de $100,000 **para Lote A específicamente**
- **Correcto**: Solo Lote A se marca como pagado
- **Lote B**: Se mantiene independiente, sin cambios
- **Resultado**: Seguimiento preciso por lote

---

## Estructura de la Tabla voucher_payments (Actualizada)

```sql
CREATE TABLE `voucher_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `serie` varchar(10) DEFAULT NULL COMMENT 'Serie del lote',
  `folio_inicio` int(11) DEFAULT NULL COMMENT 'Folio inicial del lote',
  `folio_fin` int(11) DEFAULT NULL COMMENT 'Folio final del lote',
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','transfer','check','other') NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_client_serie_folio` (`client_id`,`serie`,`folio_inicio`,`folio_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Notas Importantes

### Retrocompatibilidad ⚠️

- **Pagos antiguos sin serie/folio**: Los triggers mantienen retrocompatibilidad
- Si un pago NO tiene serie/folio (pagos antiguos), usará el comportamiento anterior
- Nuevos pagos SIEMPRE deben incluir serie/folio

### Integridad de Datos

- La migración es **idempotente** - puede ejecutarse múltiples veces sin errores
- Los triggers automáticamente actualizan el estado de los vouchers
- No se requiere actualización manual de datos existentes

### Seguridad

- Todos los cambios mantienen las restricciones de permisos existentes
- Solo usuarios con rol 'admin' o 'supervisor' pueden registrar pagos
- Los triggers se ejecutan con los permisos del usuario actual

---

## Soporte y Troubleshooting

### Error: "Column 'serie' already exists"

**Causa**: La migración ya se ejecutó previamente
**Solución**: No es un error crítico - la migración es idempotente

### Error: "Procedure 'update_voucher_payment_status_per_batch' already exists"

**Causa**: El procedimiento ya existe
**Solución**: No es un error crítico - la migración elimina y recrea

### No se actualizan los montos pendientes

**Verificar**:
1. Que la migración se aplicó completamente
2. Que los triggers existen: `SHOW TRIGGERS WHERE \`Table\` = 'voucher_payments';`
3. Logs de MySQL para errores de triggers

### Pagos no se reflejan en Reporte Financiero

**Verificar**:
1. Que el método `getTotalPaymentsInPeriod` existe en VoucherPayment.php
2. Que el rango de fechas incluye los pagos registrados
3. Cache de la aplicación está limpio

---

## Contacto

Para reportar problemas o dudas sobre esta actualización:
- Crear un issue en el repositorio
- Incluir logs relevantes
- Describir los pasos para reproducir el problema

---

## Changelog

### v1.0.0 - 2026-02-14

#### Agregado
- Campos `serie`, `folio_inicio`, `folio_fin` en tabla `voucher_payments`
- Stored procedure `update_voucher_payment_status_per_batch`
- Método `getTotalPaymentsInPeriod` en VoucherPayment model
- Sección "Pagos Parciales" en Reporte Financiero
- Información de lote en modal de registro de pagos

#### Modificado
- Triggers de `voucher_payments` para actualizar por lote
- Método `getTotalPaidByClient` para soportar filtrado por lote
- Método `create` y `update` de VoucherPayment para incluir campos de lote
- Controller `registerPayment` para pasar información de lote
- Vista `vouchers_summary.php` para mostrar y enviar datos de lote
- Vista `financial.php` para mostrar pagos parciales

#### Corregido
- Pagos parciales ahora se reflejan en totales
- Cada lote mantiene seguimiento independiente
- Pagos no se replican entre lotes del mismo cliente
- Cálculo correcto de montos pendientes por lote

---

**Fecha de Despliegue Planeada**: Inmediatamente después de pruebas
**Tiempo Estimado de Downtime**: 2-3 minutos (aplicación de migración)
**Requiere Reinicio de Servicios**: No (solo aplicar migración SQL)

---

✅ **Actualización preparada y lista para desplegar**
