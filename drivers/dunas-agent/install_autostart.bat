@echo off
setlocal

REM Carpeta donde está este .bat y el run_agent.bat
set "AGENT_DIR=%~dp0"

REM Nombre de la tarea en el Programador de tareas
set "TASK_NAME=DunasAgentAutoStart"

echo ==========================================
echo Configurando inicio automatico de Dunas-Agent
echo Carpeta del agente: %AGENT_DIR%
echo Tarea: %TASK_NAME%
echo ==========================================
echo.

REM Verificar que run_agent.bat exista
if not exist "%AGENT_DIR%run_agent.bat" (
    echo [ERROR] No se encontro run_agent.bat en:
    echo         %AGENT_DIR%
    echo Asegurate de guardar este .bat en la MISMA carpeta.
    echo.
    pause
    exit /b 1
)

REM Revisar si la tarea ya existe
schtasks /query /tn "%TASK_NAME%" >nul 2>&1
if %errorlevel%==0 (
    echo La tarea "%TASK_NAME%" ya existe.
    echo No se hara nada. Si quieres recrearla, borra primero la tarea desde
    echo el Programador de tareas o ejecuta:
    echo   schtasks /delete /tn "%TASK_NAME%" /f
    echo.
    pause
    exit /b 0
)

REM Comando que ejecutara la tarea al iniciar sesion
REM Usamos cmd /c "ruta\run_agent.bat"
set "TASK_CMD=C:\Windows\System32\cmd.exe"
set "TASK_ARGS=/c \"\"%AGENT_DIR%run_agent.bat\"\""

echo Creando tarea programada para el usuario actual...
schtasks /create ^
  /tn "%TASK_NAME%" ^
  /sc ONLOGON ^
  /rl HIGHEST ^
  /tr "%TASK_CMD% %TASK_ARGS%" ^
  /f

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] No se pudo crear la tarea. Verifica que ejecutaste este .bat
    echo como administrador.
    echo.
    pause
    exit /b 1
)

echo.
echo Tarea creada correctamente.
echo A partir del siguiente inicio de sesion, se ejecutara run_agent.bat automaticamente.
echo.
pause
endlocal