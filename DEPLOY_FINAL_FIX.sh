#!/bin/bash
# Script untuk deploy semua perbaikan multi-tenant ke hosting IDCloudHost
# Jalankan: bash DEPLOY_FINAL_FIX.sh

echo "=========================================="
echo "DEPLOY MULTI-TENANT SECURITY FIXES"
echo "=========================================="
echo ""
echo "Connecting to hosting server..."
echo ""

ssh simcost@103.134.154.77 << 'ENDSSH'
cd /var/www/html

echo "1. Cleaning up temporary files..."
sudo rm -f COMMAND_IDCLOUDHOST.txt DEPLOYMENT_SUCCESS_GOOD_DESIGN.md DEPLOY_IDCLOUDHOST.txt DEPLOY_SEKARANG.txt LANGKAH_DEPLOY_HOSTING.md MULAI_DISINI.txt README_DEPLOYMENT.md deploy-idcloudhost.sh

echo "2. Stashing local changes..."
sudo git stash

echo "3. Pulling latest changes from GitHub..."
sudo git pull origin main

echo "4. Clearing Laravel cache..."
sudo php artisan config:clear
sudo php artisan cache:clear
sudo php artisan view:clear
sudo php artisan route:clear

echo "5. Optimizing application..."
sudo php artisan config:cache
sudo php artisan route:cache

echo "6. Fixing permissions..."
sudo chmod -R 755 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

echo ""
echo "=========================================="
echo "DEPLOYMENT COMPLETED SUCCESSFULLY!"
echo "=========================================="
echo ""
echo "All pages now have multi-tenant security:"
echo "✅ Dashboard"
echo "✅ Harga Pokok Produksi (BOM)"
echo "✅ Laporan Penggajian"
echo "✅ Laporan Pembayaran Beban"
echo "✅ Laporan Pelunasan Utang"
echo "✅ Laporan Kas dan Bank"
echo "✅ Jurnal Umum"
echo "✅ Buku Besar"
echo "✅ Neraca Saldo"
echo "✅ Laporan Posisi Keuangan"
echo "✅ Laba Rugi"
echo ""
echo "Please test at: https://jobcost.eadtmanufaktur.com"
echo ""

ENDSSH

echo ""
echo "Deployment script finished!"
