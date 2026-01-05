@echo off
echo ==============================
echo Abriendo puerto 8080 en Firewall
echo ==============================

REM Verificar si la regla ya existe
powershell -Command "if (Get-NetFirewallRule -DisplayName 'Hikvision Bridge' -ErrorAction SilentlyContinue) { Write-Host 'La regla ya existe. No se realizaron cambios.' } else { New-NetFirewallRule -DisplayName 'Hikvision Bridge' -Direction Inbound -LocalPort 8080 -Protocol TCP -Action Allow; Write-Host 'Regla creada correctamente.' }"

echo.
echo Proceso finalizado.
pause
