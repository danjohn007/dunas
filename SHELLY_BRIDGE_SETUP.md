# Configuración de Bridge/Relay para Shelly - Sistema DUNAS

## 📋 Descripción del Problema

Si el sistema muestra errores de timeout al intentar abrir o cerrar la barrera:
```
Acceso registrado pero no se pudo abrir la barrera automáticamente. 
Error: Connection timed out after 5002 milliseconds (URL: http://192.168.1.159/relay/0?turn=on)
```

Esto ocurre cuando el servidor web no puede acceder directamente al dispositivo Shelly porque:
- El servidor está en una red diferente
- El servidor está alojado en la nube
- Hay restricciones de firewall
- El dispositivo Shelly está en una red local privada

## ✅ Solución: Bridge/Relay Público

Utilizar un servicio bridge que actúe como intermediario entre el servidor web y el dispositivo Shelly local.

---

## 🔧 Opción 1: Webhook.site + Script Local (Recomendado para pruebas)

### Paso 1: Configurar Webhook.site

1. Visita https://webhook.site
2. Copia tu URL única (ejemplo: `https://webhook.site/12345678-1234-1234-1234-123456789012`)
3. Esta URL recibirá las peticiones del servidor

### Paso 2: Script Local Python

Crea un archivo `shelly_bridge.py` en tu computadora local (misma red que Shelly):

```python
#!/usr/bin/env python3
"""
Bridge Script para Shelly Relay - Sistema DUNAS
Versión: 1.0
"""

import requests
import time
import json
from datetime import datetime

# Configuración
WEBHOOK_URL = "https://webhook.site/YOUR-UNIQUE-ID"  # Tu URL de webhook.site
SHELLY_IP = "192.168.1.159"  # IP de tu dispositivo Shelly
CHECK_INTERVAL = 2  # Segundos entre cada verificación
LAST_PROCESSED_FILE = "last_processed.txt"

def log(message):
    """Registra mensaje con timestamp"""
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{timestamp}] {message}")

def get_last_processed_id():
    """Obtiene el ID del último request procesado"""
    try:
        with open(LAST_PROCESSED_FILE, 'r') as f:
            return f.read().strip()
    except FileNotFoundError:
        return None

def save_last_processed_id(request_id):
    """Guarda el ID del último request procesado"""
    with open(LAST_PROCESSED_FILE, 'w') as f:
        f.write(request_id)

def process_webhook_requests():
    """Verifica y procesa nuevos requests del webhook"""
    try:
        # Obtener requests del webhook
        response = requests.get(f"{WEBHOOK_URL}/requests", timeout=10)
        
        if response.status_code != 200:
            log(f"Error al obtener requests: HTTP {response.status_code}")
            return
        
        data = response.json()
        
        if not data.get('data'):
            return
        
        # Obtener el último request procesado
        last_id = get_last_processed_id()
        
        # Procesar solo el request más reciente si es nuevo
        latest = data['data'][0]
        request_id = latest['uuid']
        
        if request_id == last_id:
            return  # Ya fue procesado
        
        # Extraer comando del query string o body
        query = latest.get('query', {})
        action = query.get('action', [''])[0]
        relay = query.get('relay', ['0'])[0]
        
        if not action:
            log(f"Request sin acción válida: {request_id}")
            return
        
        # Ejecutar comando en Shelly
        if action == 'open':
            shelly_url = f"http://{SHELLY_IP}/relay/{relay}?turn=on"
            log(f"Abriendo barrera (relay {relay})...")
        elif action == 'close':
            shelly_url = f"http://{SHELLY_IP}/relay/{relay}?turn=on"
            log(f"Cerrando barrera (relay {relay})...")
        else:
            log(f"Acción desconocida: {action}")
            return
        
        try:
            # Enviar comando al Shelly
            shelly_response = requests.get(shelly_url, timeout=5)
            
            if shelly_response.status_code == 200:
                log(f"✓ Comando ejecutado exitosamente")
                # Apagar relay después de 2 segundos
                time.sleep(2)
                off_url = f"http://{SHELLY_IP}/relay/{relay}?turn=off"
                requests.get(off_url, timeout=5)
                log(f"✓ Relay apagado")
            else:
                log(f"✗ Error del Shelly: HTTP {shelly_response.status_code}")
        
        except requests.exceptions.RequestException as e:
            log(f"✗ Error al comunicarse con Shelly: {e}")
        
        # Marcar como procesado
        save_last_processed_id(request_id)
    
    except requests.exceptions.RequestException as e:
        log(f"Error al verificar webhook: {e}")
    except Exception as e:
        log(f"Error inesperado: {e}")

def main():
    """Función principal"""
    log("Iniciando Shelly Bridge...")
    log(f"Webhook URL: {WEBHOOK_URL}")
    log(f"Shelly IP: {SHELLY_IP}")
    log(f"Intervalo de verificación: {CHECK_INTERVAL}s")
    log("Presiona Ctrl+C para detener")
    print("-" * 60)
    
    try:
        while True:
            process_webhook_requests()
            time.sleep(CHECK_INTERVAL)
    
    except KeyboardInterrupt:
        log("Bridge detenido por el usuario")
    except Exception as e:
        log(f"Error fatal: {e}")

if __name__ == "__main__":
    main()
```

