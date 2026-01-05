# 🏠 Modo Local - Bridge Hikvision

## ✅ Implementación Completada

Se ha implementado el **Modo Local** para el Bridge Hikvision que permite hacer peticiones **client-side (JavaScript)** desde el navegador en la PC puente a `127.0.0.1:8080`.

---

## 🎯 ¿Qué se implementó?

### Antes (Modo Remoto):
```
Navegador → Sistema PHP → Bridge (IP pública) → Lector
                ↑
          Petición server-side (cURL)
```

### Ahora (Modo Local):
```
Navegador (en PC Puente) → Bridge (127.0.0.1) → Lector
          ↑
    Petición client-side (JavaScript fetch)
```

---

## ⚙️ Configuración

### Paso 1: Activar Modo Local

1. Accede al sistema como **administrador**
2. Ve a **Configuraciones del Sistema**
3. Busca la sección **"Lector HikVision (Control de Acceso)"**
4. Marca el checkbox **🏠 Modo Local (PC Puente)**
5. Aparecerá el campo **"URL Local del Bridge"**
6. Déjalo en: `http://127.0.0.1:8080` (recomendado)
7. Haz clic en **"Guardar Configuración del Lector"**

### Paso 2: Configurar CORS en el Bridge

El bridge debe permitir peticiones desde tu dominio web.

**Python Flask:**
```python
from flask import Flask
from flask_cors import CORS

app = Flask(__name__)
CORS(app, origins=[
    "https://tu-dominio.com",
    "http://localhost",
    "http://127.0.0.1"
])
```

**Node.js Express:**
```javascript
const express = require('express');
const cors = require('cors');

const app = express();

app.use(cors({
    origin: [
        'https://tu-dominio.com',
        'http://localhost',
        'http://127.0.0.1'
    ]
}));
```

### Paso 3: Actualizar IP del Lector (si cambió)

En el bridge, actualiza la IP del lector a la red IENTC:

```python
# Si el lector está en red 192.168.16.x
DEVICE_IP = "192.168.16.129"  # Ajusta según tu red
```

---

## 🔄 Flujo de Operación

1. **Operador registra un acceso** desde el navegador en la PC puente
2. **AccessLog.php** detecta que está en modo local
3. **Guarda usuario en sesión** (no envía por PHP)
4. **Redirige a print_ticket.php**
5. **JavaScript lee la sesión** y obtiene los datos del usuario
6. **JavaScript hace fetch()** a `http://127.0.0.1:8080/create-ticket-user`
7. **Bridge recibe la petición** y crea el usuario en el lector
8. **Notificación en pantalla** confirma el resultado (éxito/error)

---

## 🧪 Pruebas

### 1. Verificar Configuración

```php
// En config.php deberías ver:
define('HIKVISION_BRIDGE_LOCAL_MODE', true);  // Activado
define('HIKVISION_BRIDGE_LOCAL_URL', 'http://127.0.0.1:8080');
```

### 2. Verificar Bridge está corriendo

Desde la PC puente, abre el navegador y ve a:
```
http://127.0.0.1:8080
```
Deberías ver una respuesta del bridge (aunque sea error 404, significa que está corriendo).

### 3. Registrar un Acceso de Prueba

1. Accede al sistema desde el navegador **en la PC puente**
2. Registra una entrada (crea un ticket)
3. En la página de impresión del ticket:
   - Abre **DevTools** (F12)
   - Ve a la pestaña **Console**
   - Deberías ver:
     ```
     🏠 Modo Local Hikvision: 1 usuario(s) pendientes
     📤 Enviando a: http://127.0.0.1:8080/create-ticket-user
     ✅ Usuario creado: TKT-000123 PIN: 1234
     ```
4. Debería aparecer una **notificación verde** en la esquina superior derecha

---

## 🐛 Troubleshooting

### Problema 1: Error de CORS
```
Access to fetch at 'http://127.0.0.1:8080/...' has been blocked by CORS policy
```

**Solución:** Configura CORS en el bridge (ver Paso 2 arriba).

---

### Problema 2: Connection Refused
```
Failed to fetch: TypeError: Failed to fetch
```

