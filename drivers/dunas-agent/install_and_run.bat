@echo off
REM ========================================================
REM Script Completo: Instalar flask_cors y Ejecutar Bridge
REM ========================================================

echo.
echo ========================================================
echo   Instalacion y Ejecucion del Bridge Hikvision
echo ========================================================
echo.

REM Intentar con 'py' primero (Python Launcher)
where py >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [1/3] Usando Python Launcher 'py'...
    py --version
    echo.
    
    echo [2/3] Instalando flask-cors...
    py -m pip install flask-cors
    
    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo ERROR: No se pudo instalar flask-cors
        echo Verifica tu conexion a Internet y permisos
        pause
        exit /b 1
    )
    
    echo.
    echo [3/3] Iniciando bridge...
    echo.
    py agent.py
    goto :end
)

REM Intentar con 'python' si 'py' no esta disponible
where python >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [1/3] Usando comando 'python'...
    python --version
    echo.
    
    echo [2/3] Instalando flask-cors...
    python -m pip install flask-cors
    
    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo ERROR: No se pudo instalar flask-cors
        echo Verifica tu conexion a Internet y permisos
        pause
        exit /b 1
    )
    
    echo.
    echo [3/3] Iniciando bridge...
    echo.
    python agent.py
    goto :end
)

REM Si llegamos aqui, Python no esta instalado
echo.
echo ERROR: Python no esta instalado o no esta en el PATH
echo.
echo Soluciones:
echo 1. Descarga Python desde https://www.python.org/downloads/
echo 2. Durante la instalacion, marca "Add Python to PATH"
echo 3. Reinicia el CMD despues de instalar
echo.
pause
exit /b 1

:end
echo.
echo ========================================================
echo   Bridge finalizado
echo ========================================================
pause
