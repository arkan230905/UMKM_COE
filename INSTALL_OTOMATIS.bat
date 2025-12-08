@echo off
echo ========================================
echo   INSTALL OTOMATIS - UMKM COE EADT
echo ========================================
echo.

echo [1/6] Checking PHP Extensions...
php -m | findstr /C:"intl" >nul
if %errorlevel% neq 0 (
    echo   X intl NOT ENABLED
    echo.
    echo   PERHATIAN: Extension intl belum diaktifkan!
    echo   Silakan ikuti instruksi di file: AKTIFKAN_EXTENSIONS.txt
    echo.
    pause
    exit /b 1
) else (
    echo   OK intl enabled
)

php -m | findstr /C:"zip" >nul
if %errorlevel% neq 0 (
    echo   X zip NOT ENABLED
    echo.
    echo   PERHATIAN: Extension zip belum diaktifkan!
    echo   Silakan ikuti instruksi di file: AKTIFKAN_EXTENSIONS.txt
    echo.
    pause
    exit /b 1
) else (
    echo   OK zip enabled
)

echo.
echo [2/6] Installing Dependencies...
echo   (Ini akan memakan waktu 5-10 menit, harap sabar...)
composer install --no-interaction
if %errorlevel% neq 0 (
    echo   X Failed to install dependencies
    pause
    exit /b 1
)
echo   OK Dependencies installed

echo.
echo [3/6] Setting up Environment...
if not exist .env (
    copy .env.example .env
    echo   OK .env created
) else (
    echo   OK .env already exists
)

php artisan key:generate --force
echo   OK APP_KEY generated

echo.
echo [4/6] Setting Permissions...
icacls storage /grant Users:F /T >nul 2>&1
icacls bootstrap\cache /grant Users:F /T >nul 2>&1
echo   OK Permissions set

echo.
echo [5/6] Clearing Cache...
php artisan cache:clear >nul 2>&1
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan view:clear >nul 2>&1
echo   OK Cache cleared

echo.
echo [6/6] Database Setup...
echo.
echo   Pastikan MySQL sudah berjalan!
echo   Database name: eadt_umkm
echo.
set /p migrate="Jalankan migrations? (y/n): "
if /i "%migrate%"=="y" (
    php artisan migrate --force
    if %errorlevel% equ 0 (
        echo   OK Migrations completed
        
        set /p seed="Jalankan seeders? (y/n): "
        if /i "%seed%"=="y" (
            php artisan db:seed --force
            echo   OK Seeders completed
        )
    ) else (
        echo   X Migrations failed
        echo   Pastikan database 'eadt_umkm' sudah dibuat
    )
)

echo.
echo ========================================
echo   SETUP COMPLETED!
echo ========================================
echo.
echo Untuk menjalankan server:
echo   php artisan serve
echo.
echo Aplikasi akan berjalan di:
echo   http://127.0.0.1:8000
echo.

set /p start="Start server sekarang? (y/n): "
if /i "%start%"=="y" (
    echo.
    echo Starting server...
    echo Tekan Ctrl+C untuk stop server
    echo.
    php artisan serve
)

pause
