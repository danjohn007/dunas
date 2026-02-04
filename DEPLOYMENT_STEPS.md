# Pasos de Despliegue - Módulo de Vales

## Pre-requisitos
- Acceso a la base de datos MySQL
- Acceso al servidor web (PHP)
- Permisos de administrador en el sistema

## 1. Aplicar Migración de Base de Datos

```bash
# Conectar a MySQL
mysql -u systemco_dunas -p

# Seleccionar base de datos
USE systemco_dunas;

# Ejecutar migración
SOURCE /ruta/completa/dunas/config/update_vouchers_module.sql;

# Verificar que la tabla se creó correctamente
DESCRIBE vouchers;
SHOW INDEX FROM vouchers;

# Salir
EXIT;
```

## 2. Verificar Archivos Desplegados

Confirmar que los siguientes archivos existen en el servidor:

### Nuevos Archivos
- [ ] `config/update_vouchers_module.sql`
- [ ] `app/models/Voucher.php`
- [ ] `app/controllers/VoucherController.php`
- [ ] `app/views/vouchers/index.php`
- [ ] `app/views/vouchers/create.php`
- [ ] `app/views/vouchers/print_batch.php`
- [ ] `app/views/vouchers/view.php`

### Archivos Modificados
- [ ] `public/index.php`
- [ ] `app/views/layouts/main.php`
- [ ] `app/controllers/AccessController.php`
- [ ] `app/views/access/create.php`
- [ ] `app/views/access/quick_registration.php`

## 3. Permisos de Archivos

```bash
# Asegurar permisos correctos
cd /ruta/completa/dunas
chmod 644 config/update_vouchers_module.sql
chmod 644 app/models/Voucher.php
chmod 644 app/controllers/VoucherController.php
chmod 644 app/views/vouchers/*.php
```

## 4. Limpiar Cache (si aplica)

```bash
# Si usa OPcache
# En el servidor, ejecutar:
php -r "opcache_reset();"

# O reiniciar PHP-FPM
sudo systemctl restart php-fpm
# o
sudo service php8.3-fpm restart
```

## 5. Pruebas Post-Despliegue

### 5.1 Verificar Acceso al Módulo

1. Iniciar sesión como **Administrador**
2. Verificar que aparece "Vales" en el menú lateral
3. Click en "Vales" → debe cargar sin errores

### 5.2 Generar Lote de Prueba

1. Click en "Generar Vales"
2. Completar formulario:
   - Serie: **PRUEBA**
   - Folio inicial: **1**
   - Cantidad: **5**
   - Capacidad: **1000**
3. Click en "Generar Vales"
4. Verificar que se generan 5 vales
5. Debe abrir vista de impresión

### 5.3 Verificar Impresión

1. En la vista de impresión, verificar:
   - [ ] Se muestran todos los vales generados
   - [ ] Los códigos QR se visualizan correctamente
   - [ ] El diseño es correcto (1/2 carta)
2. Click en "Imprimir" (opcional)
3. Cerrar vista de impresión

### 5.4 Verificar Listado

1. En "Vales", verificar:
   - [ ] Aparecen los 5 vales creados
   - [ ] Estadísticas correctas (5 activos)
   - [ ] Filtros funcionan
2. Click en uno de los vales
3. Verificar vista de detalle con QR

### 5.5 Probar Validación en Registro

1. Ir a "Accesos" → "Registrar Entrada"
2. Seleccionar método de pago: **Vales**
3. Debe aparecer campo de validación
4. Copiar código QR de un vale (de la vista de detalle)
5. Pegar en el campo y click "Validar"
6. Debe mostrar mensaje verde de validación exitosa
7. Completar registro normalmente
8. Verificar que el vale se marca como "Usado"

### 5.6 Verificar No Reutilización

1. Intentar usar el mismo vale de nuevo
2. Al validar, debe mostrar error: "Este vale ya fue utilizado"
3. No debe permitir continuar con el registro

### 5.7 Probar Cancelación

1. En "Vales", seleccionar un vale activo
2. Click en el ícono de cancelar (🚫)
3. Confirmar cancelación
4. Vale debe cambiar a estado "Cancelado"
5. Intentar usarlo → debe dar error

## 6. Verificación de Base de Datos

