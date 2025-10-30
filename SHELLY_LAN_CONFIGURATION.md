# Configuración Shelly por LAN - Sistema DUNAS

## 🎯 Objetivo

Este documento describe cómo configurar el sistema DUNAS para usar el dispositivo Shelly por red local (LAN) en lugar de port forwarding externo.

## 📋 Cambios Implementados

### 1. Construcción Dinámica de URLs

El sistema ahora construye automáticamente las URLs de control del Shelly basándose en:

- **`shelly_api_url`**: URL base del dispositivo (ej: `http://192.168.1.95`)
- **`shelly_relay_open`**: Canal del relay para abrir (default: 0)
- **`shelly_relay_close`**: Canal del relay para cerrar (default: 1)

**Ejemplo de construcción automática:**
```
shelly_api_url = "http://192.168.1.95"
shelly_relay_open = 0
shelly_relay_close = 1

Resultado:
- open_url = "http://192.168.1.95/rpc/Switch.Set?id=0&on=false"
- close_url = "http://192.168.1.95/rpc/Switch.Set?id=1&on=true"
```

### 2. Soporte para Basic Auth

Si el Shelly requiere autenticación, puedes configurar:

- **`shelly_username`**: Usuario de autenticación
- **`shelly_password`**: Contraseña de autenticación

El sistema automáticamente usará Basic Auth en las peticiones HTTP.

### 3. Prioridad de Configuración

El sistema usa la siguiente jerarquía de configuración:

1. **URLs completas en BD** (`shelly_open_url`, `shelly_close_url`) - Máxima prioridad
2. **Construcción dinámica** desde `shelly_api_url` + canales
3. **Constantes en `config.php`** - Fallback final

## 🔧 Configuración

### Opción A: Configuración Rápida (config.php)

Para una validación rápida, editar `/config/config.php`:

```php
// Cambiar estas 3 líneas:
define('SHELLY_API_URL', 'http://192.168.1.95/');
define('SHELLY_OPEN_URL', 'http://192.168.1.95/rpc/Switch.Set?id=0&on=false');
define('SHELLY_CLOSE_URL', 'http://192.168.1.95/rpc/Switch.Set?id=0&on=true');
```

⚠️ **Nota**: Si el cableado es inverso, intercambiar `on=false` y `on=true`.

### Opción B: Configuración por Base de Datos (Recomendado)

Insertar o actualizar en la tabla `settings`:

```sql
-- Configuración básica (construcción automática)
INSERT INTO settings (setting_key, setting_value) VALUES
('shelly_api_url', 'http://192.168.1.95'),
('shelly_relay_open', '0'),
('shelly_relay_close', '1')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Opcional: URLs completas personalizadas
INSERT INTO settings (setting_key, setting_value) VALUES
('shelly_open_url', 'http://192.168.1.95/rpc/Switch.Set?id=0&on=false'),
('shelly_close_url', 'http://192.168.1.95/rpc/Switch.Set?id=1&on=true')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Opcional: Credenciales de autenticación
INSERT INTO settings (setting_key, setting_value) VALUES
('shelly_username', 'admin'),
('shelly_password', 'tu_password_aqui')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
```

### Opción C: Panel de Administración (Próximamente)

Ir a **Configuraciones del Sistema** en el panel web y establecer:

- `shelly_api_url` = `http://192.168.1.95`
- `shelly_relay_open` = `0`
- `shelly_relay_close` = `1`
- *(Opcional)* `shelly_username` / `shelly_password`

## 🧪 Pruebas

### 1. Test de Construcción de URLs

Verificar que las URLs se construyen correctamente:

```bash
php public/test-url-construction.php
```

**Resultado esperado:** ✓ Todos los tests pasaron: 8/8

### 2. Test de Configuración

Verificar la configuración activa del sistema:

```
http://tu-dominio.com/test-config.php
```

**Verificaciones:**
- Las URLs de `open_url` y `close_url` deben contener `192.168.1.95`
- Verificar que aparece "✓ open_url contiene IP local 192.168.1.95"
- Verificar que aparece "✓ close_url contiene IP local 192.168.1.95"

### 3. Test de Relay Local

⚠️ **Importante**: Este test solo funciona desde una máquina en la misma red local que el Shelly.

**Abrir barrera (relay OFF):**
```
http://tu-dominio.com/test-local-relay.php?action=off
```

