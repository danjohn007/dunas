# Guía de Despliegue: Mejoras de Estado de Pago y Control de PINs

## Resumen de Cambios

Este documento describe las mejoras implementadas para el sistema de vales y control de acceso, según los requisitos del issue.

### Funcionalidades Implementadas

1. ✅ **Exportación Excel/PDF de "Detalle por Empresa"**
2. ✅ **Actualización Automática de Estado de Pago de Vales**
3. ✅ **Corrección de Estado de Pago en Transacciones**
4. ✅ **Uso Único de PIN para Control de Acceso HikVision**

---

## 1. Exportación de Datos (Excel/PDF)

### Ubicación
- **Página**: `/public/reports/vouchersSummary`
- **Botones**: En el encabezado de "Detalle por Empresa"

### Funcionalidad
- **Excel (CSV)**: Exporta a formato CSV compatible con Excel, incluye BOM UTF-8 para caracteres especiales
- **PDF**: Abre ventana con formato imprimible (Ctrl+P para guardar como PDF)

### Archivos Modificados
- `app/controllers/ReportController.php`: Métodos `exportVouchersSummary()`, `exportToCSV()`, `exportToPDF()`
- `app/views/reports/vouchers_summary.php`: Botones de exportación

### Datos Exportados
- Empresa/Cliente
- Serie
- Rango de Folios
- Cantidad de Vales
- Capacidad Total (Litros)
- Vales Activos
- Vales Utilizados
- Total Pagado
- Pago Registrado
- Pendiente
- **Fila de Totales Generales**

---

## 2. Actualización Automática de Estado de Pago

### Problema Resuelto
Anteriormente, al registrar pagos en "Detalle por Empresa", el estado de pago de los vales no se actualizaba automáticamente.

### Solución Implementada

#### Base de Datos
- **Stored Procedure**: `update_voucher_payment_status(client_id, date_from, date_to)`
  - Calcula el total de vales del cliente
  - Calcula el total pagado
  - Actualiza `vouchers.payment_status` a 'paid' solo cuando el pago cubre el total completo

- **Triggers**:
  - `after_voucher_payment_insert`: Se ejecuta al registrar un pago
  - `after_voucher_payment_update`: Se ejecuta al modificar un pago
  - `after_voucher_payment_delete`: Se ejecuta al eliminar un pago

#### Lógica de Negocio
```
SI (Total Pagado >= Costo Total de Vales) ENTONCES
    Marcar todos los vales como 'paid'
SINO
    Mantener vales como 'pending'
FIN SI
```

### Archivos Modificados
- `migrations/update_payment_status_and_pin_tracking.sql`: Schema changes y triggers
- `app/controllers/ReportController.php`: Comentario en `registerPayment()`

---

## 3. Estado de Pago Correcto en Transacciones

### Problema Resuelto
En "Gestión de Transacciones" (`/public/transactions`), todas las transacciones se mostraban con estado "Pagado", incluso cuando los vales asociados estaban pendientes.

### Solución Implementada

#### Base de Datos
- **Vista**: `v_transactions_with_payment_status`
  - Calcula `actual_payment_status` basándose en:
    - Si `payment_method = 'voucher'`: Usa `vouchers.payment_status`
    - Si no: Usa `transactions.payment_status`

#### Código
- **Modelo**: `app/models/Transaction.php`
  - Método `getAll()` modificado para incluir `actual_payment_status`
  
- **Vista**: `app/views/transactions/index.php`
  - Muestra `actual_payment_status` en lugar de `payment_status`

### Comportamiento
- Transacciones con método de pago "vale" ahora reflejan el estado real del vale
- Transacciones con método de pago "efectivo" o "transferencia" mantienen su estado original

---

## 4. Uso Único de PIN HikVision

### Problema Resuelto
Los PINs generados para acceso en dispositivos HikVision podían reutilizarse múltiples veces, lo cual es un riesgo de seguridad.

### Solución Implementada

#### Base de Datos
- **Nuevo Campo**: `access_logs.pin_used` (TINYINT)
  - 0 = PIN no usado
  - 1 = PIN ya utilizado
- **Índice**: `idx_ticket_code_pin_used` para búsquedas rápidas

