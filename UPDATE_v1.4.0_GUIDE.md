# Guía de Actualización a v1.4.0

## Resumen Rápido

Esta guía proporciona instrucciones paso a paso para actualizar de v1.3.0 a v1.4.0 del Sistema de Control de Acceso Dunas.

## Tiempo Estimado
- Pequeño: 15-20 minutos
- Mediano: 20-30 minutos  
- Grande: 30-45 minutos

## Pre-requisitos

### Verificar Versión Actual
```sql
SELECT setting_value FROM settings WHERE setting_key = 'system_version';
```

Debe ser v1.3.0 o superior.

### Herramientas Necesarias
- Acceso SSH o panel de control del servidor
- phpMyAdmin o cliente MySQL
- Editor de texto (para configuración si es necesario)
- Backup completo reciente

## Paso 1: Backup Completo

### 1.1 Backup de Base de Datos
```bash
# Backup de la base de datos
mysqldump -u usuario -p dunas_access_control > backup_dunas_$(date +%Y%m%d_%H%M%S).sql

# Verificar el backup
ls -lh backup_dunas_*.sql
```

### 1.2 Backup de Archivos
```bash
# Backup del código fuente
cd /var/www/html
tar -czf dunas_backup_$(date +%Y%m%d_%H%M%S).tar.gz dunas/

# Verificar el backup
ls -lh dunas_backup_*.tar.gz
```

## Paso 2: Actualizar Código Fuente

### Opción A: Usando Git (Recomendado)
```bash
cd /var/www/html/dunas

# Verificar estado actual
git status

# Guardar cambios locales si los hay
git stash

# Obtener actualizaciones
git fetch origin

# Cambiar a la rama de la versión
git checkout v1.4.0

# O actualizar desde la rama principal
git pull origin main
```

### Opción B: Descarga Manual
```bash
# Descargar archivo ZIP
wget https://github.com/danjohn007/dunas/archive/v1.4.0.zip

# Extraer
unzip v1.4.0.zip

# Mover archivos (preservando configuración)
cp -r dunas-1.4.0/* /var/www/html/dunas/
```

## Paso 3: Ejecutar Migración de Base de Datos

### 3.1 Verificar Script de Migración
```bash
cd /var/www/html/dunas
cat config/update_hikvision_shelly_channels.sql
```

### 3.2 Ejecutar Migración
```bash
# Método 1: Línea de comandos
mysql -u usuario -p dunas_access_control < config/update_hikvision_shelly_channels.sql

# Método 2: phpMyAdmin
# 1. Abrir phpMyAdmin
# 2. Seleccionar base de datos dunas_access_control
# 3. Ir a pestaña "SQL"
# 4. Pegar contenido de update_hikvision_shelly_channels.sql
# 5. Ejecutar
```

### 3.3 Verificar Migración
```sql
-- Verificar nueva tabla
SHOW TABLES LIKE 'hikvision_devices';

-- Verificar nuevos campos en shelly_devices
DESCRIBE shelly_devices;

-- Debe mostrar: entry_channel, exit_channel, pulse_duration_ms

-- Verificar datos migrados
SELECT id, name, entry_channel, exit_channel, pulse_duration_ms 
FROM shelly_devices;
```

**Resultado Esperado:**
- Tabla `hikvision_devices` existe
- Campos nuevos presentes en `shelly_devices`
- Dispositivos Shelly tienen valores en nuevos campos

## Paso 4: Verificar Permisos de Archivos

```bash
# Asegurar permisos correctos
cd /var/www/html/dunas
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 uploads/
chmod -R 775 logs/
```

## Paso 5: Limpiar Caché

### 5.1 Caché de PHP (si aplica)
```bash
# OPcache
service php7.4-fpm reload

# O reiniciar PHP-FPM
service php7.4-fpm restart
```

### 5.2 Caché del Navegador
- Ctrl + F5 en Windows/Linux
- Cmd + Shift + R en Mac
- O limpiar caché del navegador manualmente

## Paso 6: Configuración Post-Actualización

### 6.1 Acceder al Panel de Administración
1. Abrir navegador
2. Ir a: `http://su-dominio.com/settings`
3. Iniciar sesión como administrador

### 6.2 Verificar Secciones Nuevas
✅ Debe ver:
- Sección "Dispositivos HikVision"
- Campos actualizados en "Dispositivos Shelly Cloud"
  - Canal de Entrada
  - Canal de Salida
  - Duración Pulso

### 6.3 Revisar Dispositivos Shelly Existentes
1. Ir a **Configuraciones del Sistema** → **Dispositivos Shelly Cloud**
2. Verificar que cada dispositivo tiene:
   - Entry Channel configurado
   - Exit Channel configurado
   - Pulse Duration configurado (default: 5000)
3. Ajustar si es necesario
4. Guardar cambios

## Paso 7: Configurar Dispositivos HikVision (Opcional)

### 7.1 Si Tiene Cámaras LPR HikVision
1. Ir a **Configuraciones del Sistema** → **Dispositivos HikVision**
2. Clic en **"Nuevo dispositivo +"**
3. Configurar:
   - Nombre: "Cámara Entrada Principal"
   - Tipo: Cámara LPR (Lectura de Placas)
   - URL: `http://IP_DE_LA_CAMARA`
   - Usuario: Su usuario de la cámara
   - Contraseña: Su contraseña de la cámara
   - Área: "Entrada Principal"
   - Marcar "Dispositivo habilitado"
4. Guardar

### 7.2 Si Tiene Lectores de Código de Barras
1. Seguir pasos similares pero seleccionar:
   - Tipo: Lector de Código de Barras
