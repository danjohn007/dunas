# Guía de Detección Automática de Placas

## Descripción

Este sistema detecta y registra automáticamente las placas vehiculares a partir de archivos de imagen generados por cámaras Hikvision con detección ANPR (Automatic Number Plate Recognition).

## Componentes

### 1. Base de Datos

#### Tabla `detected_plates`
Almacena las detecciones de placas:
- `plate_text`: Texto de la placa detectada
- `captured_at`: Fecha y hora de captura
- `confidence`: Nivel de confianza de la detección
- `device_id`: ID del dispositivo que capturó la imagen
- `unit_id`: ID de la unidad asociada
- `is_match`: Si la placa coincide con una unidad registrada

#### Tabla `processed_plate_files`
Registra los archivos ya procesados para evitar duplicados:
- `filename`: Nombre del archivo procesado
- `processed_at`: Fecha y hora de procesamiento

**Migración**: Ejecutar `config/03_create_processed_plate_files.sql`

### 2. Endpoint de Registro

**Ruta**: `/public/api/register_new_plates.php`

**Funcionalidad**:
- Escanea el directorio `/public/Imagenes/`
- Busca archivos con patrón: `{timestamp}_{plate}_VEHICLE_DETECTION_Hik__PLATE.jpg`
- Extrae la placa y timestamp del nombre del archivo
- Inserta en `detected_plates` solo si el archivo no ha sido procesado
- Marca el archivo como procesado en `processed_plate_files`

**Formato del nombre de archivo**:
```
20251110154755988_XYA100F_VEHICLE_DETECTION_Hik__PLATE.jpg
^^^^^^^^^^^^^^^^^  ^^^^^^^
timestamp (14+)    placa
```

**Respuesta JSON**:
```json
{
  "success": true,
  "inserted": 2,
  "message": "Se registraron 2 placas nuevas"
}
```

### 3. Integración Frontend

El endpoint se ejecuta automáticamente cada 10 segundos en la vista de registro de entrada (`/app/views/access/create.php`).

**Flujo**:
1. Ejecuta `mover_ftp_a_public.php` (mueve imágenes desde FTP)
2. Ejecuta `register_new_plates.php` (registra placas nuevas)
3. Se repite cada 10 segundos

## Uso

### Configuración Inicial

1. Ejecutar la migración SQL:
```sql
mysql -u fix360_dunas -p fix360_dunas < config/03_create_processed_plate_files.sql
```

2. Verificar que el directorio `/public/Imagenes/` exista y tenga permisos de escritura:
```bash
chmod 755 public/Imagenes
```

### Verificación

1. Verificar que las imágenes lleguen al directorio:
```bash
ls -la public/Imagenes/*_PLATE.jpg
```

2. Consultar detecciones registradas:
```sql
SELECT * FROM detected_plates ORDER BY captured_at DESC LIMIT 10;
```

3. Verificar archivos procesados:
```sql
SELECT * FROM processed_plate_files ORDER BY processed_at DESC LIMIT 10;
```

### Monitoreo

En la consola del navegador (vista de registro de entrada):
- `🔁 mover_ftp_a_public.php ejecutado correctamente` - Script de mover ejecutado
- `✅ Detectadas/insertadas: N placas` - Placas nuevas registradas (solo si N > 0)
- `⚠️ Error...` - Indica un problema que debe revisarse

## Troubleshooting

### No se registran placas

1. Verificar que las imágenes tengan el formato correcto de nombre
2. Verificar permisos del directorio `/public/Imagenes/`
3. Revisar logs de errores: `tail -f logs/error.log`
4. Verificar conexión a base de datos en `config/config.php`

### Duplicados

El sistema previene duplicados usando `processed_plate_files`. Si necesitas reprocesar:
```sql
DELETE FROM processed_plate_files WHERE filename = 'nombre_archivo.jpg';
```

### Formato de archivo incorrecto

El regex espera exactamente:
- Timestamp: mínimo 14 dígitos (YYYYMMDDHHMMSS)
- Placa: caracteres alfanuméricos (A-Z, 0-9)
- Sufijo fijo: `_VEHICLE_DETECTION_Hik__PLATE.jpg`

Ejemplo válido: `20251110154755988_ABC123_VEHICLE_DETECTION_Hik__PLATE.jpg`

## Consideraciones de Seguridad

- El endpoint no requiere autenticación (diseñado para ser llamado desde el frontend ya autenticado)
- Las imágenes en `/public/Imagenes/` están excluidas del control de versiones
- Los nombres de archivo deben seguir el patrón estricto para ser procesados
- Se recomienda implementar limpieza periódica de imágenes antiguas

## Mantenimiento

### Limpieza de Imágenes Antiguas

Crear un cron job para limpiar imágenes de más de 30 días:
```bash
find /path/to/dunas/public/Imagenes -name "*_PLATE.jpg" -mtime +30 -delete
```

### Limpieza de Registros Procesados

Opcional: limpiar registros antiguos de `processed_plate_files`:
```sql
DELETE FROM processed_plate_files WHERE processed_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```