**Solución:**
1. Verifica que el bridge esté corriendo en la PC puente
2. Verifica el puerto: `netstat -ano | findstr :8080`
3. Asegúrate de usar `http://127.0.0.1:8080` en la configuración

---

### Problema 3: No aparece notificación
**Solución:**
1. Abre DevTools → Console
2. Verifica los mensajes de log
3. Busca errores en rojo
4. Verifica que `Modo Local` esté activado en Configuraciones

---

### Problema 4: Usuario no se crea en el lector
**Solución:**
1. Verifica los logs del bridge
2. Confirma que el bridge puede conectarse al lector
3. Verifica la IP del lector en el bridge
4. Revisa que el endpoint sea `/create-ticket-user`

---

## 📊 Comparación de Modos

| Característica | Modo Remoto | Modo Local |
|----------------|-------------|------------|
| **Ubicación** | Cualquier PC | Solo PC Puente |
| **Tipo petición** | Server-side (PHP cURL) | Client-side (JS fetch) |
| **URL Bridge** | IP pública (189.141.177.2) | Localhost (127.0.0.1) |
| **Necesita IP fija** | ✅ Sí | ❌ No |
| **Afectado por CGNAT** | ⚠️ Puede fallar | ✅ No afectado |
| **Requiere CORS** | ❌ No | ✅ Sí |
| **Velocidad** | Depende de red | ⚡ Instantáneo |

---

## 💡 Recomendaciones

### ✅ Usar Modo Local cuando:
- El operador siempre trabaja desde la PC puente
- No tienes IP pública fija (CGNAT/IP dinámica)
- Quieres máxima velocidad y confiabilidad

### ⚠️ Usar Modo Remoto cuando:
- Los operadores trabajan desde múltiples ubicaciones
- Tienes IP pública fija
- El bridge está expuesto correctamente a internet

### 💡 Modo Híbrido:
Puedes cambiar entre modos según necesidad:
- Activa modo local cuando uses la PC puente
- Desactiva modo local cuando trabajes remoto

---

## 🔐 Seguridad

### Modo Local:
- ✅ El bridge NO necesita estar expuesto a internet
- ✅ Las peticiones solo pueden venir del navegador local
- ⚠️ CORS debe estar bien configurado
- ⚠️ El bridge debe validar las peticiones

### Recomendaciones:
1. **CORS restrictivo:** Solo tu dominio
2. **Bridge en red local:** No exponerlo a internet
3. **Validación en bridge:** Verificar formato de datos
4. **HTTPS en producción:** Para el sistema web

---

## 📝 Archivos Modificados

1. **config/config.php** - Nuevas constantes de configuración
2. **app/models/AccessLog.php** - Lógica de modo local
3. **app/controllers/SettingsController.php** - Guardar configuración
4. **app/views/settings/index.php** - UI de configuración
5. **app/views/access/print_ticket.php** - Script JavaScript client

---

## 📞 Soporte

Si tienes problemas:
1. Revisa la **Consola del navegador** (F12 → Console)
2. Revisa los **logs del bridge**
3. Verifica la **configuración** en Settings
4. Consulta la sección **Troubleshooting** arriba

---

## ✅ Checklist de Verificación

- [ ] Modo Local activado en Configuraciones
- [ ] URL Local configurada: `http://127.0.0.1:8080`
- [ ] Bridge corriendo en la PC puente
- [ ] CORS configurado en el bridge
- [ ] IP del lector actualizada en el bridge (192.168.16.x)
- [ ] Sistema habilitado (`HIKVISION_ENABLED = true`)
- [ ] Probado: registrar acceso y ver notificación

---

## 🚀 Estado

**Versión:** 1.0.0  
**Fecha:** 5 de enero de 2026  
**Estado:** ✅ Implementado y listo para usar  
**Requiere:** Configurar CORS en el bridge

---

## 💬 Notas Finales

- **NO necesitas IP fija** con modo local ✅
- El bridge debe estar **corriendo** en la PC puente
- Las peticiones se hacen **desde el navegador** (no desde el servidor PHP)
- Puedes ver todo el proceso en la **Consola del navegador**
- Las notificaciones aparecen **automáticamente** en pantalla
