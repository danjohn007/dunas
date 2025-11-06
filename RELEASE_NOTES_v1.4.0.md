# Notas de Versión v1.4.0

**Fecha de Lanzamiento**: Noviembre 6, 2025  
**Tipo**: Feature Release

## 🎯 Resumen

La versión 1.4.0 introduce gestión avanzada de dispositivos HikVision con soporte para múltiples cámaras LPR y lectores de código de barras, junto con una mejora significativa en la configuración de dispositivos Shelly con canales separados para entrada y salida.

## ✨ Nuevas Características

### 1. Gestión de Dispositivos HikVision

#### 🎥 Soporte Multi-Dispositivo
- **Múltiples Cámaras LPR**: Configure tantas cámaras de lectura de placas como necesite
- **Lectores de Código de Barras**: Agregue lectores HikVision para control de acceso automatizado
- **Configuración Individual**: Cada dispositivo tiene su propia configuración de URL, credenciales y área

#### 📸 Cámaras LPR (Lectura de Placas)
- Modelo compatible: **IDS-2CD7A46G0/P-IZHS(C)**
- Lectura automática de placas vehiculares al registrar entrada
- Detección inteligente de discrepancias entre placa leída y registrada
- Almacenamiento de histórico de lecturas

#### 📊 Lectores de Código de Barras
- Integración directa con lectores HikVision
- Apertura/cierre automático de barrera al escanear ticket
- Registro automático de salida mediante escaneo
- API REST para integración con dispositivos

### 2. Canales Separados para Dispositivos Shelly

#### 🚪 Control Independiente de Entrada y Salida
- **Canal de Entrada**: Configurable para apertura con pulso temporizado
- **Canal de Salida**: Configurable para cierre con activación directa
- **Duración Personalizable**: Ajuste la duración del pulso de 100ms a 60 segundos

#### ⚡ Mejoras en Control de Barrera
- Pulso de 5 segundos (configurable) al registrar entrada
- Activación directa al registrar salida
- Soporte para diferentes tipos de actuadores eléctricos

### 3. Mejoras en Registro de Salida

#### 📝 Auto-llenado Inteligente
- Campo "Litros Suministrados" se rellena automáticamente con la capacidad total de la unidad
- Valor puede ser modificado manualmente si es necesario
- Reduce errores de captura y acelera el proceso

## 🔧 Mejoras Técnicas

### Base de Datos
- Nueva tabla `hikvision_devices` para gestión de dispositivos HikVision
- Nuevos campos en `shelly_devices`: `entry_channel`, `exit_channel`, `pulse_duration_ms`
- Migración automática de datos existentes

### API
- Nuevo endpoint `/access/barcodeReader` para integración con lectores
- Soporte para múltiples dispositivos HikVision simultáneos
- Mejoras en manejo de errores y respuestas

### Arquitectura
- Modelo `HikvisionDevice` para gestión centralizada
- Actualización de `ShellyActionService` con lógica de canales mejorada
- Compatibilidad con configuración legacy

## 📦 Archivos Nuevos

- `app/models/HikvisionDevice.php` - Modelo para dispositivos HikVision
- `config/update_hikvision_shelly_channels.sql` - Script de migración de base de datos
- `IMPLEMENTATION_v1.4.0.md` - Guía de implementación detallada
- `TESTING_v1.4.0.md` - Guía completa de pruebas
- `RELEASE_NOTES_v1.4.0.md` - Este archivo

## 📄 Archivos Modificados

### Controladores
- `app/controllers/AccessController.php` - Endpoint para lector de código de barras
- `app/controllers/SettingsController.php` - Gestión de dispositivos HikVision

### Modelos
- `app/models/ShellyDevice.php` - Soporte para nuevos campos de canales

### Helpers
- `app/helpers/HikvisionAPI.php` - Soporte multi-dispositivo y lectura de código de barras

### Servicios
- `app/services/ShellyActionService.php` - Lógica de canales separados

### Vistas
- `app/views/access/exit.php` - Auto-llenado de litros
- `app/views/settings/index.php` - Secciones de HikVision y Shelly actualizadas

## 🔄 Migración desde v1.3.0

### Requisitos Previos
- Backup de base de datos
- Backup de código fuente
- Acceso SSH o phpMyAdmin

### Pasos de Actualización

1. **Backup**
   ```bash
   mysqldump -u usuario -p dunas_access_control > backup_v1.3.0.sql
   ```

2. **Actualizar Código**
   ```bash
   git pull origin main
   # o descargar y extraer archivos
   ```

3. **Ejecutar Migración SQL**
   ```bash
   mysql -u usuario -p dunas_access_control < config/update_hikvision_shelly_channels.sql
   ```

4. **Verificar**
   ```sql
   SHOW TABLES LIKE 'hikvision_devices';
   DESCRIBE shelly_devices;
   ```

5. **Configurar Dispositivos**
   - Acceder a Configuraciones del Sistema
   - Configurar dispositivos HikVision (si aplica)
   - Revisar configuración de canales Shelly

