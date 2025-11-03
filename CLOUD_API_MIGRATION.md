# Migración a Shelly Cloud API

## 📋 Resumen

Este documento describe la migración del sistema de control de barreras de conexión IP local a Shelly Cloud API.

## 🔄 Cambios Principales

### Antes (Conexión IP Local)
- **Método**: Conexión directa HTTP a IP local del dispositivo
- **URL**: `http://192.168.1.95/rpc/Switch.Set?id=0&on=false`
- **Autenticación**: HTTP Basic Auth (usuario/contraseña)
- **Limitación**: Requiere que el servidor esté en la misma red que el dispositivo
- **Problemas**: No funciona con servidor remoto, requiere port forwarding

### Después (Cloud API)
- **Método**: Conexión HTTPS al Shelly Cloud
- **URL**: `https://shelly-208-eu.shelly.cloud/device/relay/control`
- **Autenticación**: Auth Token
- **Ventaja**: Funciona desde cualquier ubicación con Internet
- **Sin limitaciones**: No requiere configuración de red local

## 🛠️ Archivos Modificados

### Archivos Core
1. **`config/config.php`**
   - Removidas: Configuraciones IP, usuario, contraseña
   - Agregadas: Auth Token, Device ID, Servidor Cloud

2. **`app/helpers/ShellyAPI.php`**
   - Reescrito completamente para usar Cloud API
   - Nueva función `makeCloudRequest()` con form-encoded data
   - Mantenido sistema de reintentos (3 intentos)

3. **`app/views/settings/index.php`**
   - Actualizado formulario de configuración
   - Campos para Auth Token, Device ID, Servidor Cloud

4. **`app/views/layouts/main.php`**
   - Removida referencia a `shelly-control.js`

### Archivos Eliminados (26 archivos)
- Todos los archivos de prueba IP-based: `test-*.php`
- Scripts de diagnóstico: `diagnose-*.php`
- Scripts de configuración: `setup-*.php`
- JavaScript IP-based: `public/js/shelly-control.js`

### Archivos de Documentación Actualizados
1. **`SHELLY_API.md`**
   - Reescrito para Cloud API
   - Nuevos ejemplos con curl
   - Actualizada referencia de endpoints

2. **`README.md`**
   - Sección de configuración Shelly actualizada
   - Instrucciones para obtener credenciales

3. **`INSTALLATION_GUIDE.md`**
   - Guía de configuración Cloud API
   - Pasos para obtener Auth Token y Device ID

4. **`FEATURES.md`**
   - Actualizada descripción de integración IoT
   - Nuevos detalles de Cloud API

### Archivos Nuevos (3 archivos)
1. **`public/test-cloud-api.php`**
   - Interfaz web para probar Cloud API
   - Botones para probar estado, abrir y cerrar

2. **`public/test-cloud-api-action.php`**
   - Backend para la interfaz de prueba
   - Ejecuta llamadas a ShellyAPI

3. **`CLOUD_API_MIGRATION.md`** (este archivo)
   - Documentación de la migración

## 📊 Estadísticas de Cambios
- **Archivos modificados**: 8
- **Archivos eliminados**: 26
- **Archivos nuevos**: 3
- **Líneas eliminadas**: ~5,400
- **Líneas agregadas**: ~500
- **Reducción neta**: ~4,900 líneas

## 🔐 Configuración Requerida

### Paso 1: Obtener Credenciales

1. **Auth Token**
   - Abrir aplicación Shelly Cloud
   - Ir a: Configuración → Usuario → Clave de autorización cloud
   - Copiar el token completo

2. **Device ID**
   - En la aplicación Shelly Cloud
   - Seleccionar el dispositivo
   - Ver detalles → Información del dispositivo
   - Copiar Device ID (ej: `34987A67DA6C`)

3. **Servidor Cloud**
   - Visible en la aplicación o en el portal web
   - Formato: `shelly-XXX-eu.shelly.cloud`
   - Ejemplo: `shelly-208-eu.shelly.cloud`

### Paso 2: Configurar el Sistema

Editar `config/config.php`:

