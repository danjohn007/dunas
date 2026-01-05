@echo off
echo ==============================================
echo   Instalando dependencias para el agente
echo ==============================================
echo.

REM Intentar con 'py' primero (Python Launcher)
py --version >nul 2>&1
if not errorlevel 1 (
    echo Instalando flask-cors con py...
    py -m pip install flask-cors
    goto :success
)

REM Intentar con 'python'
python --version >nul 2>&1
if not errorlevel 1 (
    echo Instalando flask-cors con python...
    python -m pip install flask-cors
    goto :success
)

REM Si ninguno funciona, mostrar error
echo ERROR: No se encontro Python
echo Intenta ejecutar manualmente uno de estos comandos:
echo    py -m pip install flask-cors
echo    python -m pip install flask-cors
pause
exit /b 1

:success
if errorlevel 1 (
    echo.
    echo ERROR: No se pudo instalar flask-cors
    echo Intenta ejecutar este comando manualmente:
    echo    py -m pip install flask-cors
    pause
    exit /b 1
)

echo.
echo ==============================================
echo   Instalacion completada exitosamente
echo ==============================================
echo.
echo Ya puedes ejecutar run_agent.bat
pause
