# Sistema de Control de Acceso con IoT - DUNAS

Sistema integral para gestionar el acceso de pipas de agua a tomas autorizadas, controlando clientes, unidades, conductores, transacciones y generando informes detallados con integración IoT (Shelly Relay SHELLPRO4PM).

## 🚀 Características Principales

- ✅ **Gestión de Usuarios** con 5 niveles de acceso (Admin, Supervisor, Operador, Visualizador, Cliente)
- ✅ **Módulo de Clientes** con tipos (Residencial/Comercial/Industrial)
- ✅ **Gestión de Unidades (Pipas)** con historial de mantenimiento
- ✅ **Gestión de Choferes** con control de licencias
- ✅ **Control de Acceso** con tickets QR y códigos de barras
- ✅ **Registro Rápido** con búsqueda de unidad y creación automática de registros
- ✅ **Escaneo de Código de Barras** para salida automática con capacidad máxima
- ✅ **Integración IoT** con Shelly Relay SHELLPRO4PM para control de barreras vehiculares
- ✅ **Sincronización FTP** para imágenes de cámaras ANPR con detección automática
- ✅ **Transacciones y Pagos** (Efectivo, Vales, Transferencias)
- ✅ **Reportes** con exportación a Excel y PDF
- ✅ **Dashboard** con estadísticas en tiempo real y gráficas optimizadas
- ✅ **Diseño Responsivo** con Tailwind CSS

## 📋 Requisitos del Sistema

- **Servidor Web:** Apache 2.4+
- **PHP:** 7.4 o superior
- **MySQL:** 5.7 o superior
- **Extensiones PHP requeridas:**
  - PDO
  - PDO_MySQL
  - curl
  - gd (para manejo de imágenes)
  - mbstring
  - ftp (para sincronización de imágenes ANPR)

## 🔧 Instalación

### 1. Clonar o descargar el repositorio

```bash
git clone https://github.com/danjohn007/dunas.git
cd dunas
```

### 2. Configurar el servidor Apache

#### Opción A: Instalación en directorio raíz

Copie el contenido de la carpeta `public` a su directorio raíz de Apache (ej: `/var/www/html` o `htdocs`).

```bash
# Linux
sudo cp -r public/* /var/www/html/
sudo cp -r app /var/www/
sudo cp -r config /var/www/

# Windows (XAMPP)
# Copiar archivos a C:\xampp\htdocs\
```

#### Opción B: Instalación en subdirectorio

Si desea instalar en un subdirectorio (ej: `http://localhost/dunas`):

1. Copie toda la carpeta del proyecto al directorio deseado
2. El archivo `.htaccess` en la carpeta `public` ya está configurado y funciona automáticamente en cualquier ruta (raíz o subdirectorio)

3. El sistema detectará automáticamente la URL base

### 3. Configurar la base de datos

#### Crear la base de datos

Importe el archivo SQL ubicado en `config/database.sql`:

```bash
mysql -u root -p < config/database.sql
```

O desde phpMyAdmin:
1. Crear una nueva base de datos llamada `dunas_access_control`
2. Importar el archivo `config/database.sql`

#### Configurar credenciales

Edite el archivo `config/config.php` con sus credenciales de base de datos:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dunas_access_control');
define('DB_USER', 'root');           // Cambiar según su configuración
define('DB_PASS', '');               // Cambiar según su configuración
```

### 4. Configurar permisos de directorios

Los siguientes directorios deben tener permisos de escritura:

```bash
# Linux
sudo chmod -R 755 public/uploads
sudo chmod -R 755 logs
sudo chown -R www-data:www-data public/uploads
sudo chown -R www-data:www-data logs

