# Características del Sistema DUNAS

## 📱 Módulos del Sistema

### 1. 🏠 Dashboard Principal

**Funcionalidades:**
- Vista general del sistema en tiempo real
- Tarjetas con estadísticas clave (KPI):
  - Clientes activos
  - Unidades activas
  - Choferes activos
  - Accesos en progreso
- Estadísticas del día actual:
  - Total de accesos
  - Litros suministrados
  - Ingresos generados
- Gráficas interactivas con Chart.js:
  - Ingresos mensuales (últimos 6 meses)
  - Tendencias de consumo
- Lista de accesos recientes
- Accesos rápidos a funciones principales

**Roles con acceso:** Todos los usuarios autenticados

---

### 2. 👥 Gestión de Usuarios

**Funcionalidades:**
- Crear, editar y eliminar usuarios del sistema
- 4 niveles de rol:
  - **Administrador:** Acceso completo al sistema
  - **Supervisor:** Visualización de informes y aprobación de accesos
  - **Operador:** Registro de entradas/salidas y transacciones
  - **Cliente:** Consulta de sus propios registros
- Autenticación segura con password_hash() (bcrypt)
- Gestión de permisos por rol
- Control de estado (Activo/Inactivo)
- Filtros por rol y estado

**Roles con acceso:** Solo Administradores

---

### 3. 🏢 Gestión de Clientes

**Funcionalidades:**
- Registro completo de clientes
- Datos almacenados:
  - Nombre/Razón social
  - RFC/CURP
  - Dirección completa
  - Teléfono y correo electrónico
  - Tipo de cliente (Residencial/Comercial/Industrial)
  - Estado (Activo/Inactivo)
- Vinculación opcional con usuario del sistema
- Historial completo de transacciones por cliente
- Filtros por tipo y estado
- Vista detallada con estadísticas de consumo

**Roles con acceso:** Admin, Supervisor, Operador (Cliente solo ve sus datos)

**Tipos de Cliente:**
- **Residencial:** Uso doméstico
- **Comercial:** Negocios y empresas
- **Industrial:** Uso industrial de alto volumen

---

### 4. 🚛 Gestión de Unidades (Pipas)

**Funcionalidades:**
- Registro detallado de unidades
- Datos almacenados:
  - Número de placa (único)
  - Capacidad máxima en litros
  - Marca y modelo
  - Año de fabricación
  - Número de serie (único)
  - Fotografía de la unidad
  - Estado (Activo/Mantenimiento/Inactivo)
- Carga de imágenes con validación
- Historial completo de mantenimientos:
  - Fecha de mantenimiento
  - Descripción detallada
  - Costo del mantenimiento
  - Responsable del servicio
- Filtros por estado
- Estadísticas de uso y eficiencia

**Roles con acceso:** Admin, Supervisor, Operador (solo lectura)

**Estados de Unidad:**
- **Activo:** En operación
- **Mantenimiento:** En reparación o servicio
- **Inactivo:** Fuera de servicio

---

### 5. 🪪 Gestión de Choferes

**Funcionalidades:**
- Registro completo de conductores
- Datos almacenados:
  - Nombre completo
  - Número de licencia (único)
  - Fecha de vigencia de licencia
  - Teléfono de contacto
  - Fotografía del chofer
  - Estado (Activo/Inactivo)
- Carga de fotografía con validación
- Sistema de alertas:
  - Licencias próximas a vencer (30 días)
  - Notificaciones visuales en dashboard
- Asignación de unidades a choferes
- Historial de asignaciones
- Filtros por estado

**Roles con acceso:** Admin, Supervisor, Operador (solo lectura)

---

### 6. 🚪 Control de Acceso

**Funcionalidades principales:**

#### Registro de Entrada
- Selección de chofer, unidad y cliente
- Generación automática de ticket único
- Generación de código QR (para escaneo rápido)
- Generación de código de barras
- Registro de fecha/hora de entrada automático
- **Integración IoT:** Apertura automática de barrera vehicular vía Shelly Relay

