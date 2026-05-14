#!/bin/bash

# ============================================================================
# REFACTOR TARIF JAM → TARIF PRODUK - DEPLOYMENT COMMANDS
# ============================================================================

echo "🚀 Starting Tarif Produk Refactor Deployment..."
echo ""

# ============================================================================
# STEP 1: Run Migration
# ============================================================================
echo "📊 Step 1: Running database migration..."
php artisan migrate

if [ $? -ne 0 ]; then
    echo "❌ Migration failed!"
    exit 1
fi

echo "✅ Migration completed successfully"
echo ""

# ============================================================================
# STEP 2: Clear Cache
# ============================================================================
echo "🧹 Step 2: Clearing cache..."

php artisan optimize:clear
echo "✅ Optimized cache cleared"

php artisan cache:clear
echo "✅ Application cache cleared"

php artisan config:clear
echo "✅ Configuration cache cleared"

php artisan view:clear
echo "✅ View cache cleared"

php artisan route:clear
echo "✅ Route cache cleared"

echo ""

# ============================================================================
# STEP 3: Verify Database
# ============================================================================
echo "🔍 Step 3: Verifying database changes..."

php artisan tinker <<EOF
\$j = App\Models\Jabatan::first();
if (\$j && isset(\$j->tarif_produk)) {
    echo "✅ Column tarif_produk exists\n";
    echo "   Sample value: " . \$j->tarif_produk . "\n";
} else {
    echo "❌ Column tarif_produk not found\n";
}
exit;
EOF

echo ""

# ============================================================================
# STEP 4: Verify Models
# ============================================================================
echo "🔍 Step 4: Verifying models..."

php artisan tinker <<EOF
\$j = App\Models\Jabatan::first();
\$p = App\Models\Pegawai::with('jabatanRelasi')->first();

if (\$j) {
    echo "✅ Jabatan model works\n";
    echo "   tarif_produk: " . \$j->tarif_produk . "\n";
}

if (\$p && \$p->jabatanRelasi) {
    echo "✅ Pegawai model works\n";
    echo "   tarif_produk from jabatan: " . \$p->jabatanRelasi->tarif_produk . "\n";
}
exit;
EOF

echo ""

# ============================================================================
# STEP 5: Check Logs
# ============================================================================
echo "📋 Step 5: Checking logs for errors..."

if grep -i "error\|exception" storage/logs/laravel.log | tail -5; then
    echo "⚠️  Some errors found in logs (check above)"
else
    echo "✅ No recent errors in logs"
fi

echo ""

# ============================================================================
# STEP 6: Summary
# ============================================================================
echo "============================================================================"
echo "✅ DEPLOYMENT COMPLETE!"
echo "============================================================================"
echo ""
echo "Next steps:"
echo "1. Test in browser: http://localhost/master-data/kualifikasi-tenaga-kerja"
echo "2. Verify label shows 'Tarif/Produk'"
echo "3. Create/Edit kualifikasi to test"
echo "4. Create penggajian to verify tarif loads correctly"
echo ""
echo "If you encounter any issues:"
echo "1. Check logs: tail -f storage/logs/laravel.log"
echo "2. Run rollback: php artisan migrate:rollback"
echo "3. Review documentation: QUICK_DEPLOY_TARIF_PRODUK.md"
echo ""
echo "============================================================================"
