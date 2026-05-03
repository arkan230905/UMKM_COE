#!/bin/bash

# Script untuk fix semua masalah multi-tenant di hosting
# Jalankan script ini di hosting setelah upload insert_satuan_kurang.php

echo "🚀 Starting Multi-Tenant Fixes..."
echo "=================================="
echo ""

# 1. Copy script Satuan ke project folder
echo "📁 Step 1: Copying Satuan script..."
sudo cp /home/simcost/insert_satuan_kurang.php /var/www/html/
echo "✅ Done"
echo ""

# 2. Masuk ke project folder
cd /var/www/html

# 3. Pull code terbaru
echo "📥 Step 2: Pulling latest code from GitHub..."
git pull origin main
echo "✅ Done"
echo ""

# 4. Jalankan migration Jabatan
echo "🔧 Step 3: Running Jabatan migration..."
php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php
echo "✅ Done"
echo ""

# 5. Jalankan migration Aset
echo "🔧 Step 4: Running Aset migration..."
php artisan migrate --path=database/migrations/2026_05_03_130000_fix_asets_unique_constraint_multi_tenant.php
echo "✅ Done"
echo ""

# 6. Jalankan script Satuan
echo "📊 Step 5: Running Satuan script..."
php insert_satuan_kurang.php
echo "✅ Done"
echo ""

# 7. Verifikasi Satuan
echo "🔍 Step 6: Verifying Satuan count..."
mysql -u root -p eadt_umkm -e "SELECT user_id, COUNT(*) as total FROM satuans GROUP BY user_id;"
echo ""

# 8. Verifikasi constraint Jabatan
echo "🔍 Step 7: Verifying Jabatan constraint..."
mysql -u root -p eadt_umkm -e "SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan';"
echo ""

# 9. Verifikasi constraint Aset
echo "🔍 Step 8: Verifying Aset constraint..."
mysql -u root -p eadt_umkm -e "SHOW INDEX FROM asets WHERE Column_name = 'kode_aset';"
echo ""

echo "=================================="
echo "🎉 All fixes completed!"
echo "=================================="
echo ""
echo "Expected Results:"
echo "- Satuan: All users should have 16 units"
echo "- Jabatan: Constraint should be 'jabatans_kode_user_unique' with (kode_jabatan, user_id)"
echo "- Aset: Constraint should be 'asets_kode_user_unique' with (kode_aset, user_id)"
echo ""
echo "Please test:"
echo "1. Add Jabatan with different users (same kode should work)"
echo "2. Add Aset with different users (same kode should work)"
echo "3. Check Satuan page (should show 16 units, editable)"
echo "4. Check COA page (should show 50 accounts, editable)"
