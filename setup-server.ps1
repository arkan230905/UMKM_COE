# Script Setup Server untuk UMKM COE EADT
# Jalankan sebagai Administrator

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  SETUP SERVER - UMKM COE EADT" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Function untuk cek PHP extension
function Test-PHPExtension {
    param($extension)
    $result = php -m | Select-String -Pattern "^$extension$"
    return $null -ne $result
}

# 1. Cek PHP Version
Write-Host "[1/8] Checking PHP Version..." -ForegroundColor Yellow
$phpVersion = php -v | Select-String -Pattern "PHP (\d+\.\d+\.\d+)"
if ($phpVersion) {
    Write-Host "  ✓ PHP Version: $($phpVersion.Matches.Groups[1].Value)" -ForegroundColor Green
} else {
    Write-Host "  ✗ PHP not found!" -ForegroundColor Red
    exit 1
}

# 2. Cek PHP Extensions
Write-Host ""
Write-Host "[2/8] Checking PHP Extensions..." -ForegroundColor Yellow

$requiredExtensions = @("intl", "zip", "fileinfo", "mbstring", "pdo", "pdo_mysql")
$missingExtensions = @()

foreach ($ext in $requiredExtensions) {
    if (Test-PHPExtension $ext) {
        Write-Host "  ✓ $ext enabled" -ForegroundColor Green
    } else {
        Write-Host "  ✗ $ext MISSING" -ForegroundColor Red
        $missingExtensions += $ext
    }
}

if ($missingExtensions.Count -gt 0) {
    Write-Host ""
    Write-Host "PERHATIAN: Extensions berikut belum diaktifkan:" -ForegroundColor Red
    foreach ($ext in $missingExtensions) {
        Write-Host "  - $ext" -ForegroundColor Red
    }
    Write-Host ""
    Write-Host "Silakan aktifkan di php.ini:" -ForegroundColor Yellow
    Write-Host "  1. Buka: C:\xampp\php\php.ini" -ForegroundColor Yellow
    Write-Host "  2. Uncomment (hapus ;) baris: extension=$ext" -ForegroundColor Yellow
    Write-Host "  3. Restart Apache" -ForegroundColor Yellow
    Write-Host ""
    $continue = Read-Host "Lanjutkan setup? (y/n)"
    if ($continue -ne "y") {
        exit 1
    }
}

# 3. Cek Composer
Write-Host ""
Write-Host "[3/8] Checking Composer..." -ForegroundColor Yellow
$composer = Get-Command composer -ErrorAction SilentlyContinue
if ($composer) {
    Write-Host "  ✓ Composer installed" -ForegroundColor Green
} else {
    Write-Host "  ✗ Composer not found!" -ForegroundColor Red
    Write-Host "  Download from: https://getcomposer.org/" -ForegroundColor Yellow
    exit 1
}

# 4. Install Dependencies
Write-Host ""
Write-Host "[4/8] Installing Dependencies..." -ForegroundColor Yellow
if (Test-Path "vendor") {
    Write-Host "  ✓ Vendor directory exists" -ForegroundColor Green
    $reinstall = Read-Host "Reinstall dependencies? (y/n)"
    if ($reinstall -eq "y") {
        Remove-Item -Recurse -Force vendor
        composer install --no-interaction
    }
} else {
    Write-Host "  Installing composer packages..." -ForegroundColor Yellow
    composer install --no-interaction
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Dependencies installed" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Failed to install dependencies" -ForegroundColor Red
        Write-Host "  Trying with --ignore-platform-reqs..." -ForegroundColor Yellow
        composer install --ignore-platform-reqs --no-interaction
    }
}

# 5. Setup Environment
Write-Host ""
Write-Host "[5/8] Setting up Environment..." -ForegroundColor Yellow
if (!(Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "  ✓ .env file created" -ForegroundColor Green
} else {
    Write-Host "  ✓ .env file exists" -ForegroundColor Green
}

# Generate APP_KEY if not exists
$envContent = Get-Content ".env" -Raw
if ($envContent -match "APP_KEY=\s*$") {
    Write-Host "  Generating APP_KEY..." -ForegroundColor Yellow
    php artisan key:generate --force
    Write-Host "  ✓ APP_KEY generated" -ForegroundColor Green
} else {
    Write-Host "  ✓ APP_KEY exists" -ForegroundColor Green
}

# 6. Check Database
Write-Host ""
Write-Host "[6/8] Checking Database..." -ForegroundColor Yellow
Write-Host "  Pastikan MySQL/MariaDB berjalan" -ForegroundColor Yellow
Write-Host "  Database name: eadt_umkm" -ForegroundColor Yellow
$dbSetup = Read-Host "Jalankan migrations? (y/n)"
if ($dbSetup -eq "y") {
    php artisan migrate --force
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Migrations completed" -ForegroundColor Green
        
        $seedData = Read-Host "Jalankan seeders untuk data awal? (y/n)"
        if ($seedData -eq "y") {
            php artisan db:seed --force
            Write-Host "  ✓ Seeders completed" -ForegroundColor Green
        }
    } else {
        Write-Host "  ✗ Migrations failed" -ForegroundColor Red
        Write-Host "  Pastikan database 'eadt_umkm' sudah dibuat" -ForegroundColor Yellow
    }
}

# 7. Set Permissions
Write-Host ""
Write-Host "[7/8] Setting Permissions..." -ForegroundColor Yellow
if (Test-Path "storage") {
    icacls storage /grant Users:F /T | Out-Null
    Write-Host "  ✓ Storage permissions set" -ForegroundColor Green
}
if (Test-Path "bootstrap\cache") {
    icacls bootstrap\cache /grant Users:F /T | Out-Null
    Write-Host "  ✓ Bootstrap cache permissions set" -ForegroundColor Green
}

# 8. Clear Cache
Write-Host ""
Write-Host "[8/8] Clearing Cache..." -ForegroundColor Yellow
php artisan cache:clear | Out-Null
php artisan config:clear | Out-Null
php artisan route:clear | Out-Null
php artisan view:clear | Out-Null
Write-Host "  ✓ Cache cleared" -ForegroundColor Green

# Summary
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  SETUP COMPLETED!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Untuk menjalankan server:" -ForegroundColor Green
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "Aplikasi akan berjalan di:" -ForegroundColor Green
Write-Host "  http://127.0.0.1:8000" -ForegroundColor White
Write-Host ""

$startServer = Read-Host "Start server sekarang? (y/n)"
if ($startServer -eq "y") {
    Write-Host ""
    Write-Host "Starting server..." -ForegroundColor Green
    Write-Host "Tekan Ctrl+C untuk stop server" -ForegroundColor Yellow
    Write-Host ""
    php artisan serve
}
