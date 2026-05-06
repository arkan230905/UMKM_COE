@echo off
echo ========================================
echo MySQL Database Repair Script
echo ========================================
echo.

echo Step 1: Stopping MySQL...
cd C:\xampp
call mysql_stop.bat
timeout /t 3 >nul

echo Step 2: Running myisamchk to repair tables...
cd C:\xampp\mysql\bin
myisamchk.exe --recover --extend-check C:\xampp\mysql\data\mysql\*.MYI

echo Step 3: Starting MySQL...
cd C:\xampp
call mysql_start.bat
timeout /t 5 >nul

echo.
echo ========================================
echo DONE! Testing connection...
echo ========================================
cd C:\xampp\htdocs\UMKM_COE
php artisan config:clear
php artisan db:show

pause
