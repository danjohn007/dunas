# Guía de Configuración Multi-Dispositivo Shelly

## Descripción General

El sistema ahora soporta múltiples dispositivos Shelly Cloud con configuración independiente de canales y acciones extensibles. Cada dispositivo puede controlar hasta 4 canales (relays) y ejecutar diferentes tipos de acciones.

## Migración desde el Sistema Anterior

### Paso 1: Ejecutar la Migración SQL

Ejecuta el script de migración en tu base de datos:

```bash
mysql -u [usuario] -p [nombre_base_datos] < config/update_20250114_shelly_multi.sql
```

Este script:
- Crea las tablas `shelly_devices` y `shelly_actions`
- Migra automáticamente la configuración existente desde la tabla `settings` (si existe)
- Crea una acción por defecto "Abrir/Cerrar" para el dispositivo migrado

### Paso 2: Verificar la Migración

1. Accede a **Configuraciones del Sistema** desde el menú principal
2. Desplázate hasta la sección **"Dispositivos Shelly Cloud"**
3. Deberías ver tu dispositivo existente configurado con:
   - Token de autenticación
   - Device ID
   - Servidor Cloud
   - Canal activo: 0 (por defecto)
   - Acción: "Abrir/Cerrar"

## Configuración de Dispositivos

### Agregar un Nuevo Dispositivo

1. En la sección "Dispositivos Shelly Cloud", haz clic en **"Nuevo dispositivo +"**
2. Se mostrará una nueva tarjeta con los siguientes campos:

#### Campos Requeridos:

- **Token de Autenticación**: Token de acceso al Shelly Cloud API
  - El ícono de ojo permite mostrar/ocultar el token
  - Obtén este token desde la app Shelly Cloud o el panel web
  
- **Device ID**: ID único del dispositivo Shelly
  - Ejemplo: `34987A67DA6C`
  - Lo encuentras en la configuración del dispositivo en Shelly Cloud
  
- **Servidor Cloud**: Host del servidor Shelly Cloud
  - Ejemplo: `shelly-208-eu.shelly.cloud`
  - **Importante**: Sin `https://` ni puerto
  - Usa el servidor correspondiente a tu región

#### Configuración de Canal:

- **Puerto activo**: Selecciona el canal (relay) que controlará este dispositivo
  - Opciones: 0, 1, 2, 3
  - Cada Shelly Pro 4PM tiene 4 canales independientes
  - Algunos modelos tienen solo 2 canales

#### Configuración de Acción:

- **Nombre**: Tipo de acción que realizará el dispositivo
  - **Abrir/Cerrar**: Toggle - Alterna entre encendido/apagado según el contexto
  - **Vacío**: Off - No realiza ninguna acción (placeholder para futuras funciones)

#### Opciones Adicionales:

- **Dispositivo habilitado**: Marca/desmarca para habilitar/deshabilitar el dispositivo
  - Dispositivos deshabilitados no se usan en operaciones

### Editar un Dispositivo Existente

1. Localiza la tarjeta del dispositivo que deseas editar
2. Modifica los campos necesarios
3. Haz clic en **"Guardar Dispositivos Shelly"**

### Eliminar un Dispositivo

1. Haz clic en el botón **X** (rojo) en la esquina superior derecha de la tarjeta
2. Confirma la eliminación en el diálogo
3. Haz clic en **"Guardar Dispositivos Shelly"** para aplicar los cambios

## Uso del Sistema

### Control de Acceso (Entrada/Salida)

El sistema utiliza automáticamente el primer dispositivo habilitado con la acción "abrir_cerrar" configurada como predeterminada:

- **Entrada de vehículo**: Se ejecuta `relayTurnOff(channel)` (abre barrera)
- **Salida de vehículo**: Se ejecuta `relayTurnOn(channel)` (cierra barrera)

El canal utilizado es el configurado en "Puerto activo" para ese dispositivo.

### Control Manual de Barrera

