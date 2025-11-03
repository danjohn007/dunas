# API de Integración Shelly Cloud - Sistema DUNAS

## 📡 Descripción General

El sistema DUNAS se integra con dispositivos Shelly Pro 4PM para control automatizado de barreras vehiculares mediante el **Shelly Cloud API**. Esta integración permite controlar el dispositivo remotamente sin necesidad de conexión directa por IP local.

## 🔧 Configuración

### Archivo de Configuración
**Ubicación:** `config/config.php`

```php
// Configuración de Shelly Pro 4PM via Cloud API
define('SHELLY_AUTH_TOKEN', 'YOUR_AUTH_TOKEN');     // Token de autenticación del Cloud API
define('SHELLY_DEVICE_ID', 'YOUR_DEVICE_ID');       // ID del dispositivo Shelly
define('SHELLY_SERVER', 'shelly-XXX-eu.shelly.cloud'); // Servidor Cloud de Shelly
define('SHELLY_API_TIMEOUT', 15);                   // Timeout para conexión en segundos
define('SHELLY_SWITCH_ID', 0);                      // ID del switch para abrir/cerrar barrera
define('SHELLY_ENABLED', true);                     // Habilitado con Cloud API
```

### Obtener Credenciales

1. **Auth Token**: Acceda a la aplicación Shelly Cloud → Configuración de usuario → Clave de autorización cloud
2. **Device ID**: Encontrará el ID en la información del dispositivo en la aplicación Shelly Cloud
3. **Server**: El servidor se asigna según su región (ej: shelly-208-eu.shelly.cloud)

## 📚 Clase ShellyAPI

**Ubicación:** `app/helpers/ShellyAPI.php`

### Métodos Disponibles

#### `ShellyAPI::openBarrier()`
Abre la barrera vehicular activando el relay configurado.

**Uso:**
```php
$result = ShellyAPI::openBarrier();
if ($result['success']) {
    echo "Barrera abierta exitosamente";
} else {
    echo "Error: " . $result['error'];
}
```

**Retorno:**
```php
[
    'success' => bool,  // true si exitoso, false si error
    'error' => string,  // Mensaje de error (solo si success = false)
    'data' => array     // Datos de respuesta del dispositivo
]
```

**Comportamiento:**
1. Envía comando al Shelly Cloud API para apagar el switch (on=false)
2. El comando se transmite a través de la nube al dispositivo
3. Registra la operación y errores en logs
4. Reintenta automáticamente hasta 3 veces en caso de fallo

---

#### `ShellyAPI::closeBarrier()`
Cierra la barrera vehicular activando el relay configurado.

**Uso:**
```php
$result = ShellyAPI::closeBarrier();
if ($result['success']) {
    echo "Barrera cerrada exitosamente";
} else {
    echo "Error: " . $result['error'];
}
```

**Retorno:**
```php
[
    'success' => bool,
    'error' => string,
    'data' => array
]
```

**Comportamiento:**
1. Envía comando al Shelly Cloud API para encender el switch (on=true)
2. El comando se transmite a través de la nube al dispositivo
3. Registra la operación y errores en logs
4. Reintenta automáticamente hasta 3 veces en caso de fallo

---

#### `ShellyAPI::getStatus()`
Obtiene el estado actual del dispositivo Shelly.

**Uso:**
```php
$result = ShellyAPI::getStatus();
if ($result['success']) {
    print_r($result['data']);
}
```

**Retorno:**
```php
[
    'success' => bool,
    'error' => string,
    'data' => [
        // Información del estado del dispositivo desde Cloud API
        'isok' => bool,
        'data' => [
            'online' => bool,
            'device_status' => [...],
            // ... más datos
        ]
    ]
]
```

---

#### `ShellyAPI::getRelayStatus($channel)`
Obtiene el estado de un relay específico.

**Parámetros:**
- `$channel` (int): Número del canal del relay (0-3)

**Uso:**
```php
$result = ShellyAPI::getRelayStatus(0);
if ($result['success']) {
    $isOn = $result['data']['ison'];
    echo $isOn ? "Relay encendido" : "Relay apagado";
}
```

**Retorno:**
```php
[
    'success' => bool,
    'error' => string,
    'data' => [
        'ison' => bool,        // Estado del relay (true = encendido)
        'has_timer' => bool,   // Si tiene timer activo
        'timer_remaining' => int, // Segundos restantes del timer
        'overpower' => bool,   // Si está en sobre-potencia
        'is_valid' => bool     // Si los datos son válidos
    ]
]
```

