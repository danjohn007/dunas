# DEPLOYMENT CHECKLIST - DUNAS v1.1.0

## Pre-Deployment

- [ ] Backup de base de datos actual
- [ ] Backup de archivos del sistema actual
- [ ] Verificar versión de PHP (7.4+)
- [ ] Verificar acceso a base de datos

## Deployment Steps

### 1. Base de Datos
- [ ] Ejecutar script `config/update_1.1.0.sql`
- [ ] Verificar que la tabla `settings` fue creada
- [ ] Verificar configuraciones por defecto insertadas

```sql
-- Verificar tabla settings
SELECT COUNT(*) FROM settings;
-- Debe retornar al menos 12 registros
```

### 2. Archivos del Sistema
- [ ] Subir/actualizar todos los archivos modificados
- [ ] Verificar permisos de directorios:
  ```bash
  chmod 755 public/uploads
  chmod 755 public/uploads/logos
  ```

### 3. Verificación de Archivos Críticos

**Nuevos Controladores:**
- [ ] `app/controllers/ProfileController.php`
- [ ] `app/controllers/SettingsController.php`

**Nuevos Modelos:**
- [ ] `app/models/Settings.php`

**Nuevas Vistas:**
- [ ] `app/views/reports/access.php`
- [ ] `app/views/reports/operational.php`
- [ ] `app/views/profile/index.php`
- [ ] `app/views/settings/index.php`

**Archivos Actualizados:**
- [ ] `app/views/layouts/main.php`
- [ ] `app/views/transactions/create.php`
- [ ] `app/views/access/create.php`
- [ ] `app/views/reports/financial.php`
- [ ] `app/controllers/ProfileController.php`
- [ ] `public/index.php`

## Post-Deployment Testing

### 1. Funcionalidad Básica
- [ ] Login funciona correctamente
- [ ] Dashboard se carga sin errores
- [ ] Menú de navegación funciona

### 2. Nuevas Funcionalidades

**Reportes:**
- [ ] Acceder a `/reports`
- [ ] Click en "Reporte de Accesos" - debe cargar sin errores
- [ ] Click en "Reporte Operativo" - debe cargar sin errores
- [ ] Click en "Reporte Financiero" - gráficas deben mostrarse
- [ ] Click en "Ingresos del Mes" - debe cargar correctamente
- [ ] Click en "Ingresos Anuales" - debe cargar correctamente

**Transacciones:**
- [ ] Acceder a `/transactions`
- [ ] Click en "Nueva Transacción"
- [ ] Formulario se muestra completo
- [ ] Crear una transacción de prueba

**Accesos:**
- [ ] Acceder a `/access`
- [ ] Click en "Nuevo Acceso" desde dashboard
- [ ] Formulario se muestra completo
- [ ] Los selectores muestran datos (clientes, unidades, choferes)

**Perfil:**
- [ ] Click en nombre de usuario (esquina superior derecha)
- [ ] Verificar que aparece dropdown
- [ ] Click en "Perfil"
- [ ] Actualizar nombre y email
- [ ] Cambiar contraseña (opcional)

**Configuraciones (Solo Admin):**
- [ ] Click en nombre de usuario
- [ ] Click en "Configuraciones"
- [ ] Verificar que todos los campos se muestran
- [ ] Guardar configuraciones de prueba
- [ ] Verificar que se guardaron correctamente

### 3. Gráficas
- [ ] Dashboard: Gráfica "Ingresos Mensuales" se muestra
- [ ] Reporte Financiero: Gráfica "Ingresos por Día" se muestra
- [ ] No hay errores en consola del navegador (F12)

### 4. Navegación
- [ ] Todos los enlaces del menú funcionan
- [ ] Dropdown de usuario funciona
- [ ] Click fuera del dropdown lo cierra

## Rollback Plan (Si hay problemas)

1. **Restaurar Base de Datos:**
   ```bash
   mysql -u [usuario] -p [base_datos] < backup_[fecha].sql
   ```

2. **Restaurar Archivos:**
   ```bash
   # Restaurar desde backup de archivos
   cp -r backup_[fecha]/* /ruta/sistema/
   ```

## Issues Conocidos y Soluciones

### Problema: Gráficas no se muestran
**Solución:**
1. Verificar consola del navegador (F12)
2. Asegurarse que Chart.js se carga
3. Verificar que hay datos en el período seleccionado

### Problema: Error "settings table doesn't exist"
**Solución:**
1. Ejecutar nuevamente `config/update_1.1.0.sql`
2. Verificar que el usuario de BD tiene permisos CREATE TABLE

### Problema: No se pueden subir logos
**Solución:**
1. Crear directorio: `mkdir -p public/uploads/logos`
2. Dar permisos: `chmod 755 public/uploads/logos`

### Problema: Dropdown de usuario no funciona
**Solución:**
1. Limpiar caché del navegador
2. Verificar que `app/views/layouts/main.php` está actualizado
3. Verificar consola de JavaScript por errores

## Notas de Producción

- **Caché:** Limpiar caché de navegador después del deployment
- **Sesiones:** Los usuarios activos pueden necesitar hacer logout/login
- **Logs:** Monitorear logs de PHP y Apache/Nginx por las primeras 24 horas
- **Backup:** Mantener backup por al menos 7 días

## Contacto de Emergencia

En caso de problemas críticos:
1. Restaurar desde backup inmediatamente
2. Documentar el error (screenshots, logs)
3. Contactar soporte técnico

## Sign-off

- [ ] Deployment completado por: ________________
- [ ] Fecha y hora: ________________
- [ ] Testing completado por: ________________
- [ ] Aprobación final: ________________

---

**Versión:** 1.1.0  
**Fecha:** 2024-10-28  
**Deployment ID:** ______________
