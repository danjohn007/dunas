# Setup: Automatic Plate Detection System

## Quick Start

Este sistema registra automáticamente placas vehiculares detectadas por cámaras Hikvision. Sigue estos pasos para activarlo.

## Paso 1: Ejecutar Migración de Base de Datos

Ejecuta el script SQL para crear la tabla de seguimiento:

```bash
mysql -u fix360_dunas -p fix360_dunas < config/03_create_processed_plate_files.sql
```

O desde MySQL:
```sql
USE fix360_dunas;
source /path/to/dunas/config/03_create_processed_plate_files.sql;
```

**Verifica que se creó correctamente:**
```sql
USE fix360_dunas;
SHOW TABLES LIKE 'processed_plate_files';
DESCRIBE processed_plate_files;
```

Deberías ver:
```
+---------------+--------------+------+-----+-------------------+
| Field         | Type         | Null | Key | Default           |
+---------------+--------------+------+-----+-------------------+
| id            | int(11)      | NO   | PRI | NULL              |
| filename      | varchar(255) | NO   | UNI | NULL              |
| processed_at  | timestamp    | NO   |     | CURRENT_TIMESTAMP |
+---------------+--------------+------+-----+-------------------+
```

## Paso 2: Verificar Directorio de Imágenes

El directorio `/public/Imagenes/` se crea automáticamente si no existe. Verifica los permisos:

```bash
chmod 755 public/Imagenes
```

## Paso 3: Probar el Endpoint

Puedes probar el endpoint manualmente:

```bash
curl -X POST https://fix360.app/dunas/api/register_new_plates.php
```

Respuesta esperada cuando no hay imágenes:
```json
{
  "success": true,
  "inserted": 0,
  "message": "No hay archivos PLATE nuevos"
}
```

## Paso 4: Verificar Integración Frontend

1. Abre la vista de registro de entrada: `/access/create`
2. Abre la consola del navegador (F12)
3. Deberías ver cada 10 segundos:
   - `🔁 mover_ftp_a_public.php ejecutado correctamente`
   - (Solo si hay placas nuevas): `✅ Detectadas/insertadas: N placas`

## Verificación del Sistema

### Verificar que las imágenes llegan

```bash
ls -la public/Imagenes/*_PLATE.jpg
```

Formato esperado de nombres de archivo:
```
20251110154755988_XYA100F_VEHICLE_DETECTION_Hik__PLATE.jpg
20251111120000000_ABC123X_VEHICLE_DETECTION_Hik__PLATE.jpg
```

### Verificar registros en la base de datos

```sql
-- Ver últimas placas detectadas
SELECT * FROM detected_plates 
ORDER BY captured_at DESC 
LIMIT 10;

-- Ver archivos procesados
SELECT * FROM processed_plate_files 
ORDER BY processed_at DESC 
LIMIT 10;

-- Contar placas registradas hoy
SELECT COUNT(*) as total_hoy 
FROM detected_plates 
WHERE DATE(captured_at) = CURDATE();
```

## Troubleshooting

### Problema: No se registran placas

**Solución 1**: Verificar que el directorio existe y tiene permisos
```bash
ls -ld public/Imagenes
# Debe mostrar: drwxr-xr-x ... public/Imagenes
```

**Solución 2**: Verificar que las imágenes tienen el formato correcto
```bash
# Correcto:
20251110154755988_XYA100F_VEHICLE_DETECTION_Hik__PLATE.jpg

# Incorrecto (falta sufijo):
20251110154755988_XYA100F.jpg

# Incorrecto (formato diferente):
XYA100F_PLATE.jpg
```

**Solución 3**: Revisar logs de PHP
```bash
tail -f /var/log/php/error.log
# o
tail -f logs/error.log
```

### Problema: Duplicados

El sistema **previene duplicados automáticamente** usando la tabla `processed_plate_files`.

Si necesitas reprocesar un archivo específico:
```sql
DELETE FROM processed_plate_files 
WHERE filename = 'nombre_archivo.jpg';
```

### Problema: Error de conexión a base de datos

Verifica la configuración en `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fix360_dunas');
define('DB_USER', 'fix360_dunas');
define('DB_PASS', 'tu_password');
```

## Mantenimiento

### Limpieza periódica de imágenes

Recomendado: Crear un cron job para eliminar imágenes antiguas (más de 30 días):

```bash
# Agregar al crontab
crontab -e

# Agregar esta línea (ejecuta diariamente a las 3 AM)
0 3 * * * find /path/to/dunas/public/Imagenes -name "*_PLATE.jpg" -mtime +30 -delete
```

### Limpieza de registros antiguos

Opcional: Limpiar registros procesados muy antiguos (más de 90 días):

```sql
DELETE FROM processed_plate_files 
WHERE processed_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

## Arquitectura del Sistema

```
┌─────────────────┐
│  Cámara         │
│  Hikvision      │
│  (FTP/Cloud)    │
└────────┬────────┘
         │
         │ (mover_ftp_a_public.php)
         ▼
┌─────────────────┐
│ /public/        │
│ Imagenes/       │
│ *.jpg           │
└────────┬────────┘
         │
         │ (register_new_plates.php)
         │ [Cada 10 segundos]
         ▼
┌─────────────────┐
│  Base de Datos  │
│                 │
│ detected_plates │
│ processed_plate_│
│ files           │
└─────────────────┘
```

## Documentación Adicional

Para más detalles, consulta:
- `PLATE_DETECTION_GUIDE.md` - Guía completa del sistema
- `config/02_create_detected_plates.sql` - Esquema de tabla de detecciones
- `config/03_create_processed_plate_files.sql` - Esquema de tabla de seguimiento

## Soporte

Si tienes problemas:
1. Revisa los logs: `logs/error.log` y `/var/log/php/error.log`
2. Verifica las tablas de base de datos existen
3. Confirma que el directorio `public/Imagenes` tiene permisos correctos
4. Prueba el endpoint manualmente con curl
5. Revisa la consola del navegador en `/access/create`
