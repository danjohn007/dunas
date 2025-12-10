# Configuración FTP para HikVision - Guía de Implementación

## Resumen

Se ha implementado una funcionalidad que permite configurar las rutas de origen y destino del FTP de HikVision directamente desde la interfaz de **Settings**, sin necesidad de editar código.

## Cambios Realizados

### 1. Script SQL de Migración
**Archivo:** `config/update_ftp_settings.sql`

Este script agrega dos nuevas configuraciones a la tabla `settings`:
- `ftp_source_dir`: Ruta donde llegan las imágenes desde los dispositivos HikVision por FTP
- `ftp_destination_dir`: Ruta pública donde se mueven las imágenes para ser accesibles

**Ejecución:**
```sql
mysql -u residenc_dunas -p residenc_dunas < config/update_ftp_settings.sql
```

### 2. Interfaz de Configuración
**Archivo:** `app/views/settings/index.php`

Se agregó una nueva sección llamada **"Configuración FTP HikVision"** que incluye:
- Campo para la carpeta origen (FTP)
- Campo para la carpeta destino (pública)
- Validación de campos requeridos
- Información de ayuda con instrucciones

La sección se encuentra después de "Tiempo para limpiar registro automático" y antes de los botones de guardar.

### 3. Script de Movimiento de Imágenes
**Archivo:** `imagenes/mover_ftp_a_public.php`

El script fue modificado para:
- Leer las rutas desde la base de datos en lugar de tenerlas hardcodeadas
- Incluir manejo de errores robusto
- Usar valores por defecto si no se puede conectar a la BD
- Mostrar rutas específicas en mensajes de error para facilitar diagnóstico

## Cómo Usar

### Configuración Inicial

1. **Ejecutar el script SQL:**
   ```bash
   mysql -u residenc_dunas -p residenc_dunas < config/update_ftp_settings.sql
   ```

2. **Acceder a Settings:**
   - Ingresar al sistema con credenciales de administrador
   - Navegar a la sección **Settings**
   - Buscar la sección **"Configuración FTP HikVision"**

3. **Configurar las rutas:**
   - **Carpeta Origen (FTP):** Ruta absoluta donde los dispositivos HikVision depositan las imágenes por FTP
     - Ejemplo: `/home2/residencial/placas/`
   
   - **Carpeta Destino (Público):** Ruta absoluta de la carpeta pública accesible por el sistema
     - Ejemplo: `/home2/residencial/public_html/dunas/imagenes/`

4. **Guardar cambios:**
   - Hacer clic en el botón **"Guardar Configuraciones"**

### Validaciones

El sistema validará automáticamente que:
- Ambas rutas sean proporcionadas (campos requeridos)
- Las rutas finalicen con `/`
- Las carpetas existan cuando se ejecute el script de movimiento

### Funcionamiento del Script

El script `mover_ftp_a_public.php`:
1. Lee las rutas configuradas desde la base de datos
2. Verifica que ambas carpetas existan
3. Escanea archivos en la carpeta origen
4. Mueve solo imágenes (`.jpg`, `.jpeg`, `.png`)
5. Retorna un JSON con el resultado:
   ```json
   {
       "success": true,
       "movidos": 5,
       "errores": 0,
       "detalles": [...]
   }
   ```

## Ventajas

✅ **No requiere editar código** para cambiar las rutas FTP  
✅ **Interfaz amigable** para administradores  
✅ **Configuración centralizada** en la base de datos  
✅ **Validación automática** de rutas  
✅ **Manejo de errores robusto** con valores por defecto  
✅ **Documentación integrada** con tooltips informativos  

## Requisitos Técnicos

- Las carpetas deben tener permisos de lectura/escritura para el usuario del servidor web
- Las rutas deben ser absolutas en el sistema de archivos
- La tabla `settings` debe existir con la estructura correcta

## Solución de Problemas

### Error: "La carpeta origen no existe"
- Verificar que la ruta en Settings sea correcta
- Confirmar que la carpeta existe en el servidor
- Verificar permisos de lectura

### Error: "La carpeta destino no existe"
- Verificar que la ruta en Settings sea correcta
- Confirmar que la carpeta existe en el servidor
- Verificar permisos de escritura

### Las imágenes no se mueven
- Revisar que el script `mover_ftp_a_public.php` se esté ejecutando
- Verificar que haya imágenes en la carpeta origen
- Revisar el log de errores del servidor

## Notas de Implementación

- El sistema usa valores por defecto si no puede conectar a la base de datos
- Solo procesa archivos con extensiones de imagen válidas
- El script retorna códigos HTTP apropiados (200 para éxito, 500 para errores)
- Los detalles de cada archivo movido se incluyen en la respuesta JSON

## Próximos Pasos (Opcional)

- Agregar un botón "Probar Conexión" para validar rutas antes de guardar
- Implementar logs de movimientos en base de datos
- Agregar configuración de extensiones permitidas desde Settings
- Crear una interfaz para ver el historial de movimientos
