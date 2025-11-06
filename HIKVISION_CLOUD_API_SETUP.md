# Guía de Implementación: HikVision Cloud API (Hik-Partner)

Esta guía describe los pasos para implementar la integración con la API Cloud de HikVision (Hik-Partner) para detección automática de placas vehiculares.

## 📋 Resumen de Cambios

Se ha implementado soporte completo para la API Cloud de HikVision con las siguientes características:

- ✅ Autenticación OAuth con tokens automáticos
- ✅ Detección de placas en tiempo real
- ✅ Normalización y comparación de placas
- ✅ Registro de detecciones en base de datos
- ✅ UI de comparación visual (verde = match, amarillo = no match)
- ✅ Compatibilidad con ISAPI local (legacy)

## 🗄️ Paso 1: Actualizar Base de Datos

Ejecuta las siguientes migraciones SQL en orden:

### 1.1. Actualizar tabla `hikvision_devices` para Cloud API

```bash
mysql -u tu_usuario -p dunas_access_control < config/01_update_hikvision_devices_cloud.sql
```

Esta migración agrega las columnas necesarias:
- `api_key` - Clave API de Hik-Partner
- `api_secret` - Secret Key de Hik-Partner  
- `token_endpoint` - URL para obtener token
- `area_domain` - Dominio del área
- `access_token` - Token de acceso (se llena automáticamente)
- `token_expires_at` - Fecha de expiración del token
- `device_index_code` - Código/Serial del dispositivo
- `area_label` - Ubicación física

### 1.2. Crear tabla `detected_plates`

```bash
mysql -u tu_usuario -p dunas_access_control < config/02_create_detected_plates.sql
```

Esta migración crea la tabla para registrar todas las detecciones de placas con trazabilidad completa.

## ⚙️ Paso 2: Configurar Dispositivo en el Sistema

1. **Accede a Configuraciones:**
   - Inicia sesión como administrador
   - Ve a **Configuraciones del Sistema**
   - Desplázate hasta la sección **"Dispositivos HikVision"**

2. **Haz clic en "+ Nuevo dispositivo"**

3. **Completa los datos del dispositivo:**

   **Información Básica:**
   - **Nombre del Dispositivo:** `Cámara Placas`
   - **Tipo de Dispositivo:** `Cámara LPR (Lectura de Placas)`

   **Credenciales Cloud API:**
   - **Api Key:** `ErfVjgzq0y`
   - **Api Secret:** `frssZ1XEgN`
   - **Endpoint (Token):** `https://isaapi.hik-partner.com/api/hpcgw/v1/token/get`
   - **Area Domain:** `https://iusapi.hik-partner.com`
   - **Device Index Code / Serial:** `GA8817570`
   - **Área / Ubicación:** `Entrada Principal`

   **Opciones:**
   - ✅ **Dispositivo habilitado**
   - ✅ **Verificar certificado SSL**

4. **Guarda los cambios:**
   - Haz clic en **"Guardar Dispositivos HikVision"**
   - El sistema automáticamente obtendrá el token de acceso

## 🎯 Paso 3: Verificar Funcionamiento

### 3.1. Verificar Token de Acceso

Después de guardar, ve nuevamente a Configuraciones. Si la configuración es correcta, verás:
- Un campo **"Access Token (automático)"** con un token largo
- Una fecha de expiración del token

### 3.2. Probar Detección de Placas

1. Ve a **Registrar Entrada**
2. Selecciona un **Cliente**
3. Selecciona una **Unidad** con placa conocida
4. Deberías ver automáticamente:
   - **Comparación de Placas** con dos bloques:
     - **Placa de Unidad Guardada:** La placa del sistema
     - **Placa de Unidad Detectada:** La placa detectada por la cámara
   - **Resultado de Comparación:**
     - 🟢 **Verde** si las placas coinciden
     - 🟡 **Amarillo** si no coinciden

### 3.3. Refrescar Detección

Si necesitas volver a consultar la cámara:
- Haz clic en **"Detectar Placa Nuevamente"**
- El sistema consultará los últimos 10 segundos de eventos

## 🔧 Configuración Avanzada

### Múltiples Dispositivos

Puedes configurar múltiples cámaras LPR:
1. El sistema usará el **primer dispositivo habilitado** de tipo LPR
2. Puedes cambiar el orden con el campo `sort_order`
3. Deshabilita dispositivos que no quieras usar temporalmente

### Modo ISAPI Local (Legacy)

Si necesitas usar ISAPI en lugar de Cloud:
1. Deja vacíos los campos Cloud API
2. Completa la sección **"Configuración ISAPI Local"**:
   - URL de API (ISAPI)
   - Usuario (ISAPI)
   - Contraseña (ISAPI)

El sistema detectará automáticamente qué modo usar.

## 📊 Monitoreo de Detecciones

Todas las detecciones se guardan en la tabla `detected_plates` con:
- Placa detectada (normalizada)
- Nivel de confianza
- Timestamp de captura
- ID del dispositivo
- ID de la unidad (si hay match)
- Flag de match (0 = no match, 1 = match)
- Payload JSON completo del evento

## 🐛 Solución de Problemas

### El token no se genera

**Síntoma:** Campo "Access Token" vacío o error al guardar

**Solución:**
1. Verifica que `api_key` y `api_secret` sean correctos
2. Verifica que el `token_endpoint` sea accesible
3. Revisa el log del servidor en `logs/`

### No se detectan placas

**Síntoma:** "Sin detección" o "Cargando..." permanente

**Solución:**
1. Verifica que el dispositivo esté habilitado
2. Verifica que `device_index_code` sea correcto
3. Verifica que `area_domain` sea accesible
4. Comprueba que haya eventos recientes en la cámara

### Las placas no coinciden

**Síntoma:** Siempre muestra amarillo aunque debería coincidir

**Posibles causas:**
1. La placa en el sistema tiene formato diferente (espacios, guiones)
2. La cámara detectó incorrectamente
3. Nivel de confianza bajo

**Solución:**
- El sistema normaliza automáticamente (mayúsculas, sin espacios/guiones)
- Verifica el formato de la placa guardada en "Unidades"
- Revisa el nivel de confianza de la detección

## 🔐 Seguridad

- Las credenciales se almacenan en la base de datos
- Los tokens se renuevan automáticamente 2 minutos antes de expirar
- Las contraseñas se ocultan en la UI (tipo password)
- Los secretos no se exponen en logs

## 📝 Notas Importantes

1. **Ventana de Tiempo:** El sistema consulta eventos de los últimos 10 segundos
2. **Rate Limits:** Se recomienda no hacer polling continuo para evitar límites de API
3. **Timeouts:** Las consultas tienen timeout de 5 segundos
4. **SSL:** Se recomienda activar verificación SSL en producción
5. **No Bloqueo:** El registro de entrada NO se bloquea si las placas no coinciden

## 🆘 Soporte

Si encuentras problemas:
1. Revisa los logs del servidor
2. Verifica la consola del navegador (F12)
3. Comprueba las respuestas de la API en Network tab
4. Consulta la documentación de Hik-Partner API

---

**Fecha de implementación:** 2025-11-06  
**Versión:** 1.0.0  
**Compatibilidad:** MySQL 5.7+, PHP 7.4+
