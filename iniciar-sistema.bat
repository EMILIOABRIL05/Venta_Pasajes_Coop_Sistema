@echo off
setlocal

cd /d "%~dp0"

echo ==========================================
echo  Cooperativa Ambato - Inicio del sistema
echo ==========================================
echo.

where php >nul 2>nul
if errorlevel 1 (
    echo [ERROR] PHP no esta disponible en PATH.
    pause
    exit /b 1
)

where composer >nul 2>nul
if errorlevel 1 (
    echo [ERROR] Composer no esta disponible en PATH.
    pause
    exit /b 1
)

where npm >nul 2>nul
if errorlevel 1 (
    echo [ERROR] Node/NPM no esta disponible en PATH.
    pause
    exit /b 1
)

if not exist ".env" (
    echo [INFO] Copiando .env.example a .env...
    copy ".env.example" ".env" >nul
)

if not exist "vendor\autoload.php" (
    echo [INFO] Instalando dependencias PHP...
    composer install
    if errorlevel 1 goto error
)

if not exist "node_modules" (
    echo [INFO] Instalando dependencias frontend...
    npm install
    if errorlevel 1 goto error
)

echo [INFO] Generando APP_KEY si hace falta...
php artisan key:generate --ansi
if errorlevel 1 goto error

echo [INFO] Limpiando caches...
php artisan optimize:clear
if errorlevel 1 goto error

echo [INFO] Ejecutando migraciones y sembrando datos de prueba...
php artisan migrate:fresh --seed
if errorlevel 1 goto error

echo.
echo ==========================================
echo  Usuarios sembrados
echo ==========================================
echo  Admin:       admin@cooperativa.test       / Admin12345!
echo  Oficinista:  oficinista@cooperativa.test  / Oficina12345!
echo  Chofer:      chofer@cooperativa.test      / Chofer12345!
echo  Developer:   developer@cooperativa.test   / password123
echo.
echo URL local: http://127.0.0.1:8000
echo.
echo [INFO] Levantando servidor, queue, logs y Vite...
echo Para detener todo, cierra esta ventana o presiona Ctrl+C.
echo.

composer dev
exit /b %ERRORLEVEL%

:error
echo.
echo [ERROR] No se pudo iniciar el sistema. Revisa el mensaje anterior.
pause
exit /b 1
