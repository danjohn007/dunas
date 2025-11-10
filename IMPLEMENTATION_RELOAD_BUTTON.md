# Implementación - Función de Botón de Recargar

## Resumen

Se ha implementado exitosamente la funcionalidad del botón "Detectar Placa Nuevamente" para ejecutar automáticamente la sincronización de imágenes FTP antes de detectar placas. Esta mejora permite que el sistema obtenga las imágenes más recientes del servidor FTP antes de realizar la detección ANPR.

## Fecha de Implementación

2025-11-10

## Archivos Creados

### 1. `/public/mover_ftp_a_public.php`
**Propósito:** Script PHP que se conecta a un servidor FTP y descarga imágenes de vehículos.

**Características:**
- Conecta al servidor FTP usando credenciales configurables
- Soporta modo pasivo FTP (compatible con firewalls)
- Filtra archivos usando expresiones regulares
- Solo descarga imágenes que no existen localmente
- Crea automáticamente el directorio de destino
- Registra todas las operaciones en el log de PHP
- Maneja errores de forma elegante sin interrumpir el sistema

**Configuración requerida:**
- `ftp_host`: Servidor FTP
- `ftp_port`: Puerto (predeterminado: 21)
- `ftp_user`: Usuario
- `ftp_pass`: Contraseña
- `ftp_images_path`: Ruta en el servidor FTP
- `ftp_image_pattern`: Patrón de archivos a descargar

### 2. `/public/api/run_mover_ftp.php`
**Propósito:** Endpoint AJAX que ejecuta el script de sincronización FTP.

**Características:**
- Respuesta en formato JSON
- Autenticación requerida (roles: admin, supervisor, operator)
- Ejecuta el script de forma segura usando output buffering
- Retorna estado de éxito/error
- Código HTTP apropiado (401 no autenticado, 403 sin permisos, 500 error)

**Endpoint:** `POST /api/run_mover_ftp.php`

**Respuesta exitosa:**
```json
{
  "success": true
}
```

**Respuesta de error:**
```json
{
  "success": false,
  "error": "Mensaje de error"
}
```

### 3. `/FTP_IMAGE_SYNC_GUIDE.md`
**Propósito:** Documentación completa del sistema de sincronización FTP.

**Contenido:**
- Descripción general de la funcionalidad
- Instrucciones de configuración paso a paso
- Explicación de cada parámetro
- Ejemplos de patrones de archivos
- Consideraciones de seguridad
- Guía de solución de problemas
- Recomendaciones de mantenimiento

### 4. `/config/migrations/add_ftp_settings.sql`
**Propósito:** Script SQL para agregar configuración FTP a la base de datos.

**Características:**
- Inserta todas las configuraciones necesarias
- Usa `ON DUPLICATE KEY UPDATE` para ser idempotente
- Incluye comentarios explicativos
- Incluye query de verificación

**Uso:**
```bash
mysql -u root -p dunas_access_control < config/migrations/add_ftp_settings.sql
```

## Archivos Modificados

### 1. `/app/views/access/create.php`

**Cambios en el JavaScript:**

#### Antes:
```javascript
// Botón refrescar detección
document.getElementById('refreshDetectionBtn').addEventListener('click', async function() {
    await loadPlateDetection();
});
```

#### Después:
```javascript
// Botón refrescar detección
document.getElementById('refreshDetectionBtn').addEventListener('click', async function(e) {
    e.preventDefault();
    const btn = this;
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Ejecutando...';
    
    try {
        // 1) Ejecutar el mover del FTP
        const ftpResponse = await fetch(BASE_URL + '/api/run_mover_ftp.php', {
            method: 'POST',
            headers: { 'Accept': 'application/json' }
        });
        const ftpData = await ftpResponse.json();
        
        if (!ftpData.success) {
            throw new Error(ftpData.error || 'Fallo al mover imágenes');
        }
        
        // 2) Lanzar la validación que ya teníamos
        await loadPlateDetection();
        
    } catch (err) {
        console.error('Error en el proceso:', err);
        const detectedPlateEl = document.getElementById('detectedPlate');
        const detectionInfoEl = document.getElementById('detectionInfo');
        if (detectedPlateEl) {
            detectedPlateEl.innerHTML = '<span class="text-red-500">Error</span>';
        }
        if (detectionInfoEl) {
            detectionInfoEl.textContent = err.message || 'No se pudo completar la operación';
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});
```

**Mejoras implementadas:**
- Prevención de comportamiento predeterminado con `e.preventDefault()`
- Deshabilita el botón durante la operación
- Muestra spinner de carga personalizado
- Encadena dos operaciones: FTP sync → detección de placa
- Manejo robusto de errores
- Restaura el estado del botón al finalizar

## Flujo de Operación

### Secuencia al hacer clic en "Detectar Placa Nuevamente":

1. **Inicio**
   - Usuario hace clic en el botón
   - Botón se deshabilita
   - Muestra "Ejecutando..." con spinner

2. **Fase 1: Sincronización FTP**
   - Llama a `/api/run_mover_ftp.php`
   - Endpoint verifica autenticación y permisos
   - Ejecuta `/public/mover_ftp_a_public.php`
   - Script se conecta al FTP
   - Descarga imágenes nuevas
   - Retorna `success: true`

