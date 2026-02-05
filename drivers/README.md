# Agente Dunas - Instrucciones de Instalación

## Descripción

El agente Dunas es un servicio que se ejecuta en Windows para:
- Comunicarse con dispositivos Hikvision
- Procesar detección de placas
- Sincronizar datos con el servidor

## Archivos Requeridos

Debido al tamaño de los archivos, NO están incluidos en este repositorio.

### 1. Python 3.14.0 (o superior)

**Descargar desde:**
- Sitio oficial: https://www.python.org/downloads/
- Versión recomendada: Python 3.14.0 para Windows (64-bit)
- Tamaño aproximado: ~30 MB

**Instalación:**
```
python-3.14.0-amd64.exe /quiet InstallAllUsers=1 PrependPath=1
```

### 2. Agente Dunas (dunas-agent.zip)

**Obtener de:**
- Contactar al equipo de desarrollo
- O generar desde este repositorio:

```bash
# Comprimir archivos del agente
cd drivers/dunas-agent
zip -r ../dunas-agent.zip . -x "*.exe" -x "*.log" -x "__pycache__/*"
```

## Instalación Rápida

1. **Instalar Python:**
   ```cmd
   python-3.14.0-amd64.exe
   ```

2. **Descomprimir agente:**
   ```cmd
   unzip dunas-agent.zip -d C:\dunas-agent\
   ```

3. **Ejecutar instalación:**
   ```cmd
   cd C:\dunas-agent\
   install_and_run.bat
   ```

## Contenido del Agente

- `agent.py` - Servicio principal
- `install_agent.bat` - Instala el servicio
- `install_and_run.bat` - Instala y ejecuta
- `install_autostart.bat` - Configura inicio automático
- `install_cors.bat` - Configura CORS
- `open_firewall_8080.bat` - Abre puerto en firewall

## Solución de Problemas

### Error: Python no encontrado
```cmd
python --version
```
Si no funciona, reinstalar Python con la opción "Add to PATH"

### Error: Puerto 8080 ocupado
```cmd
netstat -ano | findstr :8080
taskkill /PID [número] /F
```

### Ver logs del agente
```cmd
type C:\dunas-agent\agent.log
```

## Notas de Seguridad

- Ejecutar como Administrador
- Configurar firewall correctamente
- Usar credenciales seguras para Hikvision
- Mantener Python actualizado

## Soporte

Para más información, consultar:
- `INSTRUCCIONES.txt`
- `SOLUCION_PASO_A_PASO.md`
- Contactar al equipo de desarrollo
