# Módulo de Generación de Vales

## Descripción

Este módulo permite la generación, gestión y validación de vales de agua para el sistema de control de acceso. Los vales generados incluyen códigos QR únicos compatibles con el dispositivo lector HikVision DS-K1T502DBWX.

## Características

### 1. Generación de Vales
- Generación masiva de vales consecutivos (hasta 1000 a la vez)
- Personalización de:
  - **Serie**: Identificador alfabético (ej: R, A, B)
  - **Folio**: Número inicial y cantidad
  - **Capacidad**: Litros de agua por vale
- Validación de unicidad (no se permiten duplicados)
- Códigos QR únicos generados automáticamente

### 2. Impresión de Vales
- Formato: 1/4 de tamaño carta (5.5" x 4.25")
- Diseño basado en la imagen de referencia
- Incluye:
  - Serie y folio
  - Campos para datos del operador
  - Código QR escaneable
  - Diseño profesional listo para imprimir

### 3. Gestión de Vales
- Listado completo con filtros
- Estados: Activo, Usado, Cancelado
- Vista de detalles con QR visible
- Estadísticas en tiempo real
- Cancelación de vales activos

### 4. Validación en Acceso
- Integración con sistema de acceso
- Validación automática al escanear QR
- Marcado automático como "usado"
- Prevención de reutilización
- Compatible con entrada rápida y entrada manual

## Instalación

### 1. Ejecutar Migración de Base de Datos

```bash
cd /home/runner/work/dunas/dunas
php run_voucher_migration.php
```

La migración creará la tabla `vouchers` con la siguiente estructura:
- `id`: ID único del vale
- `serie`: Serie alfabética
- `folio`: Número de folio
- `voucher_code`: Código completo (SERIE-FOLIO)
- `capacity_liters`: Capacidad en litros
- `qr_code`: Código QR único
- `status`: Estado (active, used, cancelled)
- `used_at`: Fecha de uso
- `used_by_access_log_id`: Referencia al acceso donde se usó
- `created_by`: Usuario que creó el vale

### 2. Verificar Permisos

El módulo está disponible para usuarios con roles:
- **Admin**: Acceso completo
- **Supervisor**: Acceso completo
- **Operator**: Solo visualización

## Uso

### Generar Vales

1. Ir a **Vales** en el menú lateral
2. Clic en **Generar Vales**
3. Completar el formulario:
   - Serie (ej: R)
   - Folio inicial (ej: 1)
   - Cantidad (ej: 100)
   - Capacidad en litros (ej: 10000)
4. Clic en **Generar Vales**
5. Opcionalmente imprimir los vales generados

### Usar un Vale en el Sistema de Acceso

#### Opción 1: Registro Rápido
1. Ir a **Accesos** → **Registro Rápido**
2. Seleccionar **Vales** como método de pago
3. Escanear el código QR del vale o escribir el código manualmente
4. El sistema validará automáticamente el vale
5. Continuar con el registro normal

#### Opción 2: Registro Manual
1. Ir a **Accesos** → **Nueva Entrada**
2. Completar los datos del acceso
3. Seleccionar **Vales** como método de pago
4. Escanear o ingresar el código del vale
5. Generar ticket

### Formato del Código QR

Los vales generan códigos QR en el formato:
```
VALE:SERIE-FOLIO:CAPACIDAD_LITERS
```

Ejemplo:
```
VALE:R-0001:10000L
```

El sistema acepta:
- Código QR completo: `VALE:R-0001:10000L`
- Código de vale: `R-0001`
- Serie y folio separados

## Integración con HikVision DS-K1T502DBWX

Los códigos QR generados son compatibles con el dispositivo lector HikVision DS-K1T502DBWX:

1. **Nivel de corrección**: High (H) - permite lectura incluso con daño parcial
2. **Formato**: UTF-8
3. **Tamaño**: 115x115 píxeles en impresión
4. **Versión QR**: Automática según longitud del contenido

### Configuración del Dispositivo

El dispositivo debe estar configurado para:
- Leer códigos QR
- Enviar el contenido al sistema vía API
- El endpoint de validación es: `/vouchers/validate`

## API

### POST /vouchers/validate

Valida un vale por su código QR.

**Parámetros:**
```json
{
  "qr_code": "VALE:R-0001:10000L"
}
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Vale válido",
  "voucher": {
    "id": 1,
    "serie": "R",
    "folio": 1,
    "voucher_code": "R-0001",
    "capacity_liters": 10000,
    "status": "active"
  }
}
```

**Respuesta de error:**
```json
{
  "success": false,
  "message": "Este vale ya ha sido utilizado",
  "voucher": null
}
```

## Archivos Creados/Modificados

### Nuevos Archivos
- `migrations/add_vouchers_table.sql` - Migración de base de datos
- `app/models/Voucher.php` - Modelo de vales
- `app/controllers/VoucherController.php` - Controlador de vales
- `app/views/vouchers/index.php` - Listado de vales
- `app/views/vouchers/create.php` - Generación de vales
- `app/views/vouchers/view.php` - Detalle de vale
- `app/views/vouchers/print.php` - Impresión de vales
- `run_voucher_migration.php` - Script de migración
- `VOUCHER_MODULE_README.md` - Esta documentación

### Archivos Modificados
- `public/index.php` - Agregada ruta de vouchers
- `app/views/layouts/main.php` - Agregado menú de vales
- `app/controllers/AccessController.php` - Integración con vales
- `app/views/access/quick_registration.php` - Selección de vales
- `app/views/access/create.php` - Selección de vales

## Seguridad

- Los vales no pueden ser duplicados (restricción UNIQUE en base de datos)
- Los vales usados no pueden volver a utilizarse
- Validación de permisos por rol de usuario
- Todos los cambios quedan registrados con usuario y fecha
- Los vales cancelados no pueden ser activados nuevamente

## Estadísticas

El módulo proporciona estadísticas en tiempo real:
- Total de vales generados
- Vales activos disponibles
- Vales usados
- Litros totales disponibles en vales activos

## Soporte

Para problemas o dudas sobre el módulo de vales, contactar al administrador del sistema.
