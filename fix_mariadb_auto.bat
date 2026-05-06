@echo off
echo ========================================
echo MariaDB Permission Auto Fix Script
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Script harus dijalankan sebagai Administrator!
    echo Klik kanan file ini dan pilih "Run as administrator"
    pause
    exit /b 1
)

echo Step 1: Stopping MySQL service...
C:\xampp\mysql_stop.bat
timeout /t 3 >nul

echo Step 2: Backing up my.ini...
copy C:\xampp\mysql\bin\my.ini C:\xampp\mysql\bin\my.ini.backup >nul

echo Step 3: Adding skip-grant-tables to my.ini...
echo skip-grant-tables >> C:\xampp\mysql\bin\my.ini

echo Step 4: Starting MySQL service...
C:\xampp\mysql_start.bat
timeout /t 5 >nul

echo Step 5: Fixing permissions...
php fix_mariadb_permission.php

echo.
echo Step 6: Removing skip-grant-tables from my.ini...
findstr /v "skip-grant-tables" C:\xampp\mysql\bin\my.ini > C:\xampp\mysql\bin\my.ini.tmp
move /y C:\xampp\mysql\bin\my.ini.tmp C:\xampp\mysql\bin\my.ini >nul

echo Step 7: Restarting MySQL service...
C:\xampp\mysql_stop.bat
timeout /t 3 >nul
C:\xampp\mysql_start.bat
timeout /t 3 >nul

echo.
echo ========================================
echo DONE! Testing connection...
echo ========================================
php artisan config:clear
php artisan db:show

pause
