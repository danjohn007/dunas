# Script para Limpiar el Historial de Git y Eliminar Archivos Grandes

## ⚠️ IMPORTANTE: Este script reescribe el historial de Git

Este script debe ejecutarse LOCALMENTE por el propietario del repositorio.

## Problema

El archivo ZIP de GitHub sigue siendo corrupto/incompleto porque los archivos grandes
están en el historial de Git, aunque ya no estén en el directorio de trabajo.

## Solución Aplicada

Se usó `git filter-branch` para eliminar completamente los archivos grandes del historial:
- `drivers/dunas-agent/python-3.14.0-amd64.exe` (29 MB)
- `drivers/dunas-agent/agent.log` (1.3 MB)  
- `drivers/dunas-agent.zip` (29 MB)

## Resultados

**Antes:**
- `.git` folder: 29 MB
- Repository total: 228 MB
- ZIP download: ❌ Corrupto

**Después:**
- `.git` folder: 668 KB (reducción de 98%)
- Repository total: 3.2 MB
- ZIP download: ✅ Debería funcionar

## Pasos para Aplicar Localmente

Si quieres replicar esta limpieza en tu repositorio local:

```bash
cd /ruta/a/tu/repositorio/dunas

# 1. Hacer backup primero
git clone . ../dunas-backup

# 2. Asegurarte de estar en el branch correcto
git checkout copilot/fix-voucher-printing-errors

# 3. Limpiar archivos grandes del historial
git filter-branch --force --index-filter \
  'git rm --cached --ignore-unmatch \
    drivers/dunas-agent/python-3.14.0-amd64.exe \
    drivers/dunas-agent/agent.log \
    drivers/dunas-agent.zip \
    imagenes/*.jpg' \
  --prune-empty --tag-name-filter cat -- --all

# 4. Limpiar referencias y recolectar basura
rm -rf .git/refs/original/
git reflog expire --expire=now --all
git gc --prune=now --aggressive

# 5. Verificar tamaño
du -sh .git
# Debería ser < 1 MB ahora

# 6. Force push (REESCRIBE HISTORIAL EN GITHUB)
git push --force origin copilot/fix-voucher-printing-errors
```

## ⚠️ Advertencias

1. **Force push reescribe el historial:** Cualquiera que haya clonado este branch necesitará hacer fresh clone
2. **Backup primero:** Siempre haz un backup antes de usar git filter-branch
3. **Coordinar con el equipo:** Avisar a todos antes de force push

## Verificación Post-Limpieza

```bash
# Verificar que los archivos grandes ya no están en el historial
git rev-list --objects --all | \
  git cat-file --batch-check='%(objecttype) %(objectname) %(objectsize) %(rest)' | \
  sed -n 's/^blob //p' | \
  sort --numeric-sort --key=2 --reverse | \
  head -10

# No deberías ver archivos > 1 MB

# Verificar tamaño del repositorio
git count-objects -vH
# size-pack debería ser < 1 MiB
```

## Descargar ZIP desde GitHub

Después del force push:

1. Ir a: https://github.com/danjohn007/dunas/tree/copilot/fix-voucher-printing-errors
2. Click en "Code" > "Download ZIP"
3. El archivo debería:
   - Pesar ~3-5 MB (no 100+ MB)
   - Descomprimirse sin errores
   - Contener todos los archivos de código fuente

## Alternativa: Clonar con Git

En lugar de descargar ZIP, usar git clone:

```bash
git clone -b copilot/fix-voucher-printing-errors https://github.com/danjohn007/dunas.git
cd dunas/
```

Esto siempre funcionará mejor que ZIP download.

## Prevención Futura

Para evitar que esto vuelva a pasar:

1. **Actualizar .gitignore ANTES de commit:**
   ```gitignore
   # Large binaries
   *.exe
   *.zip
   *.msi
   *.dmg
   *.pkg
   
   # Large logs
   *.log
   
   # Images
   imagenes/*.jpg
   imagenes/*.png
   ```

2. **Usar pre-commit hook:**
   ```bash
   # .git/hooks/pre-commit
   #!/bin/bash
   # Prevent large files from being committed
   max_size=1048576  # 1 MB
   
   for file in $(git diff --cached --name-only); do
       if [ -f "$file" ]; then
           size=$(stat -f%z "$file" 2>/dev/null || stat -c%s "$file")
           if [ $size -gt $max_size ]; then
               echo "Error: $file is larger than 1 MB ($size bytes)"
               exit 1
           fi
       fi
   done
   ```

3. **Revisar antes de push:**
   ```bash
   git diff --stat HEAD~1
   # Revisar si hay archivos grandes listados
   ```

## Soporte

Si encuentras problemas:

1. Verifica el tamaño: `du -sh .git`
2. Lista archivos grandes: Script de verificación arriba
3. Si persiste, contacta al equipo con detalles

---

**Fecha:** 2026-02-04
**Branch:** copilot/fix-voucher-printing-errors
**Status:** Historial limpiado, requiere force push