**Cerrar barrera (relay ON):**
```
http://tu-dominio.com/test-local-relay.php?action=on
```

**Con credenciales:**
```
http://tu-dominio.com/test-local-relay.php?action=off&username=admin&password=tu_password
```

**Resultado esperado:**
- ✅ Comando ejecutado exitosamente
- HTTP 200
- Respuesta JSON del Shelly

## 🔍 Troubleshooting

### Problema: "Connection timeout" o "Connection refused"

**Causa**: El servidor web no puede acceder al Shelly en la red local.

**Soluciones**:
1. Verificar que el servidor y el Shelly estén en la misma red
2. Hacer ping desde el servidor: `ping 192.168.1.95`
3. Verificar firewall del servidor
4. Si el servidor está en la nube, usar port forwarding o VPN

### Problema: HTTP 401 Unauthorized

**Causa**: El Shelly requiere autenticación.

**Solución**: Configurar `shelly_username` y `shelly_password` en la base de datos.

### Problema: Las URLs no se construyen correctamente

**Verificaciones**:
1. Ejecutar `test-config.php` para ver la configuración activa
2. Verificar que `shelly_api_url` esté en la BD
3. Limpiar caché estática de `ShellyAPI::getSettings()` reiniciando PHP-FPM

### Problema: El relay no responde

**Verificaciones**:
1. Verificar cableado del Shelly
2. Probar comando directo: `curl "http://192.168.1.95/rpc/Switch.Set?id=0&on=false"`
3. Verificar logs del sistema: `/var/log/php-errors.log`
4. Verificar que los canales (relay_open/relay_close) sean correctos

## 📊 Escenarios de Uso

### Escenario 1: Construcción Automática (Recomendado)

```sql
-- Solo configurar la URL base y los canales
INSERT INTO settings (setting_key, setting_value) VALUES
('shelly_api_url', 'http://192.168.1.95'),
('shelly_relay_open', '0'),
('shelly_relay_close', '1')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
```

✅ **Ventajas:**
- Configuración mínima
- Fácil de mantener
- Permite cambiar canales sin tocar código

### Escenario 2: URLs Completas Personalizadas

```sql
-- Forzar URLs específicas
INSERT INTO settings (setting_key, setting_value) VALUES
('shelly_open_url', 'http://192.168.1.95/relay/0?turn=off'),
('shelly_close_url', 'http://192.168.1.95/relay/1?turn=on')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
```

✅ **Ventajas:**
- Control total sobre las URLs
- Útil para APIs personalizadas o Shelly con firmware modificado

### Escenario 3: Mismo Canal con Timer

```sql
-- Usar el mismo canal con timer (pulso)
INSERT INTO settings (setting_key, setting_value) VALUES
('shelly_api_url', 'http://192.168.1.95'),
('shelly_open_url', 'http://192.168.1.95/rpc/Switch.Set?id=0&on=true&timer=2'),
('shelly_close_url', 'http://192.168.1.95/rpc/Switch.Set?id=0&on=true&timer=2')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
```

✅ **Ventajas:**
- Un solo canal para ambas acciones
- Timer automático (pulso de 2 segundos)
- Ideal para barreras con un solo botón

## 📝 Notas Importantes

1. **Cableado**: La convención por defecto es:
   - Abrir = `on=false` (OFF)
   - Cerrar = `on=true` (ON)
   
   Si tu cableado es inverso, intercambia estos valores.

2. **Canales**: Los Shelly Pro 4PM tienen 4 canales (0-3). Asegúrate de usar los canales correctos.

3. **Red Local**: Esta configuración solo funciona si el servidor puede acceder a la red local donde está el Shelly.

4. **Compatibilidad**: Los cambios son retrocompatibles. Si no hay configuración en BD, se usarán las constantes de `config.php`.

5. **Seguridad**: Si usas Basic Auth, asegúrate de que la conexión sea segura (HTTPS o red interna confiable).

## 🔗 Referencias

- [Documentación Shelly API](SHELLY_API.md)
- [Shelly RPC API Documentation](https://shelly-api-docs.shelly.cloud/gen2/ComponentsAndServices/Switch)
- [Test Scripts](public/test-*.php)

---

**Versión:** 1.0.0  
**Fecha:** Octubre 2024  
**Sistema:** DUNAS - Control de Acceso con IoT
