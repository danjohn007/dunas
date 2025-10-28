# Guía de Solución de Problemas - Sistema DUNAS

Esta guía proporciona soluciones para los problemas más comunes al instalar y usar el sistema.

## 📋 Tabla de Contenidos

1. [Error 404 - Página No Encontrada](#error-404---página-no-encontrada)
2. [Error de Conexión a Base de Datos](#error-de-conexión-a-base-de-datos)
3. [Directorios de Uploads No Existen](#directorios-de-uploads-no-existen)
4. [Error al Subir Archivos](#error-al-subir-archivos)
5. [Sesión Expira Constantemente](#sesión-expira-constantemente)

---

## Error 404 - Página No Encontrada

### Síntomas
- Al intentar acceder a `/login` aparece "404 - Page Not Found"
- Todas las páginas excepto `index.php` muestran error 404
- Solo funciona `http://localhost/dunas/index.php` pero no `http://localhost/dunas/login`

### Causa Raíz
El error 404 ocurre cuando Apache no puede procesar las reglas de reescritura de URL del archivo `.htaccess`. Esto puede deberse a:
1. El módulo `mod_rewrite` no está habilitado
2. Apache no tiene permiso para leer `.htaccess` (AllowOverride None)
3. El archivo `.htaccess` no existe en la carpeta `public/`

### Solución

#### Para XAMPP (Windows)

1. **Verificar que mod_rewrite esté habilitado:**
   - Abrir: `C:\xampp\apache\conf\httpd.conf` (o la ruta de instalación de XAMPP)
   - Buscar la línea: `#LoadModule rewrite_module modules/mod_rewrite.so`
   - Quitar el `#` al inicio para descomentarla
   - Guardar el archivo

2. **Habilitar AllowOverride:**
   - En el mismo archivo `httpd.conf`, buscar:
   ```apache
   <Directory "C:/xampp/htdocs">  <!-- Ajustar según su ruta de instalación -->
       ...
       AllowOverride None
       ...
   </Directory>
   ```
   - Cambiar `AllowOverride None` por `AllowOverride All`
   - Guardar el archivo

3. **Reiniciar Apache:**
   - Abrir el Panel de Control de XAMPP
   - Detener Apache
   - Iniciar Apache nuevamente

4. **Verificar que existe .htaccess:**
   - Ir a la carpeta `public/` del proyecto
   - Verificar que existe el archivo `.htaccess`
   - Si no existe, crearlo con el siguiente contenido:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /
       
       # Si el archivo o directorio existe, servir directamente
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       
       # Redireccionar todo a index.php
       RewriteRule ^(.*)$ index.php [QSA,L]
   </IfModule>
   ```

#### Para Linux (Ubuntu/Debian)

1. **Habilitar mod_rewrite:**
   ```bash
   sudo a2enmod rewrite
   ```

2. **Configurar AllowOverride:**
   ```bash
   sudo nano /etc/apache2/sites-available/000-default.conf
   ```
   
   Agregar dentro de `<VirtualHost>` (ajustar la ruta según su directorio web):
   ```apache
   <Directory /var/www/html>  <!-- Cambiar según su directorio raíz web -->
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

3. **Reiniciar Apache:**
   ```bash
   sudo systemctl restart apache2
   ```

#### Para macOS (MAMP)

1. **Verificar mod_rewrite:**
   - Abrir: `/Applications/MAMP/conf/apache/httpd.conf`
   - Buscar: `LoadModule rewrite_module modules/mod_rewrite.so`
   - Asegurarse que no esté comentada (sin `#` al inicio)

2. **Configurar AllowOverride:**
   - En el mismo archivo, buscar la sección `<Directory>` correspondiente
   - Cambiar `AllowOverride None` a `AllowOverride All`

3. **Reiniciar servidores desde MAMP**

### Verificación

Después de aplicar la solución:

1. Abrir el navegador e ir a: `http://localhost/dunas/login`
2. Debe mostrar la página de inicio de sesión, NO un error 404
3. Probar otras rutas: `http://localhost/dunas/dashboard`

### Si el problema persiste

1. **Verificar logs de Apache:**
   - XAMPP Windows: `C:\xampp\apache\logs\error.log`
   - Linux: `/var/log/apache2/error.log`
   - Buscar mensajes sobre `.htaccess` o `mod_rewrite`

2. **Probar con el servidor PHP integrado (solo para pruebas):**
   ```bash
   cd /path/to/dunas  # Reemplazar con la ruta real de su proyecto
   php -S localhost:8000 -t public/
   ```
   Luego acceder a: `http://localhost:8000/login`

---

## Error de Conexión a Base de Datos

### Síntomas
- "Error de conexión a la base de datos"
- "SQLSTATE[HY000] [2002] No such file or directory"
- "Access denied for user"

### Solución

1. **Verificar que MySQL esté corriendo:**
   - XAMPP: Iniciar MySQL desde el Panel de Control
   - Linux: `sudo systemctl status mysql`

2. **Verificar credenciales en `config/config.php`:**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'dunas_access_control');
   define('DB_USER', 'root');        // Usuario correcto
   define('DB_PASS', '');            // Contraseña correcta
   ```

3. **Verificar que la base de datos existe:**
   - Abrir phpMyAdmin: `http://localhost/phpmyadmin`
   - Buscar la base de datos `dunas_access_control`
   - Si no existe, importar `config/database.sql`

4. **Crear la base de datos manualmente:**
   ```bash
   mysql -u root -p
   CREATE DATABASE dunas_access_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE dunas_access_control;
   SOURCE /path/to/dunas/config/database.sql;  # Reemplazar con la ruta real
   EXIT;
   ```

---

## Directorios de Uploads No Existen

### Síntomas
En la página de test de conexión (`test-connection.php`):
- ❌ Uploads/Units: No existe
- ❌ Uploads/Drivers: No existe

### Solución

Los directorios necesarios ya están incluidos en el repositorio desde la versión actual. Si los subdirectorios no existen:

1. **Crear los directorios manualmente:**
   ```bash
   mkdir -p public/uploads/units
   mkdir -p public/uploads/drivers
   ```

2. **En Windows:**
   - Ir a la carpeta `public/uploads/`
   - Crear carpeta `units`
   - Crear carpeta `drivers`

3. **Configurar permisos (Linux/macOS):**
   ```bash
   chmod -R 755 public/uploads
   sudo chown -R www-data:www-data public/uploads
   ```

4. **Verificar:**
   - Acceder a: `http://localhost/dunas/test-connection.php`
   - Debe mostrar:
     - ✅ Uploads: Existe y es escribible
     - ✅ Uploads/Units: Existe y es escribible
     - ✅ Uploads/Drivers: Existe y es escribible
     - ✅ Logs: Existe y es escribible

---

## Error al Subir Archivos

### Síntomas
- "No se puede subir el archivo"
- "Error al guardar la imagen"
- Archivos no se guardan en `uploads/units` o `uploads/drivers`

### Solución

1. **Verificar permisos de escritura:**
   
   **Linux/macOS:**
   ```bash
   sudo chmod -R 755 public/uploads
   sudo chown -R www-data:www-data public/uploads
   ```
   
   **Windows:**
   - Click derecho en la carpeta `uploads`
   - Propiedades → Seguridad
   - Asegurarse que "Usuarios" tenga permisos de escritura

2. **Verificar configuración PHP:**
   
   Editar `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   ```
   
   **Ubicación php.ini:**
   - XAMPP Windows: `C:\xampp\php\php.ini`
   - XAMPP Linux: `/opt/lampp/etc/php.ini`
   - Linux: `/etc/php/X.X/apache2/php.ini` (reemplazar X.X con su versión de PHP, ej: 7.4, 8.0, 8.1)

3. **Reiniciar Apache después de cambios**

4. **Verificar que los directorios existen:**
   ```bash
   ls -la public/uploads/
   ```
   Debe mostrar: `units/` y `drivers/`

---

## Sesión Expira Constantemente

### Síntomas
- "Su sesión ha expirado"
- Se cierra la sesión cada pocos minutos

### Solución

1. **Aumentar timeout en `config/config.php`:**
   ```php
   define('SESSION_TIMEOUT', 7200); // 2 horas (en segundos)
   ```

2. **Ajustar configuración de PHP:**
   
   Editar `php.ini`:
   ```ini
   session.gc_maxlifetime = 7200
   session.cookie_lifetime = 7200
   ```

3. **Reiniciar Apache**

---

## Verificación General del Sistema

Para verificar que todo está correctamente configurado, acceder a:

```
http://localhost/dunas/test-connection.php
```

Esta página verifica:
- ✅ URL Base detectada correctamente
- ✅ Conexión a base de datos
- ✅ Tablas de la base de datos
- ✅ Permisos de directorios
- ✅ Configuración PHP

Todo debe aparecer en verde (✅) para que el sistema funcione correctamente.

---

## Contacto y Soporte

Si después de seguir esta guía el problema persiste:

1. Revisar los logs de Apache para mensajes de error específicos
2. Verificar la configuración de PHP con: `<?php phpinfo(); ?>`
3. Abrir un issue en GitHub con:
   - Descripción del problema
   - Sistema operativo
   - Versión de PHP
   - Versión de Apache/MySQL
   - Mensajes de error completos

---

**Última actualización:** Octubre 2025  
**Versión del documento:** 1.0
