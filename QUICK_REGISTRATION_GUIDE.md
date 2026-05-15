# Sistema de Registro Rápido - DUNAS

## 📋 Descripción General

El sistema de Registro Rápido permite registrar entradas y salidas de unidades (pipas) de manera eficiente mediante un proceso simplificado que incluye:
- Búsqueda rápida de unidades por placa
- Creación automática de unidades, clientes y choferes si no existen
- Generación de tickets con código de barras de 4 dígitos
- Escaneo de código de barras para salida automática
- Registro automático con capacidad máxima de la unidad

---

## 🚀 Características Principales

### 1. Registro Rápido de Entrada

#### Acceso
- **URL:** `/access/quickRegistration`
- **Ruta:** Control de Acceso → Registro Rápido
- **Permisos:** Admin, Supervisor, Operador

#### Flujo de Trabajo

**Paso 1: Buscar Unidad**
- Ingresar número de placa de la unidad
- El sistema busca automáticamente si la unidad existe
- Resultados:
  - ✅ **Unidad encontrada:** Muestra información y continúa al registro
  - ⚠️ **Unidad no encontrada:** Solicita datos para crear la unidad

**Paso 2: Datos de la Unidad (si no existe)**
- Capacidad en litros (obligatorio, seleccionando una opción existente)
- Marca (opcional)
- Modelo (opcional)
- Año (opcional)
- Número de serie (opcional)

**Paso 3: Datos del Cliente**
- Nombre de la empresa (obligatorio)
- Teléfono (obligatorio)
- RFC/CURP (opcional)
- Tipo de cliente: Comercial, Residencial, Industrial
- En flujo con vale: el vale debe estar relacionado a un cliente para continuar
- Dirección (opcional)

**Paso 4: Datos del Chofer**
- Nombre completo (obligatorio)
- Teléfono (obligatorio)
- Número de licencia (opcional)
- Vigencia de licencia (opcional)

**Resultado:**
- ✅ Entrada registrada exitosamente
- 🎫 Ticket generado con código de barras de 4 dígitos
- 🚪 Barrera abierta automáticamente
- 📄 Ticket listo para imprimir

---

### 2. Escaneo de Salida

#### Acceso
- **URL:** `/access/scanExit`
- **Ruta:** Control de Acceso → Escanear Salida
- **Permisos:** Admin, Supervisor, Operador

#### Flujo de Trabajo

**Escaneo del Código:**
1. Usar lector de código de barras o ingresar manualmente
2. Código de 4 dígitos del ticket de entrada
3. Auto-submit al completar 4 dígitos

**Procesamiento:**
- ✅ Valida el código de barras
- ✅ Verifica que el acceso esté en progreso
- ✅ Registra salida con capacidad máxima de la unidad
- ✅ Cierra la barrera automáticamente

**Resultado:**
- ✅ Salida registrada exitosamente
- 📊 Muestra información: Unidad, Cliente, Chofer, Litros
- 🚪 Barrera cerrada automáticamente
- 📜 Registro en historial de salidas recientes

---

### 3. Impresión de Ticket

#### Características del Ticket

**Contenido:**
- 🏢 Encabezado: DUNAS - Control de Acceso
- 📊 Código de barras generado automáticamente
- 🔢 Código de 4 dígitos (grande y legible)
- 📅 Fecha y hora de entrada
- 🚛 Información de la unidad (placa y capacidad)
- 👤 Cliente y chofer
- 📝 Instrucciones de uso

**Formato:**
- Tamaño: 80mm de ancho (compatible con impresoras de tickets)
- Bordes punteados para recorte
- Diseño optimizado para impresión térmica
- Código de barras en formato CODE128

**Funcionalidad:**
- 🖨️ Botón de impresión directa
- 📱 Compatible con dispositivos móviles
- 🔄 Posibilidad de reimprimir desde el historial

---

## 💻 Implementación Técnica

### Estructura de Archivos

```
app/
├── controllers/
│   └── AccessController.php
│       ├── quickRegistration()      # Vista de registro rápido
│       ├── searchUnit()             # API búsqueda de unidad
│       ├── quickEntry()             # Procesar entrada rápida
│       ├── scanExit()               # Vista de escaneo
│       ├── processExit()            # API procesar salida
│       └── printTicket($id)         # Vista de impresión de ticket
├── models/
│   ├── Unit.php
│   │   ├── findByPlateNumber()     # Buscar por placa
│   │   └── searchByPlateNumber()   # Búsqueda fuzzy
│   └── AccessLog.php
│       ├── generateTicketCode()     # Código de 4 dígitos único
│       └── getByTicket()            # Buscar por código
└── views/
    └── access/
        ├── quick_registration.php   # Formulario de registro rápido
        ├── scan_exit.php           # Escáner de salida
        └── print_ticket.php        # Ticket imprimible
```