---

## 🌐 Endpoints del Shelly Cloud API

### Estructura de URLs

**Base URL:** `https://{SHELLY_SERVER}`

### Control de Relay

#### Controlar Relay (Encender/Apagar)
```
POST https://{SHELLY_SERVER}/device/relay/control
```

**Parámetros (form-urlencoded):**
- `auth_key`: Token de autenticación
- `id`: Device ID
- `channel`: Número del canal (0-3)
- `turn`: `on` para encender, `off` para apagar

**Ejemplo con curl:**
```bash
curl -X POST "https://shelly-208-eu.shelly.cloud/device/relay/control" \
     -d "auth_key=YOUR_AUTH_TOKEN" \
     -d "id=YOUR_DEVICE_ID" \
     -d "channel=0" \
     -d "turn=on"
```

**Respuesta exitosa (JSON):**
```json
{
    "isok": true,
    "data": {
        "device_id": "34987A67DA6C",
        "channel": 0,
        "state": "on"
    }
}
```

### Consulta de Estado

#### Estado del Dispositivo
```
POST https://{SHELLY_SERVER}/device/status
```

**Parámetros:**
- `auth_key`: Token de autenticación
- `id`: Device ID

**Ejemplo con curl:**
```bash
curl -X POST "https://shelly-208-eu.shelly.cloud/device/status" \
     -d "auth_key=YOUR_AUTH_TOKEN" \
     -d "id=YOUR_DEVICE_ID"
```

**Respuesta (JSON):**
```json
{
    "isok": true,
    "data": {
        "online": true,
        "device_status": {
            "switch:0": {
                "output": false,
                "source": "cloud"
            }
        }
    }
}
```

---

## 💻 Uso en el Sistema

### 1. En el Controlador de Acceso

**Ubicación:** `app/controllers/AccessController.php`

#### Abrir Barrera al Registrar Entrada
```php
public function create() {
    // ... validación de datos ...
    
    $accessId = $this->accessModel->create($_POST);
    
    // Abrir barrera automáticamente
    $shellyResult = ShellyAPI::openBarrier();
    
    if (!$shellyResult['success']) {
        $this->setFlash('warning', 
            'Acceso registrado pero no se pudo abrir la barrera automáticamente.');
    } else {
        $this->setFlash('success', 
            'Acceso registrado y barrera abierta exitosamente.');
    }
    
    $this->redirect('/access/view/' . $accessId);
}
```

#### Cerrar Barrera al Registrar Salida
```php
public function registerExit($id) {
    // ... validación de datos ...
    
    $this->accessModel->registerExit($id, $_POST['liters_supplied']);
    
    // Cerrar barrera automáticamente
    $shellyResult = ShellyAPI::closeBarrier();
    
    if (!$shellyResult['success']) {
        $this->setFlash('warning', 
            'Salida registrada pero no se pudo cerrar la barrera automáticamente.');
    } else {
        $this->setFlash('success', 
            'Salida registrada y barrera cerrada exitosamente.');
    }
    
    $this->redirect('/access/view/' . $id);
}
```

### 2. Control Manual (API Endpoints)

#### Endpoint para Abrir
```
GET /access/openBarrier
```

**Controlador:**
```php
public function openBarrier() {
    Auth::requireRole(['admin', 'supervisor', 'operator']);
    
    $result = ShellyAPI::openBarrier();
    
    if ($result['success']) {
        $this->json([
            'success' => true, 
            'message' => 'Barrera abierta exitosamente.'
        ]);
    } else {
        $this->json([
            'success' => false, 
            'message' => 'Error al abrir la barrera.'
        ], 500);
    }
}
```

**JavaScript (Frontend):**
```javascript
async function openBarrier() {
    try {
        const response = await fetch('/access/openBarrier');
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error de conexión: ' + error.message);
    }
}
```

---

## 🔍 Manejo de Errores

### Tipos de Errores

1. **Error de Conexión**
   - Dispositivo no responde
   - IP incorrecta
   - Red desconectada

2. **Error HTTP**
   - Código de respuesta diferente de 200
   - Timeout de conexión

3. **Error de Comando**
   - Relay no disponible
   - Canal incorrecto

### Logging de Errores

Los errores se registran automáticamente en:
```
logs/error.log
```

**Ejemplo de log:**
```
[2024-10-28 03:11:19] Shelly API Error: Connection timeout
[2024-10-28 03:11:20] Shelly API HTTP Error: 503
```