```php
define('SHELLY_AUTH_TOKEN', 'TU_AUTH_TOKEN_AQUI');
define('SHELLY_DEVICE_ID', 'TU_DEVICE_ID_AQUI');
define('SHELLY_SERVER', 'shelly-XXX-eu.shelly.cloud');
define('SHELLY_SWITCH_ID', 0);  // Canal del switch
define('SHELLY_ENABLED', true);
```

### Paso 3: Probar la Configuración

1. **Desde el navegador:**
   ```
   http://tu-servidor/test-cloud-api.php
   ```

2. **Desde curl:**
   ```bash
   curl -X POST "https://shelly-208-eu.shelly.cloud/device/relay/control" \
        -d "auth_key=TU_AUTH_TOKEN" \
        -d "id=TU_DEVICE_ID" \
        -d "channel=0" \
        -d "turn=on"
   ```

## 🔍 Cómo Funciona

### Flujo de Apertura de Barrera

1. Usuario registra una entrada en el sistema
2. Sistema llama a `ShellyAPI::openBarrier()`
3. Se envía POST a Cloud API con:
   - `auth_key`: Token de autenticación
   - `id`: Device ID
   - `channel`: 0 (canal del switch)
   - `turn`: "off" (apagar switch = abrir barrera)
4. Cloud API transmite comando al dispositivo
5. Dispositivo ejecuta la acción
6. Cloud API retorna respuesta
7. Sistema registra resultado en logs

### Flujo de Cierre de Barrera

Igual que apertura, pero:
- `turn`: "on" (encender switch = cerrar barrera)

## 🐛 Solución de Problemas

### Error: "Could not resolve host"
**Causa**: Servidor no tiene acceso a Internet
**Solución**: Verificar conectividad y firewall

### Error: "HTTP 401" o "HTTP 403"
**Causa**: Auth Token inválido
**Solución**: Verificar que el token sea correcto y esté actualizado

### Error: "Device not found"
**Causa**: Device ID incorrecto
**Solución**: Verificar Device ID en la app Shelly Cloud

### Dispositivo no responde
**Causa**: Dispositivo offline o sin conexión
**Solución**: 
- Verificar que el dispositivo aparezca como "online" en Shelly Cloud
- Reiniciar el dispositivo si es necesario
- Verificar conexión WiFi del dispositivo

## 📝 Notas Importantes

1. **Latencia**: La comunicación vía Cloud puede tener latencia adicional (1-3 segundos)
2. **Conexión requerida**: El servidor debe tener acceso a Internet
3. **Puerto HTTPS**: El firewall debe permitir conexiones HTTPS salientes (puerto 443)
4. **Dispositivo online**: El Shelly debe estar conectado a Internet y activo en Cloud

## 🔄 Compatibilidad

- ✅ Compatible con Shelly Pro 4PM (Gen2)
- ✅ Compatible con cualquier dispositivo Gen2 con Cloud API
- ❌ No compatible con dispositivos Gen1 (requieren otra implementación)

## 📚 Referencias

- [Shelly Cloud API Docs](https://support.shelly.cloud/en/support/solutions/articles/103000222504-what-is-shelly-cloud-api-)
- [Shelly Technical Documentation](https://shelly-api-docs.shelly.cloud/)
- [Cloud Control API](https://shelly-api-docs.shelly.cloud/cloud-control-api/)

## ✅ Ventajas de la Migración

1. **Acceso remoto**: Control desde cualquier ubicación
2. **Sin configuración de red**: No requiere port forwarding ni IP estática
3. **Más seguro**: Usa HTTPS y autenticación por token
4. **Más simple**: Menos configuración de red
5. **Más confiable**: No depende de la red local

## ⚠️ Desventajas

1. **Latencia**: Ligero aumento en tiempo de respuesta
2. **Dependencia de Internet**: Requiere conexión estable
3. **Dependencia del servicio**: Requiere que Shelly Cloud esté operativo

---

**Fecha de migración**: Noviembre 2025  
**Versión del sistema**: 1.3.0+  
**Estado**: ✅ Completado
