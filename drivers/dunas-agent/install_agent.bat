@echo off
setlocal

REM Carpeta donde está este .bat (y agent.py)
set "AGENT_DIR=%~dp0"
cd /d "%AGENT_DIR%"

echo === Instalando entorno del Dunas-Agent ===

REM ------------------------------------------------
REM 1) Localizar un Python REAL (no el de WindowsApps)
REM    Primero probamos 'py -3', luego 'python'.
REM ------------------------------------------------
set "PYTHON_CMD="
set "PYTHON_EXE="

echo Buscando Python...

for /f "usebackq delims=" %%I in (`py -3 -c "import sys; print(sys.executable)" 2^>nul`) do (
    set "PYTHON_CMD=py -3"
    set "PYTHON_EXE=%%I"
)

if not defined PYTHON_CMD (
    for /f "usebackq delims=" %%I in (`python -c "import sys; print(sys.executable)" 2^>nul`) do (
        set "PYTHON_CMD=python"
        set "PYTHON_EXE=%%I"
    )
)

if not defined PYTHON_CMD (
    echo [ERROR] No se pudo ejecutar ni 'py -3' ni 'python'.
    echo Asegurate de haber instalado Python 3.x desde python.org
    echo y de marcar "Add python.exe to PATH".
    pause
    exit /b 1
)

echo Python base encontrado en:
echo   %PYTHON_EXE%

REM ------------------------------------------------
REM 2) Crear venv si no existe
REM ------------------------------------------------
if exist "venv" (
    echo Entorno virtual 'venv' ya existe.
) else (
    echo Creando entorno virtual 'venv'...
    %PYTHON_CMD% -m venv venv
)

REM Comprobar que el Python del venv exista
if not exist "venv\Scripts\python.exe" (
    echo [ERROR] No se encontro venv\Scripts\python.exe.
    echo Algo salio mal al crear el entorno virtual.
    pause
    exit /b 1
)

set "VENV_PY=%AGENT_DIR%venv\Scripts\python.exe"

echo.
echo Python del entorno virtual:
"%VENV_PY%" -V

REM ------------------------------------------------
REM 3) Instalar dependencias DENTRO del venv
REM ------------------------------------------------
echo.
echo Instalando paquetes de Python en el entorno virtual...
"%VENV_PY%" -m pip install --upgrade pip
"%VENV_PY%" -m pip install flask requests

echo.
echo === Instalacion completada ===
pause
endlocal