### Paso 3: Ejecutar el Script

```bash
# Instalar dependencias
pip install requests

# Ejecutar el script
python shelly_bridge.py
```

El script se ejecutará continuamente verificando nuevos comandos cada 2 segundos.

### Paso 4: Configurar DUNAS

En el archivo `config/config.php`, configura:

```php
// En lugar de la IP local, usa tu webhook URL
define('SHELLY_API_URL', 'https://webhook.site/YOUR-UNIQUE-ID');
define('SHELLY_RELAY_OPEN', 0);
define('SHELLY_RELAY_CLOSE', 1);
```

Y modifica `app/helpers/ShellyAPI.php`:

```php
private static function makeRequest($endpoint, $method = 'GET', $data = null) {
    $settings = self::getSettings();
    $baseUrl = $settings['api_url'];
    
    // Convertir endpoint de Shelly a parámetros para el bridge
    // Ejemplo: /relay/0?turn=on -> ?action=open&relay=0
    if (preg_match('/\/relay\/(\d+)\?turn=(on|off)/', $endpoint, $matches)) {
        $relay = $matches[1];
        $turn = $matches[2];
        
        // Determinar acción basada en el relay y turn
        if ($turn === 'on') {
            if ($relay == $settings['relay_open']) {
                $action = 'open';
            } else {
                $action = 'close';
            }
        }
        
        $url = $baseUrl . '?action=' . $action . '&relay=' . $relay;
    } else {
        $url = $baseUrl . $endpoint;
    }
    
    // Resto del código curl...
}
```

---

## 🔧 Opción 2: Node.js Bridge Server (Producción)

### Script Node.js: `shelly-bridge-server.js`

```javascript
/**
 * Shelly Bridge Server - Sistema DUNAS
 * Servidor intermediario para controlar Shelly desde internet
 */

const express = require('express');
const axios = require('axios');
const app = express();

// Configuración
const PORT = 3000;
const SHELLY_IP = '192.168.1.159';
const API_KEY = 'YOUR-SECRET-API-KEY'; // Cambiar por una clave segura

// Middleware para validar API key
function validateApiKey(req, res, next) {
    const apiKey = req.headers['x-api-key'] || req.query.api_key;
    
    if (apiKey !== API_KEY) {
        return res.status(401).json({ 
            success: false, 
            error: 'API Key inválida' 
        });
    }
    
    next();
}

// Endpoint para abrir barrera
app.get('/api/open', validateApiKey, async (req, res) => {
    const relay = req.query.relay || '0';
    
    try {
        console.log(`[${new Date().toISOString()}] Abriendo barrera (relay ${relay})`);
        
        // Encender relay
        await axios.get(`http://${SHELLY_IP}/relay/${relay}?turn=on`, { 
            timeout: 5000 
        });
        
        // Esperar 2 segundos
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Apagar relay
        await axios.get(`http://${SHELLY_IP}/relay/${relay}?turn=off`, { 
            timeout: 5000 
        });
        
        console.log(`[${new Date().toISOString()}] ✓ Barrera abierta exitosamente`);
        
        res.json({ 
            success: true, 
            message: 'Barrera abierta exitosamente',
            relay: relay
        });
    } catch (error) {
        console.error(`[${new Date().toISOString()}] ✗ Error:`, error.message);
        
        res.status(500).json({ 
            success: false, 
            error: error.message 
        });
    }
});

// Endpoint para cerrar barrera
app.get('/api/close', validateApiKey, async (req, res) => {
    const relay = req.query.relay || '1';
    
    try {
        console.log(`[${new Date().toISOString()}] Cerrando barrera (relay ${relay})`);
        
        // Encender relay
        await axios.get(`http://${SHELLY_IP}/relay/${relay}?turn=on`, { 
            timeout: 5000 
        });
        
        // Esperar 2 segundos
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Apagar relay
        await axios.get(`http://${SHELLY_IP}/relay/${relay}?turn=off`, { 
            timeout: 5000 
        });
        
        console.log(`[${new Date().toISOString()}] ✓ Barrera cerrada exitosamente`);
        
        res.json({ 
            success: true, 
            message: 'Barrera cerrada exitosamente',
            relay: relay
        });
    } catch (error) {
        console.error(`[${new Date().toISOString()}] ✗ Error:`, error.message);
        
        res.status(500).json({ 
            success: false, 
            error: error.message 
        });
    }
});