2. Configurar URL, credenciales y área
3. Guardar

### 7.3 Probar Conexión
Para cada dispositivo HikVision agregado:
```bash
# Probar conectividad básica
curl -v http://IP_DEL_DISPOSITIVO/ISAPI/System/deviceInfo
```

## Paso 8: Pruebas Funcionales

### 8.1 Probar Auto-llenado de Litros
1. Crear un registro de entrada
2. Ir a registrar salida
3. ✅ Verificar que el campo "Litros Suministrados" ya tiene valor
4. Completar y guardar

### 8.2 Probar Canales Shelly
1. Registrar una entrada
2. Verificar en logs que se activa el canal correcto
3. Registrar una salida  
4. Verificar que se activa el otro canal

### 8.3 Probar Lectura de Placas (Si aplica)
1. Registrar entrada de vehículo
2. Verificar que se intenta leer placa
3. Verificar registro en base de datos:
```sql
SELECT ticket_code, license_plate_reading, plate_discrepancy 
FROM access_logs 
ORDER BY id DESC 
LIMIT 5;
```

## Paso 9: Monitoreo Post-Actualización

### 9.1 Verificar Logs de Errores
```bash
# PHP Error Log
tail -f /var/log/apache2/error.log
# O
tail -f /var/log/nginx/error.log

# Application Log
tail -f /var/www/html/dunas/logs/app.log
```

### 9.2 Métricas a Monitorear
- Registros de entrada/salida funcionan correctamente
- No hay errores 500 en el servidor
- Tiempos de respuesta son normales
- Dispositivos Shelly responden adecuadamente

## Paso 10: Documentación

### 10.1 Actualizar Documentación Interna
- Registrar fecha de actualización
- Documentar dispositivos HikVision agregados
- Documentar configuración de canales Shelly

### 10.2 Capacitar al Personal
- Mostrar auto-llenado de litros
- Explicar funcionamiento de barcode reader (si aplica)
- Revisar nuevas opciones en configuración

## Troubleshooting (Solución de Problemas)

### Problema: Tabla hikvision_devices no existe
**Solución:**
```sql
-- Verificar que el script se ejecutó
SHOW TABLES LIKE 'hikvision_devices';

-- Si no existe, ejecutar nuevamente
SOURCE config/update_hikvision_shelly_channels.sql;
```

### Problema: Campos de canal no aparecen en Shelly
**Solución:**
1. Limpiar caché del navegador (Ctrl + F5)
2. Verificar que los archivos se actualizaron:
```bash
grep -n "Canal de Entrada" app/views/settings/index.php
```

### Problema: Error de permisos en archivos
**Solución:**
```bash
cd /var/www/html/dunas
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 uploads/ logs/
```

### Problema: Dispositivos Shelly no responden
**Solución:**
1. Verificar conectividad de red
2. Verificar credenciales en configuración
3. Probar manualmente desde API:
```bash
curl -X POST "https://shelly-cloud/device/relay/control" \
  -d "auth_key=TOKEN&id=DEVICE_ID&channel=0&turn=on"
```

### Problema: Lectura de placas no funciona
**Solución:**
1. Verificar URL de dispositivo HikVision
2. Verificar credenciales
3. Probar endpoint manualmente:
```bash
curl -u usuario:password http://IP_CAMARA/ISAPI/System/deviceInfo
```

## Rollback (Reversión)

Si encuentra problemas críticos y necesita revertir:

### 1. Restaurar Base de Datos
```bash
mysql -u usuario -p dunas_access_control < backup_dunas_YYYYMMDD_HHMMSS.sql
```

### 2. Restaurar Código
```bash
cd /var/www/html
rm -rf dunas/
tar -xzf dunas_backup_YYYYMMDD_HHMMSS.tar.gz
```

### 3. Limpiar Caché
```bash
service php7.4-fpm restart
```

## Checklist de Actualización

Marque cada paso completado:

### Pre-Actualización
- [ ] Backup de base de datos creado
- [ ] Backup de archivos creado
- [ ] Versión actual verificada (v1.3.0)
- [ ] Downtime programado (si es necesario)

### Actualización
- [ ] Código fuente actualizado
- [ ] Script SQL ejecutado exitosamente
- [ ] Nuevas tablas/campos verificados
- [ ] Permisos de archivos configurados
- [ ] Caché limpiado

### Post-Actualización
- [ ] Panel de administración accesible
- [ ] Secciones nuevas visibles
- [ ] Dispositivos Shelly revisados
- [ ] Dispositivos HikVision configurados (si aplica)
- [ ] Auto-llenado de litros funcional
- [ ] Canales Shelly funcionan correctamente
- [ ] Logs sin errores críticos

### Documentación
- [ ] Fecha de actualización registrada
- [ ] Personal capacitado
- [ ] Cambios documentados

## Contacto de Soporte

Si necesita ayuda durante la actualización:

**Email:** soporte@dunas.com  
**GitHub Issues:** https://github.com/danjohn007/dunas/issues  
**Documentación:** https://github.com/danjohn007/dunas/wiki

## Recursos Adicionales

- [IMPLEMENTATION_v1.4.0.md](IMPLEMENTATION_v1.4.0.md) - Detalles técnicos completos
- [TESTING_v1.4.0.md](TESTING_v1.4.0.md) - Procedimientos de prueba
- [RELEASE_NOTES_v1.4.0.md](RELEASE_NOTES_v1.4.0.md) - Notas de versión completas
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Guía general de solución de problemas

---

**Última actualización:** Noviembre 6, 2025  
**Versión:** 1.4.0
