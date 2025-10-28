# DUNAS System - Update Guide v1.1.0

## Resumen de Cambios

Esta actualización resuelve los siguientes problemas y agrega nuevas funcionalidades al sistema:

### Problemas Resueltos ✅

1. **Error de Archivos Faltantes**
   - Se crearon los archivos de vista `app/views/reports/access.php` y `app/views/reports/operational.php` que causaban errores fatales

2. **Gráficas con Carga Infinita**
   - Se corrigió el problema de las gráficas que cargaban infinitamente en:
     - Ingresos del Mes
     - Ingresos Anuales
     - Ingresos por Día en Reporte Financiero
     - Gráfica de Ingresos Mensuales del dashboard principal
   - Se agregó manejo de errores y validación de datos

### Nuevas Funcionalidades ⭐

1. **Nueva Transacción** - Gestión de Transacciones
   - Formulario completo para crear transacciones
   - Cálculo automático de totales
   - Validación de datos
   - Soporte para diferentes métodos de pago

2. **Nuevo Acceso** - Accesos Rápidos
   - Formulario completo para registrar entradas
   - Selección de cliente, unidad y chofer
   - Generación automática de ticket
   - Integración con Shelly Relay para apertura de barrera

3. **Sistema de Perfil de Usuario**
   - Edición de datos personales
   - Cambio de contraseña
   - Validación de seguridad

4. **Sistema de Configuraciones**
   - Nombre del sitio y logotipo
   - Configuración de correo del sistema
   - Número de WhatsApp del chatbot
   - Teléfonos de contacto y horarios de atención
   - Configuración de Shelly Relay API
   - Mensaje personalizado en pie de tickets

5. **Menú de Usuario Mejorado**
   - Dropdown con opciones de Perfil y Configuraciones
   - Mejor organización de opciones

## Instrucciones de Instalación

### 1. Actualizar Base de Datos

Ejecute el siguiente script SQL en su base de datos:

```bash
mysql -u [usuario] -p [nombre_base_datos] < config/update_1.1.0.sql
```

O manualmente desde phpMyAdmin/cliente MySQL:
```sql
-- Abrir el archivo config/update_1.1.0.sql y ejecutar su contenido
```

Este script:
- Crea la tabla `settings` para almacenar configuraciones
- Inserta valores por defecto
- Agrega índices para mejorar el rendimiento
- Valida la integridad de datos existentes

### 2. Verificar Archivos

Asegúrese de que los siguientes archivos nuevos estén presentes:

**Controladores:**
- `app/controllers/ProfileController.php`
- `app/controllers/SettingsController.php`

**Modelos:**
- `app/models/Settings.php`

**Vistas:**
- `app/views/reports/access.php`
- `app/views/reports/operational.php`
- `app/views/profile/index.php`
- `app/views/settings/index.php`

**Archivos Actualizados:**
- `app/views/layouts/main.php` - Menú de usuario mejorado
- `app/views/transactions/create.php` - Formulario completo
- `app/views/access/create.php` - Formulario completo
- `app/views/reports/financial.php` - Gráficas corregidas
- `public/index.php` - Routing actualizado

### 3. Configurar Permisos

Asegúrese de que el directorio de uploads tenga permisos de escritura:

```bash
chmod 755 public/uploads
chmod 755 public/uploads/logos
```

Si el directorio `logos` no existe:
```bash
mkdir -p public/uploads/logos
chmod 755 public/uploads/logos
```

### 4. Configurar el Sistema

1. Inicie sesión como **administrador**
2. Navegue a su nombre de usuario en la esquina superior derecha
3. Seleccione **Configuraciones**
4. Complete los campos según sus necesidades:
   - Nombre del sitio
   - Correo del sistema
   - Número de WhatsApp
   - Teléfonos de contacto
   - Horarios de atención
   - Configuración de Shelly Relay
   - Mensaje de tickets

## Características Detalladas

### Nueva Transacción

Permite crear transacciones manualmente desde el módulo de Transacciones:
- Calcula automáticamente el total basado en litros y precio
- Soporta efectivo, vales y transferencia bancaria
- Estado de pago configurable (pendiente/pagado)
- Notas adicionales opcionales

### Nuevo Acceso

Registra entradas de unidades al sistema:
- Selección de cliente, unidad y chofer
- Generación automática de código de ticket
- Integración con IoT (Shelly Relay) para apertura automática de barrera
- Registro de fecha y hora de entrada

### Reportes Completos

**Reporte de Accesos:**
- Lista detallada de entradas y salidas
- Filtros por fecha
- Estadísticas de accesos por estado
- Exportación a Excel y PDF

**Reporte Operativo:**
- Eficiencia por unidad (viajes y litros)
- Rendimiento por chofer
- Consumo por tipo de cliente
- Exportación a Excel y PDF

### Sistema de Configuraciones

Panel centralizado para administrar:
- Información general del sitio
- Configuración de comunicaciones (email, WhatsApp)
- Datos de contacto y horarios
- Integración IoT (Shelly Relay)
- Personalización de tickets

## Notas de Seguridad

1. **Permisos de Configuraciones**: Solo usuarios con rol `admin` pueden acceder a las configuraciones
2. **Cambio de Contraseña**: Requiere validación de contraseña actual
3. **Validación de Datos**: Todos los formularios tienen validación del lado del servidor
4. **Sesiones Seguras**: Las sesiones utilizan cookies HTTP-only

## Solución de Problemas

### Las gráficas no se muestran

1. Verifique que hay datos en el período seleccionado
2. Abra la consola del navegador (F12) y busque errores
3. Verifique que Chart.js se está cargando correctamente

### Error al subir logo

1. Verifique permisos del directorio `public/uploads/logos`
2. Verifique tamaño del archivo (máximo 5MB)
3. Use formatos JPG o PNG

### Error "Failed to open stream"

Si ve errores relacionados con archivos faltantes:
1. Asegúrese de que todos los archivos de vista estén presentes
2. Verifique permisos de lectura en el directorio `app/views`

### Configuraciones no se guardan

1. Verifique que la tabla `settings` existe en la base de datos
2. Verifique permisos de escritura en la base de datos
3. Revise los logs de errores PHP

## Compatibilidad

- **PHP**: 7.4 o superior (recomendado 8.0+)
- **MySQL**: 5.7 o superior
- **Navegadores**: Chrome, Firefox, Safari, Edge (últimas 2 versiones)

## Soporte

Para reportar problemas o solicitar ayuda:
1. Revise primero esta guía
2. Verifique los logs de error del sistema
3. Contacte al administrador del sistema

## Registro de Cambios

### Versión 1.1.0 (2024-10-28)

**Agregado:**
- Vistas de reportes de acceso y operativo
- Formulario de nueva transacción
- Formulario de nuevo acceso
- Sistema de perfil de usuario
- Sistema de configuraciones
- Menú dropdown de usuario
- Tabla de configuraciones en BD
- Script de migración SQL

**Corregido:**
- Gráficas con carga infinita
- Error de archivos de vista faltantes
- Validación de datos en formularios
- Manejo de errores en gráficas

**Mejorado:**
- Experiencia de usuario en navegación
- Validación de formularios
- Documentación del sistema
- Estructura de base de datos

---

© 2024 Sistema de Control de Acceso con IoT - DUNAS v1.1.0