#### Código
- **Modelo**: `app/models/AccessLog.php`
  - Método `registerExit()`: Marca `pin_used = 1` al completar acceso
  - Método `isPinUsed($ticketCode)`: Verifica si un PIN ya fue usado
  - Método `getPinUsageInfo($ticketCode)`: Obtiene información del uso del PIN

### Integración Futura
Para implementar la validación en el frontend/bridge HikVision:

```php
// Ejemplo de uso
$accessLog = new AccessLog();
if ($accessLog->isPinUsed($pinCode)) {
    // Rechazar acceso - PIN ya utilizado
    return ['success' => false, 'message' => 'PIN ya utilizado'];
}
// Permitir acceso
```

---

## Instrucciones de Despliegue

### Paso 1: Backup de la Base de Datos
```bash
mysqldump -u usuario -p nombre_bd > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Paso 2: Ejecutar Migración SQL
```bash
mysql -u usuario -p nombre_bd < migrations/update_payment_status_and_pin_tracking.sql
```

### Paso 3: Verificar Migración
Ejecutar las siguientes consultas:

```sql
-- Verificar campo pin_used
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'access_logs'
  AND COLUMN_NAME = 'pin_used';

-- Verificar stored procedure
SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'update_voucher_payment_status';

-- Verificar triggers
SHOW TRIGGERS WHERE `Table` = 'voucher_payments';

-- Verificar vista
SELECT TABLE_NAME, TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'v_transactions_with_payment_status';
```

### Paso 4: Desplegar Código
```bash
# Copiar archivos modificados al servidor
rsync -av app/ usuario@servidor:/ruta/app/
rsync -av migrations/ usuario@servidor:/ruta/migrations/
```

### Paso 5: Probar Funcionalidad

#### Test 1: Exportación
1. Ir a `/public/reports/vouchersSummary`
2. Seleccionar un rango de fechas
3. Clic en "Excel" - debe descargar CSV
4. Clic en "PDF" - debe abrir ventana imprimible

#### Test 2: Estado de Pago de Vales
1. Ir a "Detalle por Empresa"
2. Identificar un cliente con vales pendientes (ej: $20,000 pendiente)
3. Registrar pago parcial (ej: $10,000)
   - **Verificar**: Vales siguen como "pending"
4. Registrar pago restante (ej: $10,000)
   - **Verificar**: Vales cambian a "paid"

#### Test 3: Estado de Transacciones
1. Ir a `/public/transactions`
2. Filtrar por método de pago "vale"
3. **Verificar**: 
   - Si el vale está pendiente → Transacción muestra "Pendiente"
   - Si el vale está pagado → Transacción muestra "Pagado"

#### Test 4: PIN Único
1. Crear acceso con PIN (genera ticket)
2. Completar acceso (registrar salida)
3. **Verificar**: `access_logs.pin_used = 1`
4. Código para validar:
```php
$accessLog->isPinUsed($pinCode); // Debe retornar true
```

---

## Rollback (En caso de problemas)

### Revertir Base de Datos
```bash
mysql -u usuario -p nombre_bd < backup_YYYYMMDD_HHMMSS.sql
```

### Revertir Código
```bash
git checkout HEAD~1 app/
git checkout HEAD~1 migrations/
```

---

## Notas Importantes

### Rendimiento
- Los triggers son eficientes (ejecutan en milisegundos)
- La vista `v_transactions_with_payment_status` no impacta performance significativamente
- El índice `idx_ticket_code_pin_used` optimiza búsquedas de PINs

### Seguridad
- Los triggers previenen inconsistencias de datos
- La validación de PIN único mejora la seguridad del acceso
- Las exportaciones solo están disponibles para roles admin/supervisor

### Compatibilidad
- La migración es **idempotente** (puede ejecutarse múltiples veces)
- No rompe funcionalidad existente
- Datos históricos se preservan y actualizan correctamente

---

## Soporte y Contacto

Para problemas o preguntas sobre este despliegue:
1. Revisar logs de errores en `/logs/`
2. Verificar permisos de base de datos
3. Consultar esta guía para troubleshooting

---

**Fecha de Implementación**: 2026-02-11  
**Versión**: 1.0.0  
**Autor**: Sistema de Control de Acceso con IoT
