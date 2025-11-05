# Guía de Despliegue - Ejecución Simultánea y Campo Área

## Pre-Despliegue

### 1. Backup de Base de Datos ⚠️
```bash
# Crear backup completo
mysqldump -u [user] -p [database] > backup_before_simultaneous_$(date +%Y%m%d_%H%M%S).sql

# Verificar backup
ls -lh backup_before_simultaneous_*.sql
```

### 2. Verificar Versión Actual
```bash
# Verificar rama actual
git branch

# Ver últimos commits
git log --oneline -5
```

### 3. Revisar Documentación
- [ ] Leer `IMPLEMENTATION_SIMULTANEOUS.md`
- [ ] Revisar `TESTING_SIMULTANEOUS.md`
- [ ] Entender `SUMMARY_SIMULTANEOUS_FEATURE.md`

---

## Despliegue

### Paso 1: Actualizar Código
```bash
# En el servidor de producción/staging
cd /path/to/dunas
git fetch origin
git checkout copilot/update-settings-ui-action-area
git pull origin copilot/update-settings-ui-action-area
```

### Paso 2: Aplicar Migración de Base de Datos
```bash
# Conectar a MySQL
mysql -u [user] -p [database]
```

```sql
-- En MySQL
SOURCE config/update_area_simultaneous.sql;

-- Verificar columnas agregadas
DESCRIBE shelly_devices;
```

### Paso 3: Limpiar Cache
```bash
# Reiniciar PHP-FPM
sudo systemctl restart php-fpm

# O reiniciar Apache
sudo systemctl restart apache2
```

---

## Post-Despliegue - Testing

### Test Rápido
1. Ir a: `https://[tu-dominio]/settings`
2. Verificar campos: "Acción", "Área", "Dispositivo simultáneo"
3. Editar un dispositivo y guardar
4. Ejecutar una acción desde Control de Acceso
5. Verificar logs

**Éxito si**:
- [ ] UI carga sin errores
- [ ] Campos nuevos visibles
- [ ] Datos se guardan correctamente
- [ ] Ejecución funciona

---

## Rollback (Si algo falla)

```bash
# Rollback rápido
mysql -u [user] -p [database] << 'EOF'
ALTER TABLE shelly_devices DROP COLUMN is_simultaneous;
ALTER TABLE shelly_devices DROP COLUMN area;
EOF

cd /path/to/dunas
git checkout [commit-anterior]
sudo systemctl restart php-fpm
```