#### Registro de Salida
- Captura de litros suministrados
- Cálculo automático de tiempo de estancia
- Registro de fecha/hora de salida
- Cambio de estado a "Completado"
- **Integración IoT:** Cierre automático de barrera vehicular

#### Gestión de Tickets
- Códigos únicos por acceso
- Formato: TKT[YYYYMMDD][9999]
- Búsqueda rápida por ticket
- Visualización de QR y código de barras

#### Control Manual de Barreras
- Botón manual para abrir barrera
- Botón manual para cerrar barrera
- Estado en tiempo real del dispositivo Shelly

#### Filtros y Búsqueda
- Por estado (En Progreso/Completado/Cancelado)
- Por rango de fechas
- Por cliente, unidad o chofer
- Vista de accesos en progreso

**Roles con acceso:** Admin, Supervisor, Operador

---

### 7. 💰 Transacciones y Pagos

**Funcionalidades:**
- Registro de transacciones vinculadas a accesos
- Datos de transacción:
  - Litros suministrados
  - Precio por litro
  - Monto total (cálculo automático)
  - Fecha y hora de transacción
  - Cliente asociado
  - Notas adicionales

#### Métodos de Pago
- **Efectivo:** Pago en moneda
- **Vales:** Pago con vales o cupones
- **Transferencia Bancaria:** Pago electrónico

#### Estados de Pago
- **Pagado:** Transacción completada
- **Pendiente:** Pago por realizar
- **Cancelado:** Transacción anulada

#### Funcionalidades Adicionales
- Actualización de estado de pago
- Edición de montos y notas
- Filtros por:
  - Estado de pago
  - Método de pago
  - Rango de fechas
- Vista detallada con información completa
- Vinculación automática con acceso

**Roles con acceso:** Admin, Supervisor, Operador

---

### 8. 📊 Módulo de Reportes

#### 8.1 Reporte de Accesos

**Información mostrada:**
- Total de accesos en el período
- Accesos completados vs en progreso
- Total de litros suministrados
- Tabla detallada de todos los accesos
- Estadísticas por:
  - Cliente
  - Unidad
  - Chofer
  - Horarios (análisis de horarios pico)

**Filtros:**
- Rango de fechas personalizado
- Estado de acceso

**Exportación:**
- Excel (.xlsx)
- PDF

#### 8.2 Reporte Financiero

**Información mostrada:**
- Total de ingresos en el período
- Total de litros vendidos
- Número de transacciones
- Promedio por transacción
- Desglose por método de pago:
  - Efectivo
  - Vales
  - Transferencias bancarias
- Gráfica de ingresos por día (Chart.js)
- Tabla detallada de transacciones

**Filtros:**
- Rango de fechas personalizado
- Método de pago
- Estado de pago

**Exportación:**
- Excel (.xlsx)
- PDF

#### 8.3 Reporte Operativo

**Información mostrada:**
- Eficiencia de unidades:
  - Número de viajes por unidad
  - Litros transportados por unidad
  - Unidades más utilizadas
- Rendimiento de choferes:
  - Viajes realizados por chofer
  - Litros manejados por chofer
  - Choferes más activos
- Consumo por tipo de cliente:
  - Residencial
  - Comercial
  - Industrial
- Análisis de tendencias

**Filtros:**
- Rango de fechas personalizado

**Exportación:**
- Excel (.xlsx)
- PDF

#### 8.4 Accesos Rápidos
- Reporte de accesos de hoy
- Ingresos del mes actual
- Rendimiento del mes
- Ingresos anuales

**Roles con acceso:** Admin, Supervisor

---

## ☁️ Integración IoT - Shelly Cloud API

### Características de la Integración

**Dispositivo Compatible:**
- Shelly Pro 4PM (4 canales)
- Control de barreras vehiculares vía Cloud API

**Funcionalidades:**

1. **Control Automático:**
   - Apertura automática al registrar entrada (Switch OFF)
   - Cierre automático al registrar salida (Switch ON)
   - Reintentos automáticos (hasta 3 intentos)

2. **Control Manual:**
   - Botones de control en interfaz web
   - Apertura/cierre manual desde el sistema
   - Control remoto desde cualquier ubicación

