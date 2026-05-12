@echo off
echo Connecting to MySQL database to fix Ayam Kampung stock...
mysql -u root -p eadt_umkm < fix_ayam_kampung_final.sql
echo Done! Check the output above for results.
pause