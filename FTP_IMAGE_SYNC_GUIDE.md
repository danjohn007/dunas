# Guía de Configuración - Sincronización de Imágenes FTP

Esta guía explica cómo configurar la funcionalidad de sincronización de imágenes desde un servidor FTP para el sistema de reconocimiento de placas (ANPR).

## Descripción General

El sistema incluye funcionalidad para sincronizar automáticamente imágenes vehiculares desde un servidor FTP a la carpeta pública del sistema. Esto es útil cuando las cámaras ANPR guardan las imágenes en un servidor FTP externo.

## Componentes

### 1. Script de Sincronización
- **Ubicación:** `/public/mover_ftp_a_public.php`
- **Función:** Conecta al servidor FTP, descarga imágenes nuevas y las guarda localmente

### 2. Endpoint AJAX
- **Ubicación:** `/public/api/run_mover_ftp.php`
- **Función:** Ejecuta el script de sincronización mediante llamadas AJAX
- **Método:** POST
- **Respuesta:** JSON con estado de éxito/error

### 3. Integración en UI
- **Ubicación:** Botón "Detectar Placa Nuevamente" en `/app/views/access/create.php`
- **Flujo:**
  1. Usuario hace clic en "Detectar Placa Nuevamente"
  2. Sistema ejecuta sincronización FTP
  3. Sistema consulta última detección de placa
  4. Muestra resultado de comparación

## Configuración

### Paso 1: Agregar Configuración FTP a la Base de Datos

Debe agregar los siguientes registros en la tabla `settings`:

```sql
-- Configuración del servidor FTP
INSERT INTO settings (setting_key, setting_value) VALUES 
('ftp_host', 'ftp.example.com'),
('ftp_port', '21'),
('ftp_user', 'usuario_ftp'),
('ftp_pass', 'contraseña_ftp'),
('ftp_images_path', '/imagenes/vehiculos'),
('ftp_image_pattern', '_PLACA_.*VEHICLE\.jpg$');
```

### Paso 2: Configurar Parámetros

| Parámetro | Descripción | Ejemplo |
|-----------|-------------|---------|
| `ftp_host` | Dirección del servidor FTP | `192.168.1.100` o `ftp.miservidor.com` |
| `ftp_port` | Puerto del servidor FTP | `21` (predeterminado) |
| `ftp_user` | Usuario de autenticación FTP | `admin` |
| `ftp_pass` | Contraseña de autenticación FTP | `password123` |
| `ftp_images_path` | Ruta en el servidor FTP donde están las imágenes | `/capturas/anpr` o `/` |
| `ftp_image_pattern` | Patrón regex para filtrar archivos | `_PLACA_.*VEHICLE\.jpg$` |

### Paso 3: Crear Directorio de Destino

El sistema creará automáticamente el directorio `/public/uploads/anpr/` si no existe. Asegúrese de que el servidor web tenga permisos de escritura:

```bash
# Linux
sudo mkdir -p /var/www/html/public/uploads/anpr
sudo chmod 755 /var/www/html/public/uploads/anpr
sudo chown www-data:www-data /var/www/html/public/uploads/anpr
```

## Patrón de Nombres de Archivo

El parámetro `ftp_image_pattern` utiliza expresiones regulares de PHP para filtrar archivos. El patrón predeterminado es:

```
_PLACA_.*VEHICLE\.jpg$
```

Este patrón coincide con archivos como:
- `2024-01-15_PLACA_ABC123_VEHICLE.jpg`
- `CAM01_PLACA_XYZ789_VEHICLE.jpg`
- `20240115123045_PLACA_DEF456_VEHICLE.jpg`

### Personalizar el Patrón

Ejemplos de patrones personalizados:

```sql
-- Solo archivos .jpg
UPDATE settings SET setting_value = '\.jpg$' WHERE setting_key = 'ftp_image_pattern';

-- Archivos que contengan "PLATE" o "PLACA"
UPDATE settings SET setting_value = '(PLATE|PLACA).*\.(jpg|jpeg|png)$' WHERE setting_key = 'ftp_image_pattern';

-- Todos los archivos de imagen
UPDATE settings SET setting_value = '\.(jpg|jpeg|png|gif)$' WHERE setting_key = 'ftp_image_pattern';
```

