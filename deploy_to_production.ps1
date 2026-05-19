# ============================================
# Script Deploy ke Production Server (PowerShell)
# ============================================

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  DEPLOY MIGRATIONS TO PRODUCTION" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Backup database
Write-Host "Step 1: Backup database..." -ForegroundColor Yellow
try {
    php artisan db:backup 2>$null
} catch {
    Write-Host "Backup command not available, please backup manually via phpMyAdmin" -ForegroundColor Gray
}
Write-Host ""

# Step 2: Pull latest code
Write-Host "Step 2: Pulling latest code from GitHub..." -ForegroundColor Yellow
git fetch origin
git pull origin nayla
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Code pulled successfully" -ForegroundColor Green
} else {
    Write-Host "✗ Failed to pull code" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 3: Check migration files
Write-Host "Step 3: Checking migration files..." -ForegroundColor Yellow
$migrations = @(
    "2026_05_19_000001_rename_coa_period_id_to_period_id_in_coa_period_balances.php",
    "2026_05_19_000002_add_bank_id_to_pembelians_table.php",
    "2026_05_19_000003_add_missing_financial_columns_to_pembelians_table.php",
    "2026_05_19_000004_fix_bank_id_foreign_key_in_pembelians.php",
    "2026_05_19_000005_force_add_financial_columns_to_pembelians.php",
    "2026_05_19_000006_add_missing_columns_to_stock_movements.php",
    "2026_05_19_000007_fix_coa_pelunasan_foreign_key_in_pelunasan_utangs.php",
    "2026_05_19_000008_fix_all_accounts_foreign_keys_to_coas.php"
)

foreach ($migration in $migrations) {
    $path = "database/migrations/$migration"
    if (Test-Path $path) {
        Write-Host "✓ $migration" -ForegroundColor Green
    } else {
        Write-Host "✗ $migration (MISSING!)" -ForegroundColor Red
    }
}
Write-Host ""

# Step 4: Run migrations
Write-Host "Step 4: Running migrations..." -ForegroundColor Yellow
php artisan migrate --force
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Migrations completed successfully" -ForegroundColor Green
} else {
    Write-Host "✗ Migration failed" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 5: Clear cache
Write-Host "Step 5: Clearing cache..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan view:clear
Write-Host "✓ Cache cleared" -ForegroundColor Green
Write-Host ""

# Step 6: Verify
Write-Host "Step 6: Verifying database structure..." -ForegroundColor Yellow
Write-Host "Checking pembelians table..."
php artisan tinker --execute="echo json_encode(Schema::getColumnListing('pembelians'));"
Write-Host ""

# Step 7: Final message
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  DEPLOYMENT COMPLETED!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:"
Write-Host "1. Test: http://jobcost.eadtmanufaktur.com/transaksi/pembelian/create"
Write-Host "2. Check logs if error: storage/logs/laravel.log"
Write-Host "3. Delete this script: Remove-Item deploy_to_production.ps1"
Write-Host ""
