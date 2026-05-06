@echo off
echo Checking MySQL users and hosts...
echo.
C:\xampp\mysql\bin\mysql.exe -h 127.0.0.1 -u root -e "SELECT user, host FROM mysql.user WHERE user='root';"
echo.
pause
