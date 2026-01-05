# Script para verificar y corregir permisos del archivo config.php
# Ejecutar este script si no se guardan los cambios en la configuración

Write-Host "=== Verificando permisos de config.php ===" -ForegroundColor Cyan

$configPath = Join-Path $PSScriptRoot "config\config.php"

Write-Host "`nRuta del archivo: $configPath" -ForegroundColor Yellow

if (Test-Path $configPath) {
    Write-Host "✓ El archivo existe" -ForegroundColor Green
    
    # Obtener información del archivo
    $fileInfo = Get-Item $configPath
    Write-Host "`nAtributos actuales: $($fileInfo.Attributes)" -ForegroundColor Yellow
    
    # Verificar si es de solo lectura
    if ($fileInfo.IsReadOnly) {
        Write-Host "⚠ El archivo está marcado como SOLO LECTURA" -ForegroundColor Red
        Write-Host "Removiendo atributo de solo lectura..." -ForegroundColor Yellow
        $fileInfo.IsReadOnly = $false
        Write-Host "✓ Atributo removido exitosamente" -ForegroundColor Green
    } else {
        Write-Host "✓ El archivo NO está marcado como solo lectura" -ForegroundColor Green
    }
    
    # Obtener ACL (permisos)
    $acl = Get-Acl $configPath
    Write-Host "`nPropietario: $($acl.Owner)" -ForegroundColor Yellow
    
    Write-Host "`nPermisos:" -ForegroundColor Yellow
    foreach ($access in $acl.Access) {
        Write-Host "  - Usuario/Grupo: $($access.IdentityReference)" -ForegroundColor Cyan
        Write-Host "    Derechos: $($access.FileSystemRights)" -ForegroundColor Gray
        Write-Host "    Tipo: $($access.AccessControlType)" -ForegroundColor Gray
        Write-Host ""
    }
    
    # Intentar escribir en el archivo para probar
    Write-Host "Probando escritura en el archivo..." -ForegroundColor Yellow
    try {
        $testContent = Get-Content $configPath -Raw
        [System.IO.File]::WriteAllText($configPath, $testContent)
        Write-Host "✓ El archivo es ESCRIBIBLE" -ForegroundColor Green
    } catch {
        Write-Host "✗ ERROR: El archivo NO es escribible" -ForegroundColor Red
        Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
        
        Write-Host "`nIntentando dar permisos de escritura..." -ForegroundColor Yellow
        try {
            $currentUser = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
            $rule = New-Object System.Security.AccessControl.FileSystemAccessRule(
                $currentUser,
                "FullControl",
                "Allow"
            )
            $acl.SetAccessRule($rule)
            Set-Acl -Path $configPath -AclObject $acl
            Write-Host "✓ Permisos actualizados correctamente" -ForegroundColor Green
        } catch {
            Write-Host "✗ ERROR al actualizar permisos: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
} else {
    Write-Host "✗ ERROR: El archivo config.php NO existe en la ruta especificada" -ForegroundColor Red
}

Write-Host "`n=== Proceso completado ===" -ForegroundColor Cyan
Write-Host "Presione cualquier tecla para salir..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
