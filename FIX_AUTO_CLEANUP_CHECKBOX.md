# Fix: Checkbox de Auto-limpieza no funciona

## Problema
El script de limpieza automática de `detected_plates` se ejecuta siempre, incluso cuando el checkbox "Habilitar limpieza automática de registros" está desactivado en `/settings`.

## Causa
Las vistas `access/index.php` y `access/quick_registration.php` incluyen el script JavaScript `detected-plates-cleanup.js` sin verificar si la funcionalidad está habilitada.

## Solución

### 1. Modificar `/app/views/access/index.php`

Buscar estas líneas (al final del archivo):

```php
<!-- Configuración para limpieza periódica de registros de placas detectadas -->
<script>
window.CLEANUP_CONFIG = {
    intervalMinutes: <?php echo (int)($systemSettings['auto_cleanup_minutes'] ?? 15); ?>,
    url: "<?php echo BASE_URL; ?>/api/cleanup_detected_plates.php",
    viewName: "Access View"
};
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/detected-plates-cleanup.js"></script>
```

**Reemplazar con:**

```php
<!-- Configuración para limpieza periódica de registros de placas detectadas -->
<?php if (isset($systemSettings['auto_cleanup_enabled']) && $systemSettings['auto_cleanup_enabled'] === '1'): ?>
<script>
window.CLEANUP_CONFIG = {
    intervalMinutes: <?php echo (int)($systemSettings['auto_cleanup_minutes'] ?? 15); ?>,
    url: "<?php echo BASE_URL; ?>/api/cleanup_detected_plates.php",
    viewName: "Access View"
};
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/detected-plates-cleanup.js"></script>
<?php else: ?>
<!-- Limpieza automática desactivada en configuración -->
<?php endif; ?>
```

### 2. Modificar `/app/views/access/quick_registration.php`

Buscar estas líneas (al final del archivo):

```php
<!-- Configuración para limpieza periódica de registros de placas detectadas -->
<script>
window.CLEANUP_CONFIG = {
    intervalMinutes: <?php echo (int)($systemSettings['auto_cleanup_minutes'] ?? 15); ?>,
    url: "<?php echo BASE_URL; ?>/api/cleanup_detected_plates.php",
    viewName: "Quick Registration"
};
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/detected-plates-cleanup.js"></script>
```

**Reemplazar con:**

```php
<!-- Configuración para limpieza periódica de registros de placas detectadas -->
<?php if (isset($systemSettings['auto_cleanup_enabled']) && $systemSettings['auto_cleanup_enabled'] === '1'): ?>
<script>
window.CLEANUP_CONFIG = {
    intervalMinutes: <?php echo (int)($systemSettings['auto_cleanup_minutes'] ?? 15); ?>,
    url: "<?php echo BASE_URL; ?>/api/cleanup_detected_plates.php",
    viewName: "Quick Registration"
};
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/detected-plates-cleanup.js"></script>
<?php else: ?>
<!-- Limpieza automática desactivada en configuración -->
<?php endif; ?>
```

### 3. Verificar que los controladores pasen la variable

Asegurarse de que los controladores que renderizan estas vistas incluyan `systemSettings` en los datos:

**AccessController.php** - Métodos `index()` y `quickRegistration()`:

```php
$data = [
    // ... otros datos ...
    'systemSettings' => $settingsModel->getAll()
];
```

## Verificación

1. Ve a `/settings`
2. **Desactiva** el checkbox "Habilitar limpieza automática de registros"
3. Guarda la configuración
4. Ve a `/access` o `/access/quickRegistration`
5. Abre la consola del navegador (F12)
6. **NO** deberías ver mensajes de limpieza cada X minutos
7. Verifica que `window.CLEANUP_CONFIG` sea `undefined` en la consola

Para re-activar:
1. Ve a `/settings`
2. **Activa** el checkbox
3. Guarda
4. Ahora **SÍ** deberías ver los logs de limpieza en la consola

## Código de verificación en consola

```javascript
// Verificar si la limpieza está activa
console.log('Limpieza activa:', typeof window.CLEANUP_CONFIG !== 'undefined');
console.log('Configuración:', window.CLEANUP_CONFIG);
```

## Notas adicionales

- El valor por defecto es **habilitado** (`'1'`) si no existe el setting
- Asegúrate de que el campo hidden en el formulario de settings esté presente para enviar `'0'` cuando está desmarcado:
  ```php
  <input type="hidden" name="auto_cleanup_enabled" value="0">
  <input type="checkbox" name="auto_cleanup_enabled" value="1" ...>
  ```

## Archivos involucrados

- `/app/views/access/index.php`
- `/app/views/access/quick_registration.php`
- `/app/controllers/AccessController.php`
- `/app/views/settings/index.php` (ya tiene el checkbox correcto)
- `/app/controllers/SettingsController.php` (ya guarda correctamente)
- `/public/assets/js/detected-plates-cleanup.js` (no requiere cambios)
