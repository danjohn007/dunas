@echo off
setlocal
set "AGENT_DIR=%~dp0"
cd /d "%AGENT_DIR%"

echo ================================================
echo    Iniciando Dunas-Agent (PC Puente)
echo ================================================
echo.
echo Directorio: %AGENT_DIR%
echo.

REM Intentar activar entorno virtual si existe
if exist "venv\Scripts\activate.bat" (
    echo Activando entorno virtual...
    call venv\Scripts\activate.bat
    
    if errorlevel 1 (
        echo ADVERTENCIA: No se pudo activar el entorno virtual
        echo Intentando usar Python global...
        echo.
    ) else (
        echo Entorno virtual activado correctamente
        echo.
    )
)

REM Verificar e instalar flask_cors si es necesario
python -c "import flask_cors" 2>nul
if errorlevel 1 (
    echo ADVERTENCIA: flask_cors no esta instalado
    echo Instalando flask_cors...
    python -m pip install flask-cors
    echo.
    if errorlevel 1 (
        echo ERROR: No se pudo instalar flask_cors
        pause
        exit /b 1
    )
)

echo === Agente escuchando en puerto 8080 ===
echo (Presiona Ctrl+C para detener el agente)
echo.

python agent.py
set "EXITCODE=%ERRORLEVEL%"

echo.
echo ================================================
if %EXITCODE% NEQ 0 (
    echo El agente se ha detenido con ERROR (codigo %EXITCODE%).
    echo Revisa el mensaje de error que aparece arriba.
    echo.
    echo Verifica:
    echo - Que el puerto 8080 no este en uso
    echo - Que flask y flask-cors esten instalados
    echo - Los logs en agent.log
) else (
    echo El agente se ha detenido correctamente (codigo %EXITCODE%).
)

echo.
pause
endlocal