### Generación de Códigos de Barras

**Algoritmo:**
```php
private function generateTicketCode() {
    // Generar código de 4 dígitos único
    $attempts = 0;
    $maxAttempts = 100;
    
    do {
        // rand(1000, 9999) ya genera 4 dígitos
        $code = (string)rand(1000, 9999);
        // Verificar si el código ya existe hoy
        $sql = "SELECT COUNT(*) as count FROM access_logs 
                WHERE ticket_code = ? AND DATE(entry_datetime) = CURDATE()";
        $result = $this->db->fetchOne($sql, [$code]);
        
        if ($result['count'] == 0) {
            return $code;
        }
        
        $attempts++;
    } while ($attempts < $maxAttempts);
    
    // Fallback: usar últimos 2 dígitos de hora + 2 dígitos aleatorios (garantiza 4 dígitos)
    return substr(date('His'), -2) . str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
}
```

**Características:**
- Códigos de 4 dígitos (1000-9999)
- Únicos por día
- Máximo 100 intentos de generación
- Fallback a timestamp si no encuentra código único
- Validación automática de unicidad

### Librería de Códigos de Barras

**JsBarcode:**
```javascript
JsBarcode("#barcode", "<?php echo $access['ticket_code']; ?>", {
    format: "CODE128",  // Formato estándar compatible
    width: 2,           // Ancho de las barras
    height: 60,         // Altura en píxeles
    displayValue: false, // No mostrar texto debajo
    margin: 0           // Sin márgenes
});
```

---

## 🔧 Configuración

### Requisitos

1. **Servidor Web:** Apache con PHP 7.4+
2. **Base de datos:** MySQL 5.7+
3. **Navegador:** Compatible con JavaScript moderno
4. **Opcional:** Lector de código de barras USB/Bluetooth

### Instalación

1. **Actualizar Base de Datos:**
```bash
mysql -u root -p dunas_access_control < config/update_1.2.0.sql
```

2. **Verificar Permisos:**
```bash
# Asegurar permisos de escritura
chmod 755 app/controllers/AccessController.php
chmod 755 app/views/access/
```

3. **Configurar Impresora (Opcional):**
- Compatible con impresoras térmicas de 80mm
- Configurar impresora predeterminada en el navegador
- Probar impresión desde la vista de ticket

### Configuración de Lector de Código de Barras

**Modo Teclado (Keyboard Wedge):**
- La mayoría de lectores funcionan como teclado USB
- El código se ingresa automáticamente en el campo de texto
- No requiere configuración adicional

**Configuración Recomendada:**
- Sufijo: Enter (para auto-submit)
- Prefijo: Ninguno
- Formato: Números (0-9)

---

## 📱 Uso del Sistema

### Para Operadores

#### Registrar Entrada Rápida

1. Clic en **"Registro Rápido"** desde Control de Acceso
2. Ingresar o escanear número de placa
3. Completar datos solicitados (solo si la unidad/cliente/chofer no existen)
4. Clic en **"Registrar Entrada"**
5. El sistema:
   - Abre la barrera automáticamente
   - Genera ticket con código de barras
   - Muestra ticket para imprimir

#### Registrar Salida con Escaneo

1. Clic en **"Escanear Salida"** desde Control de Acceso
2. Enfocar el campo de código de barras
3. Escanear el código del ticket (o ingresar manualmente)
4. El sistema:
   - Valida el código
   - Registra salida con capacidad máxima
   - Cierra la barrera automáticamente
   - Muestra confirmación con detalles

### Para Administradores

#### Monitoreo

- Ver historial de salidas recientes en tiempo real
- Verificar códigos de barras procesados
- Revisar entradas/salidas en Control de Acceso

#### Configuración

- Ajustar capacidades de unidades desde Gestión de Unidades
- Configurar clientes y choferes predeterminados
- Revisar logs de errores en caso de problemas

---

## 🎯 Ventajas del Sistema

### Velocidad
- ⚡ Registro completo en menos de 1 minuto
- ⚡ Salida instantánea con escaneo de código
- ⚡ Auto-apertura y cierre de barrera

### Precisión
- ✅ Códigos únicos de 4 dígitos
- ✅ Validación automática de datos
- ✅ Registro con capacidad máxima precisa

### Flexibilidad
- 🔄 Crear unidades/clientes/choferes sobre la marcha
- 🔄 Buscar unidades existentes rápidamente
- 🔄 Escanear o ingresar manualmente

### Automatización
- 🤖 Apertura/cierre automático de barrera
- 🤖 Cálculo automático de litros (capacidad máxima)
- 🤖 Generación automática de tickets

---

## 🔍 Troubleshooting

### Problema: Código de barras no se escanea

