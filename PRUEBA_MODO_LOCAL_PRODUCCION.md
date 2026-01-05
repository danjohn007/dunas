# ✅ Guía de Prueba - Modo Local en Producción

## 🎯 Objetivo
Verificar que cuando se crea un ticket real (acceso), el usuario se cree en el lector usando modo local (client-side) desde la PC puente.

---

## 📋 Pre-requisitos

### ✅ Verificar Configuración

1. **Modo Local activado en config.php:**
   ```php
   define('HIKVISION_BRIDGE_LOCAL_MODE', true);
   define('HIKVISION_BRIDGE_LOCAL_URL', 'http://127.0.0.1:8080');
   define('HIKVISION_ENABLED', true);
   ```

2. **Bridge corriendo en PC puente:**
   ```
   C:\Users\Samsung\Desktop\dunas-agent\dunas-agent\run_agent.bat
   ```
   
   Deberías ver:
   ```
   === Agente escuchando en puerto 8080 ===
   * Serving Flask app 'agent'
   ```

---

## 🧪 Pasos de Prueba

### Paso 1: Abrir Sistema desde PC Puente

1. En la **PC puente** (Samsung), abre el navegador
2. Ve a: `https://systemcontrol.digital/dunas`
3. Inicia sesión como administrador

### Paso 2: Crear un Acceso (Ticket)

1. Ve a: **Accesos** → **Registrar Entrada**
2. Llena el formulario:
   - Cliente: [Selecciona un cliente]
   - Chofer: [Selecciona un chofer]
   - Unidad: [Selecciona una unidad]
   - Capacidad: [Cantidad de litros]
3. Haz clic en **"Guardar"**

### Paso 3: Ver el Ticket

1. El sistema te redirigirá a la página del ticket
2. Deberías ver el ticket con código QR y PIN
3. **IMPORTANTE:** Mantén abierta la consola del navegador (F12 → Console)

### Paso 4: Verificar en la Consola

En la consola del navegador deberías ver:

```javascript
🏠 Modo Local Hikvision: 1 usuario(s) pendientes
📤 Enviando a: http://127.0.0.1:8080/create-ticket-user {...}
✅ Usuario creado: TKT-000XXX PIN: XXXX
```

### Paso 5: Verificar Notificación Visual

Deberías ver una **notificación verde** en la esquina superior derecha:

```
✅ Usuario creado: TKT-000XXX
PIN: XXXX | Válido: 2h
```

### Paso 6: Verificar en el Lector

1. Ve al lector físicamente
2. Prueba ingresar el PIN en el teclado
3. Debería abrir/autorizar el acceso ✅

---

## 🔍 Verificación en Logs

### En el navegador (Consola - F12):

#### ✅ Éxito:
```javascript
🏠 Modo Local Hikvision: 1 usuario(s) pendientes
📤 Enviando a: http://127.0.0.1:8080/create-ticket-user
✅ Usuario creado: TKT-000307 PIN: 1234
```

#### ❌ Error de conexión (esperado si no estás en PC puente):
```javascript
❌ Error al enviar usuario: Failed to fetch
IMPORTANTE: Este test SOLO funciona si lo ejecutas desde la PC donde corre el bridge.
```

### En agent.log (PC puente):

```
2026-01-05 16:00:00,000 - INFO - create-ticket-user recibido: {
    "device_user_id": "TKT-000307",
    "name": "Cliente Test",
    "pin": "1234",
    "card_number": "1234",
    "hours_valid": 2
}
2026-01-05 16:00:00,500 - INFO - create_device_user RECORD RESP 200: {"statusCode": 1, "statusString": "OK"}
2026-01-05 16:00:00,700 - INFO - create_device_user MODIFY RESP 200: {"statusCode": 1, "statusString": "OK"}
2026-01-05 16:00:00,900 - INFO - create_device_card RESP 200: {"statusCode": 1, "statusString": "OK"}
2026-01-05 16:00:01,000 - INFO - 127.0.0.1 - - [05/Jan/2026 16:00:01] "POST /create-ticket-user HTTP/1.1" 200 -
```

---

## 🔧 Solución de Problemas

### ❌ "Error al crear usuario" o no aparece notificación

**Causa:** El bridge no está corriendo en la PC puente

**Solución:**
1. Ve a la PC puente
2. Ejecuta: `run_agent.bat`
3. Verifica que diga "Agente escuchando en puerto 8080"

---

### ❌ "Failed to fetch" en consola

**Causa:** Estás probando desde otra PC (no la PC puente)

**Solución:** Esto es **normal y esperado**. El modo local SOLO funciona desde la PC puente.

---

### ❌ Usuario se crea pero card_number está vacío

**Causa:** El código anterior no validaba card_number vacío

**Solución:** Ya está corregido en agent.py. Si persiste:
```python
# En agent.py, línea ~248:
if not card_number or card_number.strip() == "":
    card_number = pin
```

---

### ❌ El bridge se cierra inmediatamente

**Causa:** Falta flask_cors

**Solución:**
```cmd
cd C:\Users\Samsung\Desktop\dunas-agent\dunas-agent
python -m pip install flask-cors
python agent.py
```

---

## 📊 Diferencias: Modo Local vs Modo Remoto

### Modo Local (Actual - Activado)
```
Usuario (PC Puente)
    ↓ crea ticket
Servidor Web (systemcontrol.digital)
    ↓ guarda en sesión
    ↓ envía HTML con JavaScript
Usuario (PC Puente - Navegador)
    ↓ JavaScript ejecuta fetch()
    ↓ http://127.0.0.1:8080/create-ticket-user
Bridge (localhost en PC Puente) ✅
    ↓
Lector HikVision (192.168.16.59) ✅
```

**Ventajas:**
- ✅ No requiere IP pública
- ✅ Más seguro
- ✅ No requiere port forwarding
- ❌ Solo funciona desde PC puente

### Modo Remoto (Anterior - Desactivado)
```
Usuario (cualquier PC)
    ↓ crea ticket
Servidor Web (systemcontrol.digital)
    ↓ ejecuta PHP cURL
    ↓ http://189.141.177.2:8080 (IP pública)
Bridge (PC Puente con IP pública) ❌
    ↓
Lector HikVision (192.168.16.59)
```

**Ventajas:**
- ✅ Funciona desde cualquier PC
- ❌ Requiere IP pública fija
- ❌ Requiere port forwarding
- ❌ Menos seguro

---

## ✅ Checklist Final

Antes de dar por terminada la implementación, verifica:

- [ ] Modo local activado en config.php
- [ ] Bridge corriendo en PC puente
- [ ] Test de conexión funciona desde PC puente
- [ ] Crear usuario de prueba funciona desde PC puente
- [ ] **Crear ticket real funciona desde PC puente** ← PRUEBA ESTO
- [ ] Notificación verde aparece al crear ticket
- [ ] PIN funciona en el lector físico
- [ ] Logs en agent.log muestran respuestas 200
- [ ] Consola del navegador muestra "✅ Usuario creado"

---

## 🎉 ¡Todo Listo!

Si todos los pasos anteriores funcionan correctamente, tu sistema está operando en **modo local** exitosamente.

**Recuerda:**
- Los tickets deben crearse desde la **PC puente** (Samsung)
- Si intentas crear desde otra PC, no funcionará (comportamiento esperado)
- El bridge debe estar corriendo siempre en la PC puente
