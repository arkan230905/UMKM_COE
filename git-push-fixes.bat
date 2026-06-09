@echo off
echo ========================================
echo Git Commit and Push - COA Seeder Fix
echo ========================================
echo.

REM Add all modified files
echo Adding files to git...
git add database/seeders/CoaSeeder.php
git add database/seeders/DatabaseSeeder.php
git add app/Console/Commands/RegenerateProductionJournals.php
git add app/Http/Controllers/ProduksiController.php
git add app/Http/Controllers/LaporanController.php
git add app/Http/Controllers/LaporanKasBankController.php
git add resources/views/transaksi/produksi/show.blade.php
git add resources/views/laporan/stok/index.blade.php

echo.
echo Files staged successfully!
echo.

REM Commit with message
echo Creating commit...
git commit -m "Fix: Update COA Seeder to Ayam Goreng Bundo + Production fixes

- Replace JasukeCoaSeeder and CoaAyamSeeder with unified CoaSeeder
- Add 84 standard COAs for Ayam Goreng Bundo business
- Fix production BOP journal entries (detailed components)
- Fix stock report production column display
- Fix laporan kas bank period_id -> coa_period_id
- Add production journals regeneration command
- Update production detail view (show qty total vs qty per product)
- Hide bahan pendukung option in stock report"

echo.
echo Commit created successfully!
echo.

REM Push to GitHub
echo Pushing to GitHub...
git push origin main

echo.
echo ========================================
echo Done! Changes pushed to GitHub.
echo Jenkins will automatically deploy to SSH.
echo ========================================
pause
