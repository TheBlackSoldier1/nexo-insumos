@echo off
cd /d "%~dp0"
echo Nexo - Inventario TI
where php >nul 2>nul
if errorlevel 1 (
    echo No se encontro PHP. Instala PHP 8.4.1 o superior y agregalo al PATH.
    pause
    exit /b 1
)
if not exist "vendor\autoload.php" goto pendiente
if not exist ".env" goto pendiente
if not exist "database\database.sqlite" goto pendiente
echo Abre http://127.0.0.1:8127 en tu navegador.
echo Si ya esta abierto, utiliza la ventana existente.
echo Guia de uso: README.md. Para detener el servidor, pulsa Ctrl+C.
php artisan serve --host=127.0.0.1 --port=8127
pause
exit /b
:pendiente
echo Falta completar la instalacion. Sigue la seccion Inicio rapido del README.md.
pause
exit /b 1