// Endpoint de estado
app.get('/api/status', validateApiKey, async (req, res) => {
    try {
        const response = await axios.get(`http://${SHELLY_IP}/status`, { 
            timeout: 5000 
        });
        
        res.json({ 
            success: true, 
            data: response.data 
        });
    } catch (error) {
        res.status(500).json({ 
            success: false, 
            error: error.message 
        });
    }
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log('='.repeat(60));
    console.log('Shelly Bridge Server - Sistema DUNAS');
    console.log('='.repeat(60));
    console.log(`Servidor iniciado en puerto ${PORT}`);
    console.log(`Shelly IP: ${SHELLY_IP}`);
    console.log(`Endpoints disponibles:`);
    console.log(`  - GET /api/open?relay=0&api_key=${API_KEY}`);
    console.log(`  - GET /api/close?relay=1&api_key=${API_KEY}`);
    console.log(`  - GET /api/status?api_key=${API_KEY}`);
    console.log('='.repeat(60));
});
```

### Instalación y Ejecución

```bash
# Instalar Node.js si no está instalado
# https://nodejs.org/

# Crear directorio del proyecto
mkdir shelly-bridge
cd shelly-bridge

# Inicializar proyecto
npm init -y

# Instalar dependencias
npm install express axios

# Copiar el script anterior como shelly-bridge-server.js

# Ejecutar
node shelly-bridge-server.js
```

### Ejecutar como Servicio (Linux con systemd)

Crear archivo `/etc/systemd/system/shelly-bridge.service`:

```ini
[Unit]
Description=Shelly Bridge Server for DUNAS
After=network.target

[Service]
Type=simple
User=pi
WorkingDirectory=/home/pi/shelly-bridge
ExecStart=/usr/bin/node /home/pi/shelly-bridge/shelly-bridge-server.js
Restart=on-failure
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Habilitar e iniciar el servicio:

```bash
sudo systemctl daemon-reload
sudo systemctl enable shelly-bridge
sudo systemctl start shelly-bridge
sudo systemctl status shelly-bridge
```

### Exponer a Internet (ngrok)

Si el bridge está en tu red local y necesitas exponerlo:

```bash
# Instalar ngrok
# https://ngrok.com/download

# Exponer puerto 3000
ngrok http 3000
```

Ngrok te dará una URL pública (ejemplo: `https://abc123.ngrok.io`)

### Configurar DUNAS para usar el Bridge

Modifica `app/helpers/ShellyAPI.php`:

```php
<?php
class ShellyAPI {
    
    const TIMEOUT_EXTENDED = 15;
    const CONNECT_TIMEOUT = 10;
    const MAX_RETRIES = 2;
    
    private static function getSettings() {
        static $settings = null;
        if ($settings === null) {
            $settingsModel = new Settings();
            $allSettings = $settingsModel->getAll();
            
            $settings = [
                'api_url' => $allSettings['shelly_api_url'] ?? SHELLY_API_URL,
                'api_key' => $allSettings['shelly_api_key'] ?? '',
                'relay_open' => $allSettings['shelly_relay_open'] ?? SHELLY_RELAY_OPEN,
                'relay_close' => $allSettings['shelly_relay_close'] ?? SHELLY_RELAY_CLOSE,
            ];
        }
        return $settings;
    }
    
    private static function makeRequest($endpoint, $method = 'GET') {
        $settings = self::getSettings();
        $url = rtrim($settings['api_url'], '/') . $endpoint;
        
        // Agregar API key si existe
        if (!empty($settings['api_key'])) {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . 'api_key=' . urlencode($settings['api_key']);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_EXTENDED);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($error) {
            error_log("Shelly API Error: " . $error);
            return ['success' => false, 'error' => $error];
        }
        
        if ($httpCode !== 200) {
            error_log("Shelly API HTTP Error: " . $httpCode);
            return ['success' => false, 'error' => 'HTTP ' . $httpCode];
        }
        
        $decoded = json_decode($response, true);
        return ['success' => true, 'data' => $decoded];
    }
    
    public static function openBarrier() {
        $settings = self::getSettings();
        
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            $result = self::makeRequest('/api/open?relay=' . $settings['relay_open']);
            
            if ($result['success']) {
                break;
            }
            
            if ($attempt < self::MAX_RETRIES) {
                usleep(500000);
            }
        }
        
        return $result;
    }
    
    public static function closeBarrier() {
        $settings = self::getSettings();
        
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            $result = self::makeRequest('/api/close?relay=' . $settings['relay_close']);
            
            if ($result['success']) {
                break;
            }
            
            if ($attempt < self::MAX_RETRIES) {
                usleep(500000);
            }
        }
        
        return $result;
    }
    
    public static function getStatus() {
        return self::makeRequest('/api/status');
    }
}
```

