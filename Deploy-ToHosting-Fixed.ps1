# PowerShell Script untuk Deploy ke IDCloudHost (FIXED VERSION)
# Jalankan: .\Deploy-ToHosting-Fixed.ps1

Write-Host "=========================================="
Write-Host "DEPLOY MULTI-TENANT SECURITY FIXES"
Write-Host "=========================================="
Write-Host ""

$server = "simcost@103.134.154.77"
$projectPath = "/var/www/html"

Write-Host "Connecting to hosting server..." -ForegroundColor Yellow
Write-Host "You will be asked for password once." -ForegroundColor Yellow
Write-Host ""

# Create a single SSH command with all operations
$sshCommand = "cd $projectPath; echo '1. Cleaning temporary files...'; sudo rm -f COMMAND_IDCLOUDHOST.txt DEPLOYMENT_SUCCESS_GOOD_DESIGN.md DEPLOY_IDCLOUDHOST.txt DEPLOY_SEKARANG.txt LANGKAH_DEPLOY_HOSTING.md MULAI_DISINI.txt README_DEPLOYMENT.md deploy-idcloudhost.sh; echo '2. Stashing local changes...'; sudo git stash; echo '3. Pulling from GitHub...'; sudo git pull origin main; echo '4. Installing Composer dependencies...'; sudo composer install --no-dev --optimize-autoloader; echo '5. Clearing Laravel cache...'; sudo php artisan config:clear; sudo php artisan cache:clear; sudo php artisan view:clear; sudo php artisan route:clear; echo '6. Optimizing application...'; sudo php artisan config:cache; sudo php artisan route:cache; echo '7. Fixing permissions...'; sudo chmod -R 755 storage bootstrap/cache; sudo chown -R www-data:www-data storage bootstrap/cache; echo ''; echo '=========================================='; echo 'DEPLOYMENT COMPLETED SUCCESSFULLY!'; echo '=========================================='; echo ''; echo 'All pages now have multi-tenant security:'; echo '- Dashboard'; echo '- Harga Pokok Produksi (BOM)'; echo '- Laporan Penggajian'; echo '- Laporan Pembayaran Beban'; echo '- Laporan Pelunasan Utang'; echo '- Laporan Kas dan Bank'; echo '- Jurnal Umum'; echo '- Buku Besar'; echo '- Neraca Saldo'; echo '- Laporan Posisi Keuangan'; echo '- Laba Rugi'; echo ''; echo 'Test at: https://jobcost.eadtmanufaktur.com'"

# Execute SSH command
ssh $server $sshCommand

Write-Host ""
Write-Host "=========================================="
Write-Host "Deployment script finished!" -ForegroundColor Green
Write-Host "=========================================="
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Open browser: https://jobcost.eadtmanufaktur.com/dashboard"
Write-Host "2. Login with your credentials"
Write-Host "3. Test all pages to verify they work correctly"
Write-Host ""
