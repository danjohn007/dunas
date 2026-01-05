# 🚀 Guía de Solución: Configurar Bridge Hikvision en PC Puente

## ✅ Cambios Realizados

1. **agent.py** - IP del lector actualizada a `192.168.16.59`
2. **agent.py** - CORS habilitado para permitir peticiones del navegador
3. **test-hikvision-bridge.php** - Detecta modo local y usa `127.0.0.1`

---

## 📋 Pasos para que Funcione

### Paso 1: Instalar flask-cors en la PC Puente

Abre PowerShell en la PC puente y ejecuta:

```powershell
cd C:\Users\Roberto\Desktop\dunas\drivers\dunas-agent
.\install_cors.bat
```

O manualmente:
```powershell
python -m pip install flask-cors
```

---

### Paso 2: Reiniciar el Bridge

1. **Detener el bridge** si está corriendo (Ctrl+C en la terminal)
2. **Iniciar nuevamente:**

```powershell
cd C:\Users\Roberto\Desktop\dunas\drivers\dunas-agent
.\run_agent.bat
```

Deberías ver:
```
=== Agente escuchando en puerto 8080 ===
 * Running on all addresses (0.0.0.0)
 * Running on http://127.0.0.1:8080
```

---

### Paso 3: Activar Modo Local en el Sistema

1. Accede al sistema desde el navegador en la PC puente
2. Ve a **Configuraciones del Sistema**
3. Busca la sección **"Lector HikVision (Control de Acceso)"**
4. Marca el checkbox **🏠 Modo Local (PC Puente)**
5. Verifica que la URL Local sea: `http://127.0.0.1:8080`
6. Haz clic en **"Guardar Configuración del Lector"**

---

### Paso 4: Probar la Conexión

1. Ve a: `https://systemcontrol.digital/dunas/public/test-hikvision-bridge.php`
2. Deberías ver un banner amarillo que dice: **🏠 MODO LOCAL ACTIVO**
3. Haz clic en **"🔌 Probar Conexión"**
4. Debería aparecer: **✅ Conexión exitosa con el PC puente: http://127.0.0.1:8080**

---

### Paso 5: Crear Usuario de Prueba

1. En la misma página de test
2. Completa los campos:
   - **Nombre del Usuario:** Usuario Test
   - **PIN:** 1234 (o cualquier 4 dígitos)
   - **Horas de Validez:** 1
3. Haz clic en **"➕ Crear Usuario de Prueba"**

---

## 🐛 Troubleshooting

### Error: ModuleNotFoundError: No module named 'flask_cors'

**Solución:** Ejecuta:
```powershell
python -m pip install flask-cors
```

---

### Error: No se puede conectar (Connection Refused)

**Causas posibles:**
1. El bridge no está corriendo
2. El bridge está en otro puerto

**Solución:**
```powershell
# Verificar si el puerto 8080 está en uso
netstat -ano | findstr :8080

# Si no aparece nada, el bridge no está corriendo
# Inicia el bridge:
cd C:\Users\Roberto\Desktop\dunas\drivers\dunas-agent
.\run_agent.bat
```

---

### Error: CORS Policy

Si ves en la consola del navegador:
```
Access to fetch at 'http://127.0.0.1:8080/...' has been blocked by CORS policy
```

**Solución:**
1. Verifica que `flask-cors` esté instalado
2. Reinicia el bridge después de instalar CORS

---

### Error: Timeout connecting to device

Si el bridge responde pero no puede conectarse al lector:

**Causas posibles:**
1. La IP del lector es incorrecta
2. El lector no está accesible desde la PC puente

**Verificar conectividad:**
```powershell
# Hacer ping al lector
ping 192.168.16.59

# Si no responde, verifica:
# 1. Que estés en la misma red (192.168.16.x)
# 2. Que el lector esté encendido
# 3. Que la IP sea correcta
```

---

## 📊 Verificación de Red

Para confirmar que estás en la red correcta:

```powershell
ipconfig
```

Deberías ver:
```
Dirección IPv4. . . . . . . : 192.168.16.X  ← Tu PC
```

Y el lector debe estar en: `192.168.16.59`

---

## ✅ Checklist de Verificación

- [ ] Bridge corriendo en puerto 8080
- [ ] flask-cors instalado
- [ ] agent.py tiene IP `192.168.16.59`
- [ ] Modo Local activado en Configuraciones
- [ ] Test muestra "MODO LOCAL ACTIVO"
- [ ] Prueba de conexión exitosa
- [ ] PC y lector en misma red (192.168.16.x)

---

## 🎯 Flujo Completo

```
Navegador (PC Puente)
    ↓ JavaScript fetch()
Bridge (127.0.0.1:8080)
    ↓ Python requests
Lector (192.168.16.59)
    ↓
Usuario creado ✅
```

---

## 📝 Logs del Bridge

Para ver qué está pasando, revisa el archivo de log:

```powershell
cd C:\Users\Roberto\Desktop\dunas\drivers\dunas-agent
notepad agent.log
```

O en tiempo real:
```powershell
Get-Content agent.log -Wait -Tail 20
```

---

## 🔍 Verificar si el Bridge Responde

Abre PowerShell y ejecuta:

```powershell
# Test simple
curl http://127.0.0.1:8080

# Debería responder con error 404 (pero está funcionando)
```

---

## 💡 Tip Final

Si todo falla, verifica que:
1. **Firewall** permita el puerto 8080
2. **Antivirus** no esté bloqueando Python
3. **Bridge** esté corriendo con permisos de administrador

---

## ✅ Estado Esperado

Cuando todo funcione, deberías ver en el test:

```
🏠 MODO LOCAL ACTIVO
URL Local: http://127.0.0.1:8080
Las pruebas se harán a localhost en lugar de la IP pública

✅ Conexión exitosa con el PC puente: http://127.0.0.1:8080
```

Y al crear un usuario de prueba:
```
✅ Usuario de prueba creado exitosamente
ID: TEST-1736123456
Nombre: Usuario Test
PIN: 1234
Válido: 1 hora(s)
```