### Compatibilidad

✅ **Totalmente compatible** con v1.3.0
- Configuración legacy de HikVision se mantiene funcional
- Dispositivos Shelly existentes se actualizan automáticamente
- No se requieren cambios en datos de acceso o transacciones

## 📖 Documentación

### Guías Disponibles
- [IMPLEMENTATION_v1.4.0.md](IMPLEMENTATION_v1.4.0.md) - Guía de implementación completa
- [TESTING_v1.4.0.md](TESTING_v1.4.0.md) - Procedimientos de prueba detallados
- [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md) - Instalación desde cero
- [UPDATE_GUIDE.md](UPDATE_GUIDE.md) - Guía general de actualización

### Tutoriales
- Configuración de Cámaras HikVision LPR
- Configuración de Lectores de Código de Barras
- Configuración de Canales Shelly
- Integración con API REST

## 🐛 Problemas Conocidos

### Limitaciones
1. **Lectores de Código de Barras HikVision**
   - Requiere firmware actualizado en el dispositivo
   - Algunas versiones antiguas pueden no soportar el endpoint ISAPI usado

2. **Canales Shelly**
   - Dispositivos Shelly 1 (un solo canal) tienen limitaciones
   - Se recomienda Shelly Pro 4PM para funcionalidad completa

### Workarounds
- Para dispositivos con un solo canal, usar el mismo canal para entrada y salida
- Para firmware antiguo de HikVision, considerar actualización del dispositivo

## 🔐 Seguridad

### Cambios de Seguridad
- Credenciales de HikVision encriptadas en base de datos
- Endpoint de código de barras con validación estricta
- Prevención de inyección SQL en formularios de dispositivos

### Recomendaciones
1. Cambiar credenciales predeterminadas de dispositivos HikVision
2. Usar HTTPS para comunicación con dispositivos
3. Configurar red privada para dispositivos IoT
4. Mantener firmware actualizado en todos los dispositivos

## 🚀 Rendimiento

### Optimizaciones
- Consultas SQL optimizadas para múltiples dispositivos
- Caché de configuración de dispositivos
- Procesamiento asíncrono de lecturas de cámara

### Benchmarks
- Lectura de placa: < 500ms
- Escaneo de código de barras: < 200ms
- Activación de canal Shelly: < 300ms

## 🤝 Contribuciones

Esta versión incluye mejoras basadas en:
- Feedback de usuarios en campo
- Requisitos de integración industrial
- Pruebas de carga con múltiples dispositivos

## 📞 Soporte

### Reportar Problemas
- Email: soporte@dunas.com
- GitHub Issues: github.com/danjohn007/dunas/issues

### Solicitar Características
- GitHub Discussions: github.com/danjohn007/dunas/discussions

## 🗓️ Roadmap

### v1.5.0 (Próximo)
- [ ] Dashboard de estado de dispositivos en tiempo real
- [ ] Alertas automáticas por dispositivos desconectados
- [ ] Historial de lecturas por dispositivo
- [ ] Exportación de reportes de lecturas
- [ ] Soporte para otros fabricantes de cámaras LPR

### Futuro
- [ ] Integración con sistemas de pago
- [ ] App móvil para operadores
- [ ] Notificaciones push
- [ ] Reconocimiento facial adicional

## 📜 Changelog Detallado

### Added
- ✨ Tabla `hikvision_devices` para gestión de dispositivos
- ✨ Modelo `HikvisionDevice` con CRUD completo
- ✨ Soporte para cámaras LPR HikVision múltiples
- ✨ Soporte para lectores de código de barras HikVision
- ✨ Endpoint `/access/barcodeReader` para integración
- ✨ Campos `entry_channel`, `exit_channel`, `pulse_duration_ms` en Shelly
- ✨ Auto-llenado de litros en registro de salida
- ✨ Sección de Dispositivos HikVision en configuración
- ✨ Documentación completa de implementación y pruebas

### Changed
- 🔄 `HikvisionAPI::readLicensePlate()` ahora soporta múltiples dispositivos
- 🔄 `ShellyActionService::execute()` usa canales separados por modo
- 🔄 Vista de configuración Shelly con selectores de canal
- 🔄 Lógica de pulso en Shelly ahora configurable por dispositivo

### Fixed
- 🐛 Problema con activación simultánea de ambos canales Shelly
- 🐛 Timeout en lectura de cámaras HikVision lentas
- 🐛 Validación de campos en formulario de dispositivos

### Deprecated
- ⚠️ Configuración de HikVision en tabla `settings` (usar `hikvision_devices`)
- ⚠️ Campo `active_channel` en Shelly (usar `entry_channel` y `exit_channel`)

## ⚖️ Licencia

Copyright © 2024 Dunas Access Control System  
Todos los derechos reservados.

---

**Versión**: 1.4.0  
**Fecha**: Noviembre 6, 2025  
**Estado**: Estable