```sql
-- Verificar vales creados
SELECT * FROM vouchers ORDER BY created_at DESC LIMIT 10;

-- Verificar estadísticas
SELECT 
    status,
    COUNT(*) as total,
    SUM(capacity) as total_capacity
FROM vouchers
GROUP BY status;

-- Verificar vales usados
SELECT 
    v.serie,
    v.folio,
    v.used_at,
    a.ticket_code
FROM vouchers v
LEFT JOIN access_logs a ON v.used_by_access_log_id = a.id
WHERE v.status = 'used';
```

## 7. Configuración de HikVision (Opcional)

Si se tiene acceso físico al lector HikVision DS-K1T502DBWX:

### 7.1 Configuración del Dispositivo
1. Acceder a la interfaz web del dispositivo
2. Configurar modo de lectura: **QR Code**
3. Nivel de corrección de error: **High (H)**
4. Timeout de lectura: **5 segundos**

### 7.2 Configuración del Webhook
1. URL de callback: `https://tu-dominio.com/vouchers/validateQR`
2. Método: **POST**
3. Parámetro: `qr_code`
4. Timeout: **10 segundos**

### 7.3 Prueba Física
1. Imprimir un vale de prueba
2. Presentar al lector
3. Verificar que el lector lee el QR
4. Verificar que el sistema responde correctamente
5. Verificar que la barrera se abre (si está configurada)

## 8. Monitoreo Post-Despliegue

### 8.1 Logs a Revisar
```bash
# Logs del sistema
tail -f /ruta/dunas/logs/error.log

# Logs de PHP
tail -f /var/log/php8.3-fpm/error.log

# Logs de Apache/Nginx
tail -f /var/log/nginx/error.log
# o
tail -f /var/log/apache2/error.log
```

### 8.2 Métricas a Monitorear
- Número de vales generados por día
- Tasa de uso de vales
- Errores de validación
- Vales cancelados
- Tiempo de respuesta del endpoint de validación

## 9. Rollback (En caso de problemas)

### 9.1 Rollback de Base de Datos
```sql
-- Eliminar tabla de vales
DROP TABLE IF EXISTS vouchers;
```

### 9.2 Rollback de Código
```bash
# Revertir al commit anterior
git checkout [commit-hash-anterior]

# O eliminar archivos nuevos y restaurar modificados
git checkout HEAD -- app/views/layouts/main.php
git checkout HEAD -- public/index.php
git checkout HEAD -- app/controllers/AccessController.php
git checkout HEAD -- app/views/access/create.php
git checkout HEAD -- app/views/access/quick_registration.php

rm -rf app/views/vouchers
rm app/models/Voucher.php
rm app/controllers/VoucherController.php
rm config/update_vouchers_module.sql
```

## 10. Soporte Post-Despliegue

### Contactos
- **Desarrollador**: [Tu nombre/equipo]
- **Administrador de Sistema**: [Contacto]
- **Base de Datos**: [Contacto]

### Documentación
- Guía de usuario: `VOUCHER_MODULE_GUIDE.md`
- Resumen técnico: `VOUCHER_IMPLEMENTATION_SUMMARY.md`
- Este documento: `DEPLOYMENT_STEPS.md`

### Issues Comunes

**Problema**: No aparece "Vales" en el menú
- **Solución**: Verificar que el usuario tenga rol admin/supervisor/operator
- **Verificar**: `app/views/layouts/main.php` línea donde se añadió el menú

**Problema**: Error al generar vales
- **Solución**: Verificar que la tabla `vouchers` existe
- **Verificar**: Logs de PHP para mensajes de error

**Problema**: QR no se genera en impresión
- **Solución**: Verificar CDN de QRCode.js está accesible
- **URL**: `https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js`

**Problema**: Validación de vale no funciona
- **Solución**: Verificar que el endpoint responde
- **Test**: `curl -X POST https://tu-dominio.com/vouchers/validateQR -d "qr_code=TEST-000001-123456"`

## 11. Checklist Final

- [ ] Migración SQL ejecutada exitosamente
- [ ] Tabla `vouchers` creada con índices
- [ ] Archivos desplegados y con permisos correctos
- [ ] Módulo accesible desde menú
- [ ] Generación de vales funciona
- [ ] Impresión con QR funciona
- [ ] Validación en registro funciona
- [ ] Vale se marca como usado correctamente
- [ ] No reutilización funciona
- [ ] Cancelación funciona
- [ ] Estadísticas correctas
- [ ] Documentación disponible
- [ ] Equipo capacitado

---

**Fecha de Despliegue**: _______________
**Realizado por**: _______________
**Validado por**: _______________

**Estado**: 
- [ ] En Desarrollo
- [ ] En Testing
- [ ] Desplegado en Producción
- [ ] Validado
