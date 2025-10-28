# Guía Completa de Instalación - Sistema DUNAS

Esta guía proporciona instrucciones paso a paso para instalar y configurar el Sistema de Control de Acceso con IoT.

## 📋 Tabla de Contenidos

1. [Requisitos Previos](#requisitos-previos)
2. [Instalación del Servidor](#instalación-del-servidor)
3. [Instalación del Sistema](#instalación-del-sistema)
4. [Configuración de la Base de Datos](#configuración-de-la-base-de-datos)
5. [Configuración del Sistema](#configuración-del-sistema)
6. [Configuración de Shelly Relay](#configuración-de-shelly-relay)
7. [Verificación de la Instalación](#verificación-de-la-instalación)
8. [Primeros Pasos](#primeros-pasos)
9. [Solución de Problemas](#solución-de-problemas)

---

## 📋 Requisitos Previos

### Software Requerido

- **Servidor Web:** Apache 2.4 o superior
- **PHP:** 7.4 o superior
- **MySQL:** 5.7 o superior
- **Sistema Operativo:** Windows, Linux, o macOS

### Extensiones PHP Requeridas

```bash
# Verificar extensiones instaladas
php -m

# Extensiones necesarias:
- PDO
- PDO_MySQL
- curl
- gd
- mbstring
- json
- session
```

### Instalación de Extensiones PHP (si faltan)

**En Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install php-mysql php-curl php-gd php-mbstring
sudo systemctl restart apache2
```

**En Windows (XAMPP):**
- Editar `php.ini`
- Descomentar las líneas correspondientes:
  ```ini
  extension=pdo_mysql
  extension=curl
  extension=gd
  extension=mbstring
  ```
- Reiniciar Apache

---

## 🖥️ Instalación del Servidor

### Opción 1: XAMPP (Windows/Mac/Linux)

1. **Descargar XAMPP**
   - Ir a https://www.apachefriends.org/
   - Descargar la versión para su sistema operativo
   - Ejecutar el instalador

2. **Configurar XAMPP**
   - Iniciar el Panel de Control de XAMPP
   - Activar Apache y MySQL
   - Verificar que los servicios estén corriendo

### Opción 2: LAMP (Linux)

```bash
# Instalar Apache
sudo apt-get update
sudo apt-get install apache2

# Instalar MySQL
sudo apt-get install mysql-server

# Instalar PHP
sudo apt-get install php libapache2-mod-php php-mysql

# Habilitar mod_rewrite
sudo a2enmod rewrite

# Reiniciar Apache
sudo systemctl restart apache2
```

### Opción 3: WAMP (Windows)

1. Descargar WAMP desde http://www.wampserver.com/
2. Instalar siguiendo el asistente
3. Iniciar WAMP y verificar que esté en línea (icono verde)

---

## 📦 Instalación del Sistema

### Método 1: Clonar desde Git

```bash
# Navegar al directorio del servidor web
cd /var/www/html        # Linux
cd C:\xampp\htdocs      # Windows XAMPP

# Clonar el repositorio
git clone https://github.com/danjohn007/dunas.git
cd dunas
```

### Método 2: Descarga Manual

1. Descargar el repositorio como ZIP
2. Extraer el contenido
3. Copiar la carpeta al directorio del servidor web:
   - **Linux:** `/var/www/html/dunas`
   - **Windows XAMPP:** `C:\xampp\htdocs\dunas`
   - **macOS XAMPP:** `/Applications/XAMPP/htdocs/dunas`

### Configurar Permisos (Linux)

```bash
# Dar permisos de escritura a directorios necesarios
sudo chmod -R 755 /var/www/html/dunas/public/uploads
sudo chmod -R 755 /var/www/html/dunas/logs

# Cambiar propietario a usuario del servidor web
sudo chown -R www-data:www-data /var/www/html/dunas/public/uploads
sudo chown -R www-data:www-data /var/www/html/dunas/logs
```

---

## 💾 Configuración de la Base de Datos

### Paso 1: Crear la Base de Datos

**Usando phpMyAdmin:**

1. Abrir phpMyAdmin en el navegador:
   - `http://localhost/phpmyadmin`
2. Hacer clic en "Nueva" en el panel izquierdo
3. Nombre de la base de datos: `dunas_access_control`
4. Cotejamiento: `utf8mb4_unicode_ci`
5. Hacer clic en "Crear"

**Usando línea de comandos:**

```bash
# Conectar a MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE dunas_access_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Salir
EXIT;
```

### Paso 2: Importar el Esquema

**Usando phpMyAdmin:**

1. Seleccionar la base de datos `dunas_access_control`
2. Hacer clic en la pestaña "Importar"
3. Elegir archivo: `config/database.sql`
4. Hacer clic en "Continuar"

**Usando línea de comandos:**

```bash
# Desde el directorio del proyecto
mysql -u root -p dunas_access_control < config/database.sql
```

### Paso 3: Verificar las Tablas

```sql
-- Conectar a MySQL
mysql -u root -p dunas_access_control

-- Ver tablas creadas
SHOW TABLES;

-- Debería mostrar:
-- +----------------------------------+
-- | Tables_in_dunas_access_control   |
-- +----------------------------------+
-- | access_logs                      |
-- | clients                          |
-- | driver_unit_assignments          |
-- | drivers                          |
-- | maintenance_history              |
-- | transactions                     |
-- | units                            |
-- | users                            |
-- +----------------------------------+
```

---

## ⚙️ Configuración del Sistema

### Paso 1: Configurar Credenciales de Base de Datos

Editar el archivo `config/config.php`:

```php
// Configuración de base de datos
define('DB_HOST', 'localhost');           // Host de la BD
define('DB_NAME', 'dunas_access_control');// Nombre de la BD
define('DB_USER', 'root');                // Usuario de MySQL
define('DB_PASS', '');                    // Contraseña de MySQL (dejar vacío para XAMPP por defecto)
define('DB_CHARSET', 'utf8mb4');
```

### Paso 2: Configurar Apache mod_rewrite

**Para XAMPP:**

1. Abrir `httpd.conf`:
   - Windows: `C:\xampp\apache\conf\httpd.conf`
   - Linux: `/opt/lampp/etc/httpd.conf`

2. Buscar y descomentar (quitar el #):
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```

3. Buscar `AllowOverride None` y cambiar a:
   ```apache
   AllowOverride All
   ```

4. Reiniciar Apache

**Para Linux (Apache2):**

```bash
# Habilitar mod_rewrite
sudo a2enmod rewrite

# Editar configuración del sitio
sudo nano /etc/apache2/sites-available/000-default.conf

# Agregar dentro de <VirtualHost>:
<Directory /var/www/html>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

# Reiniciar Apache
sudo systemctl restart apache2
```

### Paso 3: Verificar Configuración PHP

Editar `php.ini`:

```ini
; Tamaño máximo de archivos
upload_max_filesize = 10M
post_max_size = 10M

; Tiempo de ejecución
max_execution_time = 300
max_input_time = 300

; Zona horaria
date.timezone = America/Mexico_City
```

Reiniciar el servidor web después de los cambios.

---

## 🔌 Configuración de Shelly Relay

### Paso 1: Configurar Dispositivo Shelly

1. **Conectar el Shelly Relay a la red:**
   - Conectar el dispositivo a la alimentación
   - Usar la app móvil Shelly o el portal web
   - Configurar WiFi y obtener la IP del dispositivo

2. **Probar conexión:**
   ```bash
   # Verificar que el dispositivo responde
   curl http://IP_DEL_SHELLY/status
   ```

### Paso 2: Configurar en el Sistema

Editar `config/config.php`:

```php
// Configuración de Shelly Relay API
define('SHELLY_API_URL', 'http://192.168.1.100'); // Cambiar a la IP real
define('SHELLY_API_TIMEOUT', 5);
define('SHELLY_RELAY_OPEN', 0);  // Canal para abrir barrera
define('SHELLY_RELAY_CLOSE', 1); // Canal para cerrar barrera
```

### Paso 3: Probar Integración

Acceder a:
```
http://localhost/dunas/access
```

Usar los controles manuales de barrera para probar la conexión.

---

## ✅ Verificación de la Instalación

### Test de Conexión

1. Abrir en el navegador:
   ```
   http://localhost/dunas/test-connection.php
   ```

2. Verificar que todo aparezca en verde:
   - ✅ URL Base detectada correctamente
   - ✅ Conexión a base de datos exitosa
   - ✅ Todas las tablas presentes
   - ✅ Directorios con permisos correctos

### Resolver Errores Comunes

**Error: No se puede conectar a la base de datos**
- Verificar credenciales en `config/config.php`
- Verificar que MySQL esté corriendo
- Verificar que la base de datos exista

**Error: 404 en todas las páginas**
- Verificar que mod_rewrite esté habilitado
- Verificar que el archivo `.htaccess` exista en `public/`
- Verificar AllowOverride All en configuración Apache

**Error: No se pueden subir archivos**
- Verificar permisos de `public/uploads/`
- Verificar configuración de `upload_max_filesize` en php.ini

---

## 🚀 Primeros Pasos

### 1. Acceder al Sistema

Abrir en el navegador:
```
http://localhost/dunas/
```

### 2. Iniciar Sesión

Usar las credenciales de prueba:

| Usuario    | Contraseña | Rol           |
|-----------|------------|---------------|
| admin     | admin123   | Administrador |
| supervisor| admin123   | Supervisor    |
| operator  | admin123   | Operador      |
| cliente1  | admin123   | Cliente       |

### 3. Explorar el Dashboard

Después de iniciar sesión, verá:
- Estadísticas en tiempo real
- Accesos recientes
- Gráficas de ingresos
- Accesos rápidos a módulos

### 4. Cambiar Contraseñas (IMPORTANTE)

**Para producción, es CRÍTICO cambiar las contraseñas:**

1. Ir a Usuarios (solo Admin)
2. Seleccionar cada usuario
3. Cambiar la contraseña
4. Guardar cambios

### 5. Configurar Usuarios Reales

1. **Crear usuarios del sistema:**
   - Ir a Usuarios > Nuevo Usuario
   - Llenar formulario
   - Asignar rol apropiado

2. **Registrar clientes:**
   - Ir a Clientes > Nuevo Cliente
   - Completar información
   - Asociar con usuario si es necesario

3. **Registrar unidades (pipas):**
   - Ir a Unidades > Nueva Unidad
   - Subir foto de la unidad
   - Completar especificaciones

4. **Registrar choferes:**
   - Ir a Choferes > Nuevo Chofer
   - Subir foto y licencia
   - Asignar unidad

---

## 🔧 Solución de Problemas

### Problema: Sesión expira muy rápido

**Solución:**
Editar `config/config.php`:
```php
define('SESSION_TIMEOUT', 7200); // 2 horas (en segundos)
```

### Problema: Error al subir fotos

**Solución:**
```bash
# Linux
sudo chmod -R 775 public/uploads
sudo chown -R www-data:www-data public/uploads

# Windows: Dar permisos completos a la carpeta en Propiedades > Seguridad
```

### Problema: URL incorrecta / estilos no cargan

**Solución:**
1. Verificar que `BASE_URL` se detecte correctamente en el archivo de prueba `test-connection.php`
2. El archivo `.htaccess` ya está configurado para funcionar automáticamente en cualquier subdirectorio sin necesidad de ajustes adicionales
3. Si los estilos no cargan, verificar que las rutas de los recursos sean relativas a `BASE_URL`

### Problema: Error 500 en páginas

**Solución:**
1. Activar display_errors en `config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```
2. Revisar los logs de error de Apache
3. Verificar permisos de archivos

### Problema: No funciona Shelly Relay

**Solución:**
1. Verificar que el dispositivo esté en la misma red
2. Hacer ping a la IP del dispositivo
3. Probar endpoint directamente:
```bash
curl http://IP_SHELLY/relay/0?turn=on
```
4. Verificar firewall no esté bloqueando

---

## 📞 Soporte Adicional

### Recursos

- **Documentación:** Ver README.md
- **Repositorio:** https://github.com/danjohn007/dunas
- **Issues:** Reportar problemas en GitHub

### Logs del Sistema

Los logs se guardan en:
```
logs/
├── error.log      # Errores del sistema
├── access.log     # Registros de acceso
└── api.log        # Llamadas a APIs externas
```

### Información del Sistema

Crear archivo `info.php` en `public/`:
```php
<?php phpinfo(); ?>
```

Acceder a `http://localhost/dunas/info.php` para ver configuración completa de PHP.

**⚠️ IMPORTANTE: Eliminar este archivo después de revisar.**

---

## ✅ Checklist de Instalación Completa

- [ ] Servidor web Apache instalado y corriendo
- [ ] PHP 7.4+ instalado con extensiones requeridas
- [ ] MySQL instalado y corriendo
- [ ] Sistema descargado/clonado
- [ ] Base de datos creada e importada
- [ ] Credenciales configuradas en config.php
- [ ] Permisos de directorios configurados
- [ ] mod_rewrite habilitado
- [ ] Test de conexión exitoso
- [ ] Login funcional
- [ ] Dashboard accesible
- [ ] Contraseñas de prueba cambiadas
- [ ] Shelly Relay configurado (opcional)

---

## 🎉 ¡Sistema Listo!

Si completó todos los pasos, el sistema está listo para usar.

**Próximos pasos recomendados:**

1. Configurar backup automático de la base de datos
2. Configurar SSL/HTTPS para producción
3. Revisar y ajustar configuraciones de seguridad
4. Capacitar usuarios en el uso del sistema
5. Configurar monitoreo y alertas

---

**Versión:** 1.0.0  
**Última actualización:** Octubre 2024  
**Soporte:** Sistema DUNAS - Control de Acceso con IoT
