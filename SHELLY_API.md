# API de Integración Shelly Relay - Sistema DUNAS

## 📡 Descripción General

El sistema DUNAS se integra con dispositivos Shelly Pro 4PM para control automatizado de barreras vehiculares mediante comunicación HTTP REST.

## 🔧 Configuración

### Archivo de Configuración
**Ubicación:** `config/config.php`

```php
// Configuración de Shelly Relay API
define('SHELLY_API_URL', 'http://192.168.1.100'); // IP del dispositivo Shelly
define('SHELLY_API_TIMEOUT', 5);                   // Timeout en segundos
define('SHELLY_RELAY_OPEN', 0);                    // Canal para abrir barrera
define('SHELLY_RELAY_CLOSE', 1);                   // Canal para cerrar barrera
```

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
1. Activa el relay configurado en `SHELLY_RELAY_OPEN`
2. Espera 2 segundos
3. Desactiva el relay automáticamente
4. Registra errores en logs

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
1. Activa el relay configurado en `SHELLY_RELAY_CLOSE`
2. Espera 2 segundos
3. Desactiva el relay automáticamente
4. Registra errores en logs

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
        // Información completa del estado del dispositivo
        'wifi_sta' => [...],
        'cloud' => [...],
        'mqtt' => [...],
        'relays' => [...],
        // ... más datos
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

## 🔌 Endpoints del Dispositivo Shelly

### Estructura de URLs

**Base URL:** `http://{IP_DEL_DISPOSITIVO}`

### Control de Relays

#### Encender Relay
```
GET /relay/{channel}?turn=on
```

**Parámetros:**
- `channel`: Número del canal (0-3)
- `turn`: `on` para encender

**Ejemplo:**
```bash
curl "http://192.168.1.100/relay/0?turn=on"
```

#### Apagar Relay
```
GET /relay/{channel}?turn=off
```

**Ejemplo:**
```bash
curl "http://192.168.1.100/relay/0?turn=off"
```

#### Toggle Relay
```
GET /relay/{channel}?turn=toggle
```

**Ejemplo:**
```bash
curl "http://192.168.1.100/relay/0?turn=toggle"
```

#### Encender con Timer
```
GET /relay/{channel}?turn=on&timer={seconds}
```

**Ejemplo (encender por 5 segundos):**
```bash
curl "http://192.168.1.100/relay/0?turn=on&timer=5"
```

### Consulta de Estado

#### Estado General
```
GET /status
```

**Respuesta (JSON):**
```json
{
    "wifi_sta": {
        "connected": true,
        "ssid": "MyWiFi",
        "ip": "192.168.1.100"
    },
    "relays": [
        {
            "ison": false,
            "has_timer": false,
            "overpower": false
        }
    ]
}
```

#### Estado de Relay Específico
```
GET /relay/{channel}
```

**Respuesta (JSON):**
```json
{
    "ison": false,
    "has_timer": false,
    "timer_remaining": 0,
    "overpower": false,
    "is_valid": true
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
1. Ping al dispositivo:
   ```bash
   ping 192.168.1.100
   ```

2. Verificar en navegador:
   ```
   http://192.168.1.100/status
   ```

3. Revisar configuración WiFi del Shelly

4. Verificar firewall del servidor

### Problema: Relay no cambia de estado

**Verificaciones:**
1. Probar comando directo:
   ```bash
   curl "http://192.168.1.100/relay/0?turn=on"
   ```

2. Verificar cableado del relay

3. Revisar logs del dispositivo Shelly

4. Verificar que el canal sea correcto (0-3)

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

- **Documentación Shelly:** https://shelly-api-docs.shelly.cloud/
- **Shelly Pro 4PM:** https://www.shelly.cloud/en/products/shop/shelly-pro-4-pm
- **API REST Shelly:** https://shelly-api-docs.shelly.cloud/gen1/#shelly-family-overview

---

**Versión:** 1.0.0  
**Última actualización:** Octubre 2024  
**Sistema:** DUNAS - Control de Acceso con IoT