Los operadores pueden controlar la barrera manualmente desde la interfaz:
- Botón **"Abrir Barrera"**: Ejecuta acción de apertura
- Botón **"Cerrar Barrera"**: Ejecuta acción de cierre

## Arquitectura Extensible

### Tipos de Acciones Soportadas

El sistema está diseñado para soportar diferentes tipos de acciones:

1. **toggle**: Alterna estado según el contexto (open/close)
2. **on**: Siempre enciende el relay
3. **off**: Siempre apaga el relay
4. **pulse**: Enciende, espera X milisegundos, y apaga

### Agregar Nuevas Acciones (Desarrollo Futuro)

Para agregar una nueva acción personalizada:

1. **En la UI**: Agrega la opción al select de "Nombre" en `app/views/settings/index.php`
2. **En el controlador**: Modifica `saveShellyDevices()` para manejar el nuevo código de acción
3. **En la base de datos**: Inserta el registro en `shelly_actions` con:
   - `code`: Identificador único (ej: 'abrir_puerta')
   - `label`: Nombre descriptivo (ej: 'Abrir Puerta')
   - `action_kind`: 'toggle', 'on', 'off', o 'pulse'
   - `channel`: Canal a usar
   - `duration_ms`: Duración para acciones tipo 'pulse'
4. **En el código**: Invoca la acción con:
   ```php
   ShellyActionService::execute($db, 'abrir_puerta', 'open');
   ```

### Ejemplo: Agregar Acción de Pulso

```php
// En el controlador de settings
ShellyAction::upsertForDevice($db, $deviceId, [[
    'code' => 'abrir_puerta',
    'label' => 'Abrir Puerta',
    'action_kind' => 'pulse',
    'channel' => 1,
    'duration_ms' => 800,  // 800ms de pulso
    'is_default' => 1
]]);

// Para usarlo desde cualquier controlador
require_once APP_PATH . '/services/ShellyActionService.php';
$db = Database::getInstance();
ShellyActionService::execute($db, 'abrir_puerta', 'open');
```

## Compatibilidad hacia Atrás

El sistema mantiene compatibilidad con el código existente:

- Los métodos estáticos `ShellyAPI::openBarrier()` y `ShellyAPI::closeBarrier()` siguen funcionando
- Usan la configuración de `config/config.php` si no hay dispositivos en la base de datos
- El AccessController tiene fallback automático al sistema legacy si falla el nuevo sistema

## Solución de Problemas

### El dispositivo no responde

1. Verifica que el dispositivo esté **habilitado** en la configuración
2. Comprueba que el **Token de Autenticación** sea correcto
3. Verifica que el **Device ID** corresponda al dispositivo
4. Confirma que el **Servidor Cloud** sea el correcto para tu región
5. Revisa los logs del sistema para errores específicos

### "Acción Shelly no configurada"

Este error indica que no hay dispositivos con la acción solicitada:

1. Ve a **Configuraciones → Dispositivos Shelly Cloud**
2. Verifica que haya al menos un dispositivo habilitado
3. Asegúrate de que el dispositivo tenga una acción "Abrir/Cerrar"
4. Haz clic en **"Guardar Dispositivos Shelly"**

### Canal incorrecto

Si el relay incorrecto se activa:

1. Verifica el **Puerto activo** en la configuración del dispositivo
2. Confirma que el canal coincida con el cableado físico del Shelly
3. Guarda los cambios y prueba nuevamente

## Seguridad

- Los tokens de autenticación se muestran como contraseña (ocultos) por defecto
- Usa el botón de ojo para mostrar/ocultar el token cuando sea necesario
- Se recomienda usar HTTPS en producción para proteger las credenciales
- Configura `session.cookie_secure=1` en el servidor de producción

## Logs y Depuración

Los logs del sistema registran:
- Intentos de conexión a Shelly Cloud
- Errores de autenticación o timeout
- Dispositivos utilizados en cada operación
- Canal y acción ejecutada

Revisa `/logs/` para más información sobre operaciones del sistema.