3. **Cloud API:**
   - Comunicación vía HTTPS al Shelly Cloud
   - Comandos de control:
     - `POST /device/relay/control` con `turn=off` - Abrir barrera
     - `POST /device/relay/control` con `turn=on` - Cerrar barrera
   - Consulta de estado: `POST /device/status`
   - Autenticación mediante Auth Token

4. **Manejo de Errores:**
   - Timeout configurable (15 segundos)
   - Logs detallados de errores de comunicación
   - Notificaciones en caso de fallo
   - Modo manual de respaldo
   - Sistema de reintentos automáticos

**Configuración:**
```php
define('SHELLY_AUTH_TOKEN', 'YOUR_AUTH_TOKEN');    // Token del Cloud API
define('SHELLY_DEVICE_ID', 'YOUR_DEVICE_ID');      // ID del dispositivo
define('SHELLY_SERVER', 'shelly-XXX-eu.shelly.cloud'); // Servidor Cloud
define('SHELLY_SWITCH_ID', 0);                     // Canal del switch
```

---

## 🔐 Seguridad del Sistema

### Autenticación
- Contraseñas encriptadas con bcrypt
- Sesiones seguras con timeout
- Protección contra fuerza bruta
- Tokens CSRF (preparado para implementar)

### Autorización
- Control de acceso basado en roles (RBAC)
- Verificación de permisos en cada acción
- Páginas protegidas por rol
- Auditoría de acciones (logs)

### Validación de Datos
- Validación del lado del servidor
- Protección contra SQL Injection (PDO)
- Validación de tipos de archivo
- Sanitización de entradas

### Archivos
- Validación de tipos MIME
- Límite de tamaño (5MB)
- Nombres únicos generados
- Almacenamiento seguro

---

## 💻 Tecnologías Utilizadas

### Backend
- **PHP puro** - Sin frameworks, código limpio
- **MySQL 5.7+** - Base de datos relacional
- **PDO** - Capa de abstracción de datos
- **bcrypt** - Encriptación de contraseñas

### Frontend
- **HTML5** - Estructura semántica
- **Tailwind CSS** - Diseño responsivo
- **JavaScript vanilla** - Interactividad
- **Chart.js** - Gráficas y visualizaciones
- **Font Awesome** - Iconografía

### Arquitectura
- **MVC** - Model-View-Controller
- **REST API** - Para Shelly Relay
- **URL Rewriting** - URLs amigables

---

## 📈 Características Adicionales

### Diseño Responsivo
- Optimizado para desktop
- Compatible con tablets
- Funcional en móviles
- Navegación adaptativa

### Experiencia de Usuario
- Interfaz intuitiva y moderna
- Mensajes flash de confirmación
- Iconografía clara
- Colores semánticos
- Feedback visual inmediato

### Rendimiento
- Consultas optimizadas
- Paginación implementable
- Carga asíncrona preparada
- Caché de imágenes

### Mantenibilidad
- Código documentado
- Estructura MVC clara
- Separación de responsabilidades
- Fácil extensión

---

## 🚀 Funcionalidades Futuras (Roadmap)

### Corto Plazo
- [ ] Generación real de códigos QR y barras
- [ ] Exportación completa a Excel
- [ ] Exportación completa a PDF
- [ ] Integración con FullCalendar.js
- [ ] Notificaciones por email

### Mediano Plazo
- [ ] App móvil complementaria
- [ ] Dashboard en tiempo real (WebSockets)
- [ ] API REST completa
- [ ] Sistema de notificaciones push
- [ ] Reportes programados automáticos

### Largo Plazo
- [ ] Machine Learning para predicción de demanda
- [ ] Integración con GPS de unidades
- [ ] Sistema de facturación electrónica
- [ ] Portal de autoservicio para clientes
- [ ] Integración con sistemas ERP

---

## 📞 Soporte

Para más información sobre características específicas, consultar:
- **README.md** - Documentación general
- **INSTALLATION_GUIDE.md** - Guía de instalación
- **Repositorio GitHub** - Código fuente y issues

---

**Versión:** 1.0.0  
**Última actualización:** Octubre 2024
