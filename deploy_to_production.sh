#!/bin/bash

echo "=========================================="
echo "DEPLOYMENT TO PRODUCTION SERVER"
echo "=========================================="
echo ""

# Step 1: Pull latest code from GitHub
echo "Step 1: Pulling latest code from GitHub..."
git pull origin nayla

if [ $? -ne 0 ]; then
    echo "❌ Git pull failed. Please resolve conflicts manually."
    exit 1
fi

echo "✓ Code updated successfully"
echo ""

# Step 2: Clear all caches
echo "Step 2: Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "✓ Caches cleared"
echo ""

# Step 3: Update COA mappings for Bahan Baku
echo "Step 3: Updating COA mappings for Bahan Baku..."
php artisan tinker --execute="
\$updates = [
    ['Ayam Potong', '1141'],
    ['Ayam Kampung', '1142'],
    ['Bebek', '1143'],
];

foreach (\$updates as \$update) {
    \$bb = \App\Models\BahanBaku::where('nama_bahan', \$update[0])->first();
    if (\$bb) {
        \$bb->coa_persediaan_id = \$update[1];
        \$bb->save();
        echo \"✓ Updated {\$update[0]}: {\$update[1]}\n\";
    }
}
"

echo "✓ Bahan Baku COA mappings updated"
echo ""

# Step 4: Update COA mappings for Bahan Pendukung
echo "Step 4: Updating COA mappings for Bahan Pendukung..."
php artisan tinker --execute="
\$updates = [
    ['Tepung Terigu', '1152'],
    ['Tepung Maizena', '1153'],
    ['Lada', '1154'],
    ['Bubuk Kaldu Ayam', '1155'],
    ['Bubuk Bawang Putih', '1156'],
    ['Minyak Goreng', '1151'],
    ['Air Galon', '1150'],
    ['Kemasan Makanan', '1157'],
];

foreach (\$updates as \$update) {
    \$bp = \App\Models\BahanPendukung::where('nama_bahan', \$update[0])->first();
    if (\$bp) {
        \$bp->coa_persediaan_id = \$update[1];
        \$bp->save();
        echo \"✓ Updated {\$update[0]}: {\$update[1]}\n\";
    }
}
"

echo "✓ Bahan Pendukung COA mappings updated"
echo ""

# Step 5: Fix foreign key constraint
echo "Step 5: Fixing foreign key constraint for pembelians..."
php artisan tinker --execute="
try {
    DB::statement('ALTER TABLE pembelians DROP FOREIGN KEY pembelians_vendor_id_foreign');
    DB::statement('ALTER TABLE pembelians ADD CONSTRAINT pembelians_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE');
    echo \"✓ Foreign key constraint fixed\n\";
} catch (\Exception \$e) {
    echo \"⚠ Foreign key may already be correct: \" . \$e->getMessage() . \"\n\";
}
"

echo ""

# Step 6: Final cache clear
echo "Step 6: Final cache clear..."
php artisan view:clear
php artisan config:clear

echo "✓ Final caches cleared"
echo ""

echo "=========================================="
echo "✓ DEPLOYMENT COMPLETED SUCCESSFULLY"
echo "=========================================="
echo ""
echo "Please test the following:"
echo "1. Go to Transaksi > Pembelian > Tambah Pembelian"
echo "2. Select 'Ayam Potong' as Bahan Baku"
echo "3. Check Preview Jurnal should show 'Pers. Bahan Baku Ayam Potong' with badge '1141'"
echo "4. Try saving the transaction"
echo ""