## Seguridad

### Permisos de Usuario

El endpoint `/api/run_mover_ftp.php` requiere autenticación y verifica que el usuario tenga uno de los siguientes roles:
- `admin`
- `supervisor`
- `operator`

### Conexión FTP

- El script utiliza modo pasivo FTP por defecto (útil con firewalls)
- Timeout de conexión: 10 segundos
- Solo descarga archivos que no existen localmente (evita descargas duplicadas)

### Logs

Los errores y actividades se registran en el log de PHP:
- Errores de conexión FTP
- Errores de autenticación
- Archivos descargados
- Total de imágenes sincronizadas

## Uso

### Uso Manual

Los usuarios con permisos adecuados pueden hacer clic en el botón **"Detectar Placa Nuevamente"** en la pantalla de Registrar Entrada:

1. El botón muestra "Ejecutando..." con un spinner
2. Se ejecuta la sincronización FTP
3. Se consulta la última detección de placa
4. Se muestra el resultado de la comparación

### Uso Programático

También puede llamar directamente al endpoint mediante AJAX:

```javascript
const response = await fetch('/api/run_mover_ftp.php', {
    method: 'POST',
    headers: { 
        'Accept': 'application/json' 
    }
});

const result = await response.json();
if (result.success) {
    console.log('Sincronización FTP exitosa');
} else {
    console.error('Error:', result.error);
}
```

## Solución de Problemas

### Error: "No se encontró mover_ftp_a_public.php"

**Causa:** El archivo del script no existe o la ruta es incorrecta.

**Solución:** Verifique que `/public/mover_ftp_a_public.php` existe.

### Error: "Configuración FTP incompleta"

**Causa:** Faltan parámetros FTP en la tabla `settings`.

**Solución:** Agregue los parámetros `ftp_host` y `ftp_user` a la base de datos.

### Error de Conexión FTP

**Causa:** El servidor FTP no es accesible o los parámetros son incorrectos.

**Solución:**
1. Verifique que el servidor FTP esté en línea
2. Verifique la configuración de firewall
3. Confirme el puerto correcto (21 para FTP estándar)
4. Pruebe las credenciales con un cliente FTP

### No se Descargan Imágenes

**Causa:** El patrón de archivo no coincide con los nombres reales.

**Solución:**
1. Revise los nombres de archivo en el servidor FTP
2. Ajuste el patrón `ftp_image_pattern` según sea necesario
3. Revise los logs de PHP para ver qué archivos se encontraron

### Permisos Denegados

**Causa:** El servidor web no tiene permisos para escribir en `/public/uploads/anpr/`.

**Solución:**
```bash
sudo chmod 755 /var/www/html/public/uploads/anpr
sudo chown www-data:www-data /var/www/html/public/uploads/anpr
```

## Notas Importantes

1. **Sin FTP configurado:** Si no hay configuración FTP, el sistema no genera errores críticos y continúa funcionando normalmente sin sincronización.

2. **Descargas incrementales:** El script solo descarga archivos que no existen localmente, evitando descargas duplicadas.

3. **Compatibilidad:** El script es compatible con servidores FTP estándar que soporten modo pasivo.

4. **Rendimiento:** La sincronización puede tomar tiempo dependiendo del número y tamaño de las imágenes. El botón muestra un indicador de carga durante el proceso.

## Mantenimiento

### Limpieza de Imágenes Antiguas

Se recomienda configurar una tarea cron para eliminar imágenes antiguas:

```bash
# Eliminar imágenes mayores a 30 días
0 2 * * * find /var/www/html/public/uploads/anpr -type f -mtime +30 -delete
```

### Monitoreo

Revise regularmente los logs de PHP para detectar problemas:

```bash
tail -f /var/log/php/error.log | grep "mover_ftp"
```

## Soporte

Para problemas o preguntas adicionales, consulte:
- Documentación del sistema en `/README.md`
- Logs del sistema en `/logs/`
- Configuración en `config/config.php`
