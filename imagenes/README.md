# Directorio de Imágenes de Detección de Placas

Este directorio se genera automáticamente cuando el sistema detecta vehículos y placas.

## Configuración

El sistema guarda automáticamente las imágenes de:
- Vehículos detectados
- Placas detectadas

## Ubicación

Las imágenes se guardan en:
- `/imagenes/` (este directorio)
- `/public/Imagenes/` (para acceso web)

## Mantenimiento

Se recomienda:
1. Limpiar periódicamente imágenes antiguas para ahorrar espacio
2. Configurar rotación automática de logs
3. Mantener solo imágenes necesarias para auditoría

## Nota Importante

**Este directorio NO debe incluirse en el control de versiones (git).**

Las imágenes son datos de producción y pueden ocupar mucho espacio. 
Están excluidas en `.gitignore` para evitar:
- Repositorio muy grande
- Descargas lentas
- Problemas al clonar/descargar el proyecto

## Respaldo

Para respaldar imágenes:
```bash
# Comprimir imágenes por fecha
tar -czf imagenes_backup_$(date +%Y%m%d).tar.gz imagenes/

# O copiar a otro servidor
rsync -av imagenes/ user@backup-server:/ruta/imagenes/
```