En `config/config.php`:

```php
// URL del bridge (puede ser ngrok, tu servidor público, etc.)
define('SHELLY_API_URL', 'https://abc123.ngrok.io');
define('SHELLY_API_KEY', 'YOUR-SECRET-API-KEY'); // Opcional pero recomendado
define('SHELLY_RELAY_OPEN', 0);
define('SHELLY_RELAY_CLOSE', 1);
```

Agregar en la tabla `settings` de la base de datos:

```sql
INSERT INTO settings (setting_key, setting_value) VALUES
('shelly_api_key', 'YOUR-SECRET-API-KEY')
ON DUPLICATE KEY UPDATE setting_value = 'YOUR-SECRET-API-KEY';
```

---

## 🔧 Opción 3: Shelly Cloud (Más Simple)

Si tu Shelly está conectado a Shelly Cloud, puedes usar su API:

### 1. Obtener Server URI y Auth Key

1. Abre la app Shelly
2. Ve a Settings → User Settings
3. Copia "Server URI" y "Authorization Cloud Key"

### 2. Configurar DUNAS

```php
// config/config.php
define('SHELLY_CLOUD_SERVER', 'https://shelly-xx-eu.shelly.cloud'); // Tu server URI
define('SHELLY_CLOUD_AUTH_KEY', 'YOUR_AUTH_KEY');
define('SHELLY_DEVICE_ID', 'shellypro4pm-XXXX'); // ID de tu dispositivo
```

### 3. Actualizar ShellyAPI.php

```php
private static function makeCloudRequest($channel, $turn) {
    $url = SHELLY_CLOUD_SERVER . '/device/relay/control';
    
    $data = [
        'channel' => $channel,
        'turn' => $turn,
        'id' => SHELLY_DEVICE_ID
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . SHELLY_CLOUD_AUTH_KEY
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'HTTP ' . $httpCode];
}
```

---

## 📊 Comparación de Opciones

| Opción | Complejidad | Costo | Ventajas | Desventajas |
|--------|-------------|-------|----------|-------------|
| Webhook.site + Script | Baja | Gratis | Fácil de probar | No para producción |
| Node.js Bridge | Media | Gratis | Control total, producción | Requiere servidor |
| Shelly Cloud | Baja | Gratis | Oficial, sin setup | Dependencia de servicio externo |

---

## 🔍 Troubleshooting

### Bridge no responde

1. Verificar que el script/servidor esté ejecutándose
2. Verificar logs del bridge
3. Probar conectividad: `curl https://your-bridge.com/api/status?api_key=XXX`

### Shelly no responde desde bridge

1. Verificar que el bridge esté en la misma red que Shelly
2. Hacer ping desde el servidor del bridge: `ping 192.168.1.159`
3. Probar comando directo: `curl http://192.168.1.159/relay/0?turn=on`

### Timeout del sistema DUNAS

1. Aumentar timeouts en ShellyAPI.php
2. Verificar conectividad del servidor web al bridge
3. Revisar logs: `/home/runner/work/dunas/dunas/logs/error.log`

---

## 📝 Configuración Recomendada para Producción

1. **Usar Node.js Bridge Server** en servidor local mismo lugar que Shelly
2. **Exponer con ngrok o servidor con IP pública**
3. **Configurar API Key** para seguridad
4. **Configurar como servicio systemd** para auto-inicio
5. **Monitorear logs** del bridge

---

## ✅ Verificación Final

Una vez configurado, verifica:

```bash
# Probar apertura
curl "https://your-bridge.com/api/open?relay=0&api_key=YOUR_KEY"

# Probar cierre
curl "https://your-bridge.com/api/close?relay=1&api_key=YOUR_KEY"

# Ver estado
curl "https://your-bridge.com/api/status?api_key=YOUR_KEY"
```

Si estos comandos funcionan, DUNAS podrá controlar la barrera sin problemas.

---

**Versión:** 1.0  
**Fecha:** Octubre 2024  
**Sistema:** DUNAS - Control de Acceso con IoT