# Windows: Asegurar que el usuario del servidor web tenga permisos de escritura
```

### 5. Configurar Shelly Cloud API (Opcional)

Si desea utilizar la integración con Shelly Cloud API para control de barreras:

Edite el archivo `config/config.php`:

```php
define('SHELLY_AUTH_TOKEN', 'YOUR_AUTH_TOKEN');    // Token del Shelly Cloud API
define('SHELLY_DEVICE_ID', 'YOUR_DEVICE_ID');      // ID del dispositivo Shelly
define('SHELLY_SERVER', 'shelly-XXX-eu.shelly.cloud'); // Servidor Cloud
define('SHELLY_SWITCH_ID', 0);                     // Canal del switch
```

**Para obtener las credenciales:**
1. Abra la aplicación Shelly Cloud
2. Vaya a Configuración de usuario → Clave de autorización cloud (Auth Token)
3. Encuentre el Device ID en la información del dispositivo

### 6. Verificar instalación

Acceda al sistema desde su navegador:

```
http://localhost
```

o 

```
http://localhost/dunas
```

Inicie sesión con las credenciales de administrador (ver sección "Usuarios de Prueba")

## 👥 Usuarios de Prueba

El sistema incluye usuarios de ejemplo con las siguientes credenciales:

| Usuario    | Contraseña | Rol        |
|-----------|------------|------------|
| admin     | admin123   | Administrador |
| supervisor| admin123   | Supervisor |
| operator  | admin123   | Operador   |
| cliente1  | admin123   | Cliente    |

**⚠️ IMPORTANTE:** Cambie estas contraseñas en producción.

## 🗂️ Estructura del Proyecto

```
dunas/
├── app/
│   ├── controllers/      # Controladores MVC
│   ├── models/          # Modelos de datos
│   ├── views/           # Vistas (HTML/PHP)
│   │   ├── layouts/     # Layouts principales
│   │   ├── home/        # Vistas de home
│   │   ├── auth/        # Vistas de autenticación
│   │   ├── dashboard/   # Dashboard
│   │   ├── users/       # Gestión de usuarios
│   │   ├── clients/     # Gestión de clientes
│   │   ├── units/       # Gestión de unidades
│   │   ├── drivers/     # Gestión de choferes
│   │   ├── access/      # Control de acceso
│   │   ├── transactions/# Transacciones
│   │   └── reports/     # Reportes
│   └── helpers/         # Clases auxiliares
├── config/
│   ├── config.php       # Configuración principal
│   └── database.sql     # Script de base de datos
├── public/
│   ├── index.php        # Punto de entrada
│   ├── .htaccess        # Configuración Apache
│   ├── test-connection.php # Test de conexión
│   ├── css/             # Estilos personalizados
│   ├── js/              # Scripts JavaScript
│   ├── images/          # Imágenes del sistema
│   └── uploads/         # Archivos subidos
│       ├── units/       # Fotos de unidades
│       └── drivers/     # Fotos de choferes
├── logs/                # Logs del sistema
└── README.md            # Este archivo
```

## 🔐 Seguridad

El sistema implementa las siguientes medidas de seguridad:

- ✅ Contraseñas encriptadas con `password_hash()` (bcrypt)
- ✅ Sesiones seguras con timeout automático
- ✅ Validación de datos de entrada
- ✅ Protección contra SQL Injection (PDO prepared statements)
- ✅ Control de acceso basado en roles (RBAC)
- ✅ Validación de tipos de archivo en uploads
- ✅ Protección de archivos sensibles vía `.htaccess`

## 📱 Módulos del Sistema

### 1. Dashboard
- Estadísticas en tiempo real
- Gráficas de ingresos mensuales
- Accesos recientes
- Indicadores clave de rendimiento (KPI)

### 2. Gestión de Usuarios
- CRUD completo de usuarios
- 4 roles: Admin, Supervisor, Operador, Cliente
- Control de permisos por rol

### 3. Gestión de Clientes
- Registro de clientes (Residencial/Comercial/Industrial)
- Historial de transacciones por cliente
- Gestión de información de contacto

### 4. Gestión de Unidades (Pipas)
- Registro de unidades con fotos
- Control de capacidad en litros
- Historial de mantenimientos
- Estados: Activo/Mantenimiento/Inactivo

### 5. Gestión de Choferes
- Registro de choferes con fotos
- Control de licencias y vigencias
- Asignación de unidades
- Alertas de vencimiento de licencias

### 6. Control de Acceso
- Registro de entradas/salidas
- Generación de tickets con QR y códigos de barras
- Control de barreras con Shelly Relay
- Validación de accesos autorizados
- Detección automática de placas (ANPR) con Hikvision
- Sincronización FTP de imágenes vehiculares
- Comparación automática de placas detectadas vs. registradas

### 7. Transacciones y Pagos
- Registro de transacciones
- Múltiples métodos de pago (Efectivo, Vales, Transferencia)
- Control de estados (Pagado/Pendiente/Cancelado)
- Cálculo automático de montos

### 8. Reportes
- Reportes de acceso por período
- Reportes financieros
- Reportes operativos
- Exportación a Excel y PDF

## 🌐 URLs del Sistema

El sistema utiliza URLs amigables:

```
/                      → Página de inicio
/login                 → Inicio de sesión
/dashboard             → Dashboard principal
/users                 → Gestión de usuarios
/clients               → Gestión de clientes
/units                 → Gestión de unidades
/drivers               → Gestión de choferes
/access                → Control de acceso
/transactions          → Transacciones
/reports               → Reportes
/logout                → Cerrar sesión
```

## 🔌 API Shelly Relay

El sistema se integra con Shelly Relay SHELLPRO4PM para control automatizado de barreras vehiculares.

### Configuración

```php
// En config/config.php
define('SHELLY_API_URL', 'http://IP_DEL_DISPOSITIVO');
define('SHELLY_RELAY_OPEN', 0);   // Canal para abrir
define('SHELLY_RELAY_CLOSE', 1);  // Canal para cerrar
```

### Funciones disponibles

```php
ShellyAPI::openBarrier();   // Abrir barrera
ShellyAPI::closeBarrier();  // Cerrar barrera
ShellyAPI::getStatus();     // Obtener estado del dispositivo
```

## 📷 Sincronización FTP para Imágenes ANPR

El sistema incluye sincronización automática de imágenes vehiculares desde servidores FTP.

### Características

- Sincronización de imágenes desde cámaras ANPR Hikvision
- Detección automática de placas vehiculares
- Comparación de placas detectadas vs. registradas
- Botón de recarga manual para obtener imágenes actualizadas

### Configuración

1. Ejecutar la migración SQL:
```bash
mysql -u root -p dunas_access_control < config/migrations/add_ftp_settings.sql
```

2. Configurar los parámetros FTP en la tabla `settings`:
   - `ftp_host` - Servidor FTP
   - `ftp_port` - Puerto (predeterminado: 21)
   - `ftp_user` - Usuario
   - `ftp_pass` - Contraseña
   - `ftp_images_path` - Ruta de imágenes
   - `ftp_image_pattern` - Patrón de archivos

### Documentación

Para información detallada, consulte:
- **[FTP_IMAGE_SYNC_GUIDE.md](FTP_IMAGE_SYNC_GUIDE.md)** - Guía completa de configuración
- **[IMPLEMENTATION_RELOAD_BUTTON.md](IMPLEMENTATION_RELOAD_BUTTON.md)** - Documentación técnica

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP puro (sin frameworks)
- **Base de datos:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Estilos:** Tailwind CSS
- **Gráficas:** Chart.js
- **Iconos:** Font Awesome
- **Arquitectura:** MVC (Model-View-Controller)
- **IoT:** Shelly Relay API
- **ANPR:** Hikvision Cloud API

## 📊 Exportación de Reportes

El sistema soporta exportación de reportes en los siguientes formatos:

- **Excel (.xlsx)** - Para análisis de datos
- **PDF** - Para impresión y archivo

## 🐛 Troubleshooting

**Para soluciones detalladas, consulte [TROUBLESHOOTING.md](TROUBLESHOOTING.md)**

### Error: 404 - Página No Encontrada

Si al acceder a `/login` u otras páginas aparece error 404:
1. Verifique que `mod_rewrite` esté habilitado en Apache
2. Verifique que el archivo `.htaccess` exista en `public/`
3. Verifique que `AllowOverride All` esté configurado

**Ver [TROUBLESHOOTING.md](TROUBLESHOOTING.md#error-404---página-no-encontrada) para instrucciones detalladas**

### Error: No se puede conectar a la base de datos

Verifique:
1. Que MySQL esté ejecutándose
2. Las credenciales en `config/config.php`
3. Que la base de datos exista y esté importada

### Error: URL base incorrecta

El sistema detecta automáticamente la URL base. Si hay problemas:
1. Verifique el archivo `.htaccess` esté configurado
2. Verifique que `mod_rewrite` esté habilitado en Apache
3. El `.htaccess` está configurado para funcionar automáticamente en cualquier ruta (raíz o subdirectorio)

### Error: No se pueden subir archivos

Verifique:
1. Permisos de escritura en `public/uploads`
2. Configuración de `upload_max_filesize` en PHP
3. Configuración de `post_max_size` en PHP

### Error: Sesión expirada constantemente

Ajuste el timeout en `config/config.php`:

```php
define('SESSION_TIMEOUT', 7200); // 2 horas
```

### Más Soluciones

Para problemas adicionales y soluciones detalladas, consulte **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)**

## 📚 Documentación Adicional

### Guías Especializadas

- **[QUICK_REGISTRATION_GUIDE.md](QUICK_REGISTRATION_GUIDE.md)** - Guía completa del sistema de registro rápido con códigos de barras
- **[SHELLY_BRIDGE_SETUP.md](SHELLY_BRIDGE_SETUP.md)** - Configuración de bridge para control remoto de Shelly Relay
- **[SHELLY_API.md](SHELLY_API.md)** - Documentación de la API de Shelly Relay
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Solución de problemas comunes

### Características Nuevas (v1.2.0)

#### Sistema de Registro Rápido
- **Búsqueda inteligente** de unidades por número de placa
- **Creación automática** de unidades, clientes y choferes sobre la marcha
- **Generación de tickets** con código de barras de 4 dígitos
- **Impresión de tickets** optimizada para impresoras térmicas de 80mm

#### Escaneo de Salida Automatizado
- **Escaneo de código de barras** para registro rápido de salida
- **Registro automático** con capacidad máxima de la unidad
- **Control de barrera** automático al completar salida
- **Historial en tiempo real** de salidas recientes

Ver [QUICK_REGISTRATION_GUIDE.md](QUICK_REGISTRATION_GUIDE.md) para instrucciones detalladas.

## 📞 Soporte

Para problemas, preguntas o sugerencias, puede:

- Abrir un issue en GitHub
- Contactar al equipo de desarrollo

## 📝 Licencia

Este proyecto está desarrollado como sistema privado. Todos los derechos reservados.

## ✨ Contribuciones

Desarrollado por el equipo DUNAS para la gestión eficiente de control de acceso con IoT.

---

**Versión:** 1.2.0  
**Última actualización:** Octubre 2024