3. **Fase 2: Detección de Placa**
   - Llama a `loadPlateDetection()`
   - Consulta `/api/anpr/latest.php`
   - Obtiene última detección ANPR
   - Actualiza UI con la placa detectada
   - Compara con placa guardada
   - Muestra resultado de comparación

4. **Finalización**
   - Habilita el botón
   - Restaura texto original del botón
   - Muestra resultado de la detección

### Diagrama de Flujo

```
[Usuario hace clic] 
        ↓
[Deshabilitar botón + Spinner]
        ↓
[POST /api/run_mover_ftp.php]
        ↓
[Ejecutar mover_ftp_a_public.php]
        ↓
[Conectar FTP y descargar imágenes]
        ↓
[Retornar success: true]
        ↓
[Llamar loadPlateDetection()]
        ↓
[GET /api/anpr/latest.php]
        ↓
[Obtener última detección ANPR]
        ↓
[Actualizar UI con resultado]
        ↓
[Habilitar botón]
```

## Compatibilidad

- **PHP:** 7.4 o superior
- **MySQL:** 5.7 o superior
- **Extensiones PHP requeridas:** 
  - ftp (para sincronización FTP)
  - curl (ya requerida por el sistema)
  - json (ya requerida por el sistema)

## Seguridad

### Autenticación y Autorización
- Endpoint `/api/run_mover_ftp.php` requiere sesión activa
- Solo roles autorizados: admin, supervisor, operator
- Respuestas HTTP apropiadas: 401 (no autenticado), 403 (sin permisos)

### Conexión FTP
- Credenciales almacenadas en base de datos (tabla settings)
- Modo pasivo habilitado por defecto
- Timeout de conexión configurado (10 segundos)
- Logs de todas las operaciones FTP

### Validación de Archivos
- Filtrado por patrón regex configurable
- Solo descarga archivos que coincidan con el patrón
- Verificación de existencia local antes de descargar

## Pruebas Recomendadas

### 1. Prueba de Configuración Incorrecta
**Escenario:** No hay configuración FTP
**Resultado esperado:** Sistema continúa funcionando sin FTP, no genera errores críticos

### 2. Prueba de Conexión FTP Fallida
**Escenario:** Servidor FTP inalcanzable
**Resultado esperado:** Endpoint retorna error, UI muestra mensaje apropiado

### 3. Prueba de Sincronización Exitosa
**Escenario:** FTP configurado correctamente con imágenes nuevas
**Resultado esperado:** Imágenes descargadas a `/public/uploads/anpr/`, detección funciona

### 4. Prueba de Permisos
**Escenario:** Usuario sin rol autorizado intenta ejecutar
**Resultado esperado:** HTTP 403, mensaje de acceso denegado

### 5. Prueba de Flujo Completo
**Escenario:** Subir imagen al FTP, hacer clic en botón
**Resultado esperado:** 
1. Imagen descargada
2. Placa detectada
3. Comparación mostrada

## Mantenimiento

### Logs
Revisar logs de PHP para monitorear:
```bash
tail -f /var/log/php/error.log | grep "mover_ftp"
```

### Limpieza de Imágenes
Configurar tarea cron para eliminar imágenes antiguas:
```bash
# Ejecutar diariamente a las 2 AM
0 2 * * * find /var/www/html/public/uploads/anpr -type f -mtime +30 -delete
```

### Monitoreo de Espacio en Disco
Verificar periódicamente el uso de espacio:
```bash
du -sh /var/www/html/public/uploads/anpr
```

## Notas de Implementación

1. **Retrocompatibilidad:** La función loadPlateDetection() existente no fue modificada significativamente, solo se eliminó la lógica de deshabilitación del botón que ahora está en el event handler.

2. **Graceful Degradation:** Si no hay configuración FTP, el sistema continúa funcionando normalmente sin sincronización.

3. **Idempotencia:** La sincronización es idempotente - ejecutar múltiples veces no descarga duplicados.

4. **Performance:** Solo se descargan archivos nuevos, optimizando el uso de ancho de banda.

5. **Extensibilidad:** El patrón de archivos es configurable, permitiendo adaptarse a diferentes nomenclaturas.

## Próximos Pasos

1. **Configurar FTP:** Ejecutar la migración SQL y configurar los valores apropiados
2. **Probar conexión:** Verificar conectividad con el servidor FTP
3. **Subir imágenes de prueba:** Cargar imágenes al FTP con el patrón correcto
4. **Probar funcionalidad:** Hacer clic en "Detectar Placa Nuevamente" y verificar
5. **Monitorear logs:** Verificar que no hay errores en los logs de PHP
6. **Ajustar parámetros:** Optimizar timeout, patrón de archivos según necesidad

## Soporte

Para preguntas o problemas:
- Consultar `FTP_IMAGE_SYNC_GUIDE.md` para documentación detallada
- Revisar logs de PHP: `/var/log/php/error.log`
- Verificar configuración en tabla `settings`
- Probar conexión FTP con cliente externo (FileZilla, etc.)

## Referencias

- Issue original: "Nueva función de boton de recargar"
- Documentación completa: `FTP_IMAGE_SYNC_GUIDE.md`
- Migración SQL: `config/migrations/add_ftp_settings.sql`
