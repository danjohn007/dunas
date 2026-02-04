# Módulo de Generación de Vales

## Descripción General

El módulo de vales permite generar y gestionar vales de suministro de agua con códigos QR únicos que pueden ser escaneados por el lector HikVision DS-K1T502DBWX para control de acceso y registro de transacciones.

## Características Principales

### 1. Generación de Vales en Lote
- **Serie**: Identificador alfabético del lote (máximo 10 caracteres)
- **Folio Inicial**: Número de inicio para la numeración consecutiva
- **Cantidad**: Número de vales a generar (máximo 1000 por lote)
- **Capacidad**: Litros de agua que representa cada vale

### 2. Códigos QR Únicos
- Cada vale genera un código QR único con el formato: `SERIE-FOLIO-TIMESTAMP`
- Los códigos QR son legibles por dispositivos HikVision y escáneres estándar
- El sistema valida que no existan duplicados antes de generar

### 3. Estados del Vale
- **Activo**: Vale disponible para usar
- **Usado**: Vale ya consumido (no reutilizable)
- **Cancelado**: Vale anulado por el administrador

### 4. Control de Uso
- Validación en tiempo real al escanear
- Marcado automático como "usado" al registrar acceso
- Trazabilidad completa (quién lo usó, cuándo, en qué acceso)

## Flujo de Trabajo

### Generación de Vales

1. **Acceder al módulo**: Menú lateral → Vales
2. **Generar nuevo lote**: Clic en "Generar Vales"
3. **Completar formulario**:
   - Serie: Por ejemplo "R", "A", "ABC"
   - Folio inicial: Por ejemplo 1, 501, 1001
   - Cantidad: Número de vales a generar
   - Capacidad: Litros por vale (ej: 10000)
4. **Vista previa**: El sistema muestra el rango de vales a generar
5. **Confirmar**: Al confirmar, se generan todos los vales
6. **Impresión**: Automáticamente se abre la vista de impresión

### Impresión de Vales

- **Formato**: 1/2 tamaño carta (5.5" x 4.25")
- **Layout**: 2 vales por página
- **Contenido de cada vale**:
  - Título "SUMINISTRO DE AGUA"
  - Campos para: Empresa, Operador, Placas, Capacidad, Teléfono, Fecha, Hora de carga
  - Código QR legible
  - Serie y Folio en formato destacado
  - Pie de página "AGUA DE SERVICIOS"

### Uso de Vales en el Sistema

#### Registro de Acceso con Vale

1. **Formulario de Registro de Entrada** o **Registro Rápido**
2. **Seleccionar método de pago**: "Vales"
3. **Aparece campo de validación de vale**
4. **Escanear o ingresar código QR del vale**
5. **Clic en "Validar"**
6. **Sistema verifica**:
   - Que el vale exista
   - Que esté activo (no usado ni cancelado)
   - Muestra información del vale (serie, folio, capacidad)
7. **Completar registro**: Al guardar, el vale se marca automáticamente como "usado"

## Gestión de Vales

### Listado de Vales

- Vista de todos los vales generados
- Filtros por:
  - Serie
  - Estado (activo, usado, cancelado)
  - Búsqueda por código QR o folio
- Estadísticas en tiempo real:
  - Total de vales
  - Vales activos
  - Vales usados
  - Capacidad total activa (litros)

### Detalle de Vale

- Información completa del vale
- Código QR visualizado
- Historial de uso (si aplica)
- Opciones:
  - Imprimir vale individual
  - Cancelar vale (solo si está activo)

### Cancelación de Vales

- Solo administradores y supervisores
- Solo vales en estado "activo"
- Acción irreversible
- Útil para vales extraviados o erróneos

## Integración con HikVision

### Lectura Directa por Dispositivo

El lector HikVision DS-K1T502DBWX puede escanear directamente el código QR del vale impreso. Para configurar:

1. **Configuración del Dispositivo**:
   - El dispositivo debe estar configurado en modo QR
   - Nivel de corrección de error: Alto (H)
   - Formato soportado: Alfanumérico

2. **Endpoint de Validación**:
   - URL: `/vouchers/validateQR`
   - Método: POST
   - Parámetro: `qr_code`
   - Respuesta JSON con información del vale

3. **Respuesta Exitosa**:
```json
{
  "success": true,
  "voucher": {
    "id": 123,
    "serie": "R",
    "folio": 501,
    "capacity": 10000,
    "qr_code": "R-000501-1738694876",
    "status": "active"
  }
}
```

4. **Respuesta con Error**:
```json
{
  "success": false,
  "message": "Este vale ya fue utilizado",
  "voucher": {
    "serie": "R",
    "folio": 501,
    "status": "used",
    "used_at": "2026-02-04 15:30:00"
  }
}
```

## Base de Datos

### Tabla: `vouchers`

```sql
CREATE TABLE `vouchers` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `serie` varchar(10) NOT NULL,
  `folio` int(11) NOT NULL,
  `qr_code` varchar(50) UNIQUE NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','used','cancelled') NOT NULL DEFAULT 'active',
  `used_at` datetime DEFAULT NULL,
  `used_by_access_log_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_qr_code` (`qr_code`),
  KEY `idx_serie_folio_status` (`serie`,`folio`,`status`),
  KEY `idx_status` (`status`)
);
```

### Migración

Para instalar el módulo en una base de datos existente:

```bash
mysql -u usuario -p base_de_datos < config/update_vouchers_module.sql
```

## Permisos y Roles

### Administrador
- Ver todos los vales
- Generar vales
- Cancelar vales
- Imprimir vales

### Supervisor
- Ver todos los vales
- Generar vales
- Cancelar vales
- Imprimir vales

### Operador
- Ver todos los vales
- Validar vales en registro de acceso
- No puede generar ni cancelar

## Mejores Prácticas

### Numeración de Series
- Use series cortas y memorables: "R", "A", "B", "ABC"
- Mantenga consistencia en la nomenclatura
- Ejemplo: Serie "R" para vales regulares, "E" para especiales

### Folios
- Comience desde 1 o en rangos organizados (1000, 2000, etc.)
- Evite saltos grandes entre lotes de la misma serie
- Mantenga registro manual de los rangos asignados

### Impresión
- Use papel de buena calidad para durabilidad
- Verifique la impresión del código QR antes de distribuir
- Pruebe un vale de cada lote con el lector

### Seguridad
- Cancele inmediatamente vales extraviados
- Revise periódicamente el reporte de vales usados
- Mantenga control físico de los vales impresos

## Troubleshooting

### El código QR no se escanea
- Verifique que el código QR esté impreso claramente
- Asegúrese que no hay manchas o dobleces
- Intente con el lector a diferente distancia/ángulo
- Verifique la configuración del lector HikVision

### Vale marcado como duplicado
- Verifique en el listado de vales si ya existe
- Si es un error, use una serie o folio diferente
- No intente forzar la creación

### Vale no válido al escanear
- Verifique el estado del vale en el sistema
- Confirme que el código QR corresponda al vale correcto
- Verifique la conexión del lector con el sistema

## Soporte Técnico

Para problemas con el módulo de vales:
1. Revise los logs del sistema en `/logs`
2. Verifique la consola del navegador (F12) para errores JavaScript
3. Confirme que la tabla `vouchers` existe en la base de datos
4. Verifique permisos del usuario actual

## Actualización

Este módulo fue añadido en la versión 1.9.0 del sistema.

**Fecha de implementación**: Febrero 2026
**Autor**: Sistema de Control con IoT
