# Guía de Despliegue - Actualización de Vales

## Resumen de Cambios

Esta actualización implementa las siguientes mejoras al módulo de vales:

1. **Códigos QR más cortos**: Formato simplificado `SERIE-FOLIO` (ej: `B-500`)
2. **Registro con vales**: El sistema de acceso rápido ahora acepta códigos de vale
3. **Estado "Registrado"**: Nuevo estado para vales usados en acceso
4. **Tracking financiero**: Campos de costo y estado de pago (Pagado/Pendiente)
5. **Reporte financiero mejorado**: Incluye estadísticas de vales pagados y pendientes
6. **Impresión reducida**: Tamaño de impresión 30% más pequeño

## Pasos de Despliegue

### 1. Backup de Base de Datos

```bash
# Crear backup antes de cualquier cambio
mysqldump -u usuario -p nombre_base_datos > backup_pre_actualizacion_$(date +%Y%m%d).sql
```

### 2. Ejecutar Migración de Base de Datos

```bash
# Conectarse a MySQL
mysql -u usuario -p nombre_base_datos

# Ejecutar el script de migración
source /ruta/al/proyecto/migrations/update_vouchers_qr_and_payment.sql
```

La migración agrega:
- Campo `cost` (DECIMAL 10,2)
- Campo `payment_status` (ENUM 'paid'|'pending')
- Actualiza enum de `status` para incluir 'registered'
- Índices para optimizar consultas financieras

### 3. Actualizar Archivos del Proyecto

```bash
# Respaldar archivos actuales
cp -r app app_backup_$(date +%Y%m%d)
cp -r public public_backup_$(date +%Y%m%d)

# Copiar nuevos archivos
# (usar git pull o copiar manualmente los archivos modificados)
```

### 4. Verificar Permisos

```bash
# Asegurar permisos correctos
chmod 644 migrations/update_vouchers_qr_and_payment.sql
chmod 644 app/models/Voucher.php
chmod 644 app/controllers/*.php
chmod 644 app/views/**/*.php
```

### 5. Pruebas Post-Despliegue

#### Prueba 1: Generación de Vales
1. Ir a `/public/vouchers/create`
2. Llenar formulario con:
   - Cliente
   - Serie (ej: `B`)
   - Folio inicial (ej: `1000`)
   - Cantidad (ej: `5`)
   - Capacidad (ej: `10000`)
   - **Costo (ej: `250.00`)**
   - **Estado de pago (Pagado o Pendiente)**
3. Generar vales
4. Verificar que el código QR tenga formato corto: `B-1000`, `B-1001`, etc.

#### Prueba 2: Impresión
1. Imprimir los vales generados
2. Verificar que el tamaño sea aproximadamente 30% más pequeño
3. Verificar que el código QR sea escaneable

#### Prueba 3: Registro con Vale
1. Ir a `/public/access/quickRegistration`
2. En el campo "Registro Manual", escribir un código de vale (ej: `B-1000`)
3. Click en "Registrar"
4. Verificar que:
   - El sistema reconoce el vale
   - Muestra "Vale Válido" con los datos
   - Permite completar el registro

#### Prueba 4: Estado Registrado
1. Completar el registro con el vale
2. Ir a `/public/vouchers`
3. Verificar que el vale usado muestre estado "Registrado"
4. Intentar usar el mismo vale nuevamente
5. Verificar que el sistema rechace el vale por estar registrado

#### Prueba 5: Reporte Financiero
1. Ir a `/public/reports/financial`
2. Verificar que aparezca la sección "Vales Generados"
3. Verificar que muestre:
   - Total pagados (ingresos)
   - Total pendientes (cuentas por cobrar)
   - Cantidad de vales por estado
4. Verificar que los vales pagados se sumen al total de ingresos

### 6. Script de Pruebas Automatizado

```bash
# Ejecutar script de pruebas
php test_voucher_changes.php
```

Debe mostrar todos los tests como PASS.

## Vales Existentes

### Compatibilidad
- Los vales existentes con formato largo siguen funcionando
- Pueden ser usados normalmente
- Solo los vales nuevos tendrán formato corto

### Actualización de Vales Existentes (Opcional)
Si desea actualizar vales existentes para usar formato corto:

```sql
-- PRECAUCIÓN: Hacer backup antes
UPDATE vouchers 
SET qr_code = CONCAT(serie, '-', folio)
WHERE status = 'active'
  AND qr_code LIKE '%-%--%';  -- Solo los que tienen timestamp
```

## Rollback

Si necesita revertir los cambios:

### 1. Restaurar Base de Datos
```bash
mysql -u usuario -p nombre_base_datos < backup_pre_actualizacion_YYYYMMDD.sql
```

### 2. Restaurar Archivos
```bash
rm -rf app
rm -rf public
mv app_backup_YYYYMMDD app
mv public_backup_YYYYMMDD public
```

## Notas Importantes

1. **Nuevos Campos Obligatorios**: Al crear vales ahora se requiere costo y estado de pago
2. **Estado Registrado vs Usado**: 
   - "Registrado": Vale usado en acceso rápido
   - "Usado": Vale usado en transacción tradicional
3. **Formato de Vale**: El código debe tener formato `LETRA(S)-NUMERO` (mínimo 4 caracteres)
4. **Reporte Financiero**: Los vales se cuentan como:
   - Pagados = Ingresos realizados
   - Pendientes = Cuentas por cobrar (crédito)

## Soporte

Si encuentra algún problema:
1. Verificar logs de PHP en `/logs` o `/var/log/apache2/error.log`
2. Verificar logs de MySQL
3. Ejecutar script de pruebas: `php test_voucher_changes.php`
4. Revisar que la migración se aplicó correctamente: `DESCRIBE vouchers;`

## Contacto

Para soporte adicional, contactar al equipo de desarrollo.