**Solución:**
1. Verificar que el lector esté en modo teclado (keyboard wedge)
2. Probar escanear en un editor de texto (Notepad)
3. Si funciona en Notepad pero no en el sistema:
   - Verificar que el campo de código esté enfocado
   - Revisar configuración de sufijo (debe ser Enter)
4. Como alternativa: ingresar manualmente el código de 4 dígitos

### Problema: Ticket no se imprime correctamente

**Solución:**
1. Verificar configuración de impresora:
   - Tamaño de papel: 80mm
   - Orientación: Vertical
   - Márgenes: Mínimos
2. Probar con "Vista previa de impresión" del navegador
3. Verificar que JsBarcode se cargó correctamente (ver consola de JavaScript)
4. Reimprimir desde el navegador usando Ctrl+P

### Problema: Código duplicado o ya usado

**Solución:**
- El sistema genera códigos únicos automáticamente
- Si aparece este error:
  1. Verificar que la fecha/hora del servidor sea correcta
  2. Revisar que no haya entradas con fecha/hora futura
  3. Los códigos son únicos solo para el día actual

### Problema: Barrera no se abre/cierra

**Solución:**
- Ver [SHELLY_BRIDGE_SETUP.md](SHELLY_BRIDGE_SETUP.md) para configuración del relay
- El sistema continúa funcionando aunque falle la barrera
- Usar control manual de la barrera como respaldo

---

## 📊 Estadísticas y Reportes

### Métricas Disponibles

El sistema de registro rápido genera datos para:
- Total de registros rápidos por día/semana/mes
- Tiempo promedio de registro
- Tasa de éxito de escaneo de salida
- Unidades nuevas creadas automáticamente
- Clientes y choferes registrados

### Exportación

Todos los registros se pueden exportar desde:
- **Reportes → Reporte de Acceso**
- Formatos: Excel, PDF
- Incluye códigos de barras y timestamps

---

## 🔐 Seguridad

### Validaciones Implementadas

- ✅ Códigos únicos por día
- ✅ Validación de estado del acceso (solo registrar salida si está en progreso)
- ✅ Autenticación requerida (roles: admin, supervisor, operador)
- ✅ Validación de datos de entrada (campos obligatorios)
- ✅ Protección contra SQL injection (prepared statements)

### Logs

Todas las operaciones se registran en:
- Tabla `access_logs`: Registro completo de entradas/salidas
- Archivo `logs/error.log`: Errores del sistema
- Base de datos: Timestamps de creación y actualización

---

## 📚 API Endpoints

### Buscar Unidad
```
GET /access/searchUnit?plate=ABC123
```

**Respuesta exitosa (unidad existe):**
```json
{
    "success": true,
    "exists": true,
    "unit": {
        "id": 5,
        "plate_number": "ABC123",
        "capacity_liters": 20000,
        "brand": "Kenworth",
        "model": "T800"
    }
}
```

**Respuesta exitosa (unidad no existe):**
```json
{
    "success": true,
    "exists": false
}
```

### Procesar Salida
```
POST /access/processExit
Content-Type: application/x-www-form-urlencoded

barcode=1234
```

**Respuesta exitosa:**
```json
{
    "success": true,
    "message": "Salida registrada exitosamente con 20,000 litros.",
    "access": {
        "id": 123,
        "plate_number": "ABC123",
        "client_name": "Empresa XYZ",
        "driver_name": "Juan Pérez",
        "capacity_liters": 20000
    }
}
```

**Respuesta error:**
```json
{
    "success": false,
    "message": "Código de barras no válido o no encontrado"
}
```

---

## 🔄 Actualizaciones Futuras

### Mejoras Planificadas

- [ ] Integración con cámara para escaneo QR desde móvil
- [ ] App móvil dedicada para operadores
- [ ] Notificaciones push al completar entrada/salida
- [ ] Dashboard en tiempo real de unidades en planta
- [ ] Integración con sistema de peso (báscula)
- [ ] Firma digital del chofer en el ticket

### Feedback

Para sugerencias o reportar problemas:
1. Crear issue en GitHub
2. Contactar al equipo de soporte
3. Documentar pasos para reproducir el problema

---

## ✅ Lista de Verificación de Instalación

- [ ] Base de datos actualizada con `update_1.2.0.sql`
- [ ] Permisos de archivos configurados correctamente
- [ ] JsBarcode cargándose correctamente (verificar en ticket)
- [ ] Lector de código de barras configurado (si aplica)
- [ ] Impresora térmica configurada (si aplica)
- [ ] Barrera Shelly configurada (opcional, ver SHELLY_BRIDGE_SETUP.md)
- [ ] Usuarios con permisos de operador creados
- [ ] Prueba completa: entrada → impresión → salida

---

**Versión:** 1.2.0  
**Última actualización:** Octubre 2024  
**Sistema:** DUNAS - Control de Acceso con IoT