### Manejo en el Código

```php
try {
    $result = ShellyAPI::openBarrier();
    
    if (!$result['success']) {
        // Registrar error
        error_log("Shelly Error: " . $result['error']);
        
        // Notificar al usuario
        $this->setFlash('warning', 
            'La barrera debe abrirse manualmente. Error: ' . $result['error']);
        
        // Continuar con el proceso
        // No bloquear el registro de acceso
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
}
```

---

## 🛠️ Troubleshooting

### Problema: Dispositivo no responde

**Verificaciones:**
1. Verificar conectividad del dispositivo:
   - Asegúrese de que el dispositivo esté encendido
   - Verifique que el dispositivo tenga conexión WiFi
   - Confirme que el dispositivo esté conectado al Shelly Cloud

2. Verificar credenciales:
   - Token de autenticación correcto
   - Device ID correcto
   - Servidor Cloud correcto

3. Verificar estado en la aplicación Shelly Cloud:
   - Abra la app Shelly Cloud
   - Verifique que el dispositivo aparezca como "online"

4. Verificar conectividad del servidor:
   - El servidor debe poder acceder a Internet
   - Verificar que no haya firewall bloqueando conexiones HTTPS salientes

### Problema: Relay no cambia de estado

**Verificaciones:**
1. Probar comando directo desde curl:
   ```bash
   curl -X POST "https://shelly-208-eu.shelly.cloud/device/relay/control" \
        -d "auth_key=YOUR_TOKEN" \
        -d "id=YOUR_DEVICE_ID" \
        -d "channel=0" \
        -d "turn=on"
   ```

2. Verificar cableado del relay

3. Revisar estado del dispositivo en la app Shelly Cloud

4. Verificar que el canal sea correcto (0-3)

5. Confirmar que el dispositivo tenga firmware actualizado

### Problema: Timeout constante

**Soluciones:**
1. Aumentar timeout en config:
   ```php
   define('SHELLY_API_TIMEOUT', 10); // Aumentar a 10 segundos
   ```

2. Verificar congestión de red

3. Reiniciar dispositivo Shelly

---

## 📱 Mejores Prácticas

### 1. Validación Previa
```php
// Verificar conectividad antes de operaciones críticas
$status = ShellyAPI::getStatus();
if (!$status['success']) {
    // Usar modo manual
    $this->setFlash('warning', 'Sistema en modo manual');
}
```

### 2. Modo de Respaldo
```php
// Siempre tener opción manual
if (!$shellyResult['success']) {
    // Mostrar botón de control manual
    $manualControlRequired = true;
}
```

### 3. Logging Detallado
```php
// Registrar todas las operaciones importantes
error_log(sprintf(
    "[Shelly] Operación: %s, Resultado: %s, IP: %s",
    $operation,
    $result['success'] ? 'OK' : 'FAIL',
    SHELLY_API_URL
));
```

### 4. Notificaciones Claras
```php
// Mensajes específicos para el usuario
if ($result['success']) {
    $this->setFlash('success', '✓ Barrera operada correctamente');
} else {
    $this->setFlash('warning', 
        '⚠ Por favor, opere la barrera manualmente. ' .
        'Sistema automático temporalmente no disponible.'
    );
}
```

---

## 🔐 Seguridad

### 1. Autenticación de Endpoints
```php
// Siempre verificar permisos
Auth::requireRole(['admin', 'supervisor', 'operator']);
```

### 2. Validación de Parámetros
```php
// Validar canal del relay
if ($channel < 0 || $channel > 3) {
    throw new Exception("Canal inválido");
}
```

### 3. Rate Limiting
```php
// Evitar spam de comandos
if ($lastCommand && (time() - $lastCommand) < 2) {
    throw new Exception("Por favor, espere entre comandos");
}
```

---

## 📚 Referencias

- **Documentación Shelly Cloud API:** https://support.shelly.cloud/en/support/solutions/articles/103000222504-what-is-shelly-cloud-api-
- **Documentación Técnica Shelly:** https://shelly-api-docs.shelly.cloud/
- **Shelly Pro 4PM:** https://www.shelly.cloud/en/products/shop/shelly-pro-4-pm
- **Cloud Control API:** https://shelly-api-docs.shelly.cloud/cloud-control-api/

---

**Versión:** 1.0.0  
**Última actualización:** Octubre 2024  
**Sistema:** DUNAS - Control de Acceso con IoT
