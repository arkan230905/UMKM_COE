<?php
/**
 * MANUAL DEPLOYMENT SCRIPT FOR PRODUCTION
 * 
 * Upload this file to your production server root directory
 * Then run: php deploy_to_production_manual.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "==========================================\n";
echo "DEPLOYMENT TO PRODUCTION SERVER\n";
echo "==========================================\n\n";

try {
    // Step 1: Update COA mappings for Bahan Baku
    echo "Step 1: Updating COA mappings for Bahan Baku...\n";
    $bahanBakuUpdates = [
        ['Ayam Potong', '1141'],
        ['Ayam Kampung', '1142'],
        ['Bebek', '1143'],
    ];

    foreach ($bahanBakuUpdates as $update) {
        $bb = \App\Models\BahanBaku::where('nama_bahan', $update[0])->first();
        if ($bb) {
            $bb->coa_persediaan_id = $update[1];
            $bb->save();
            echo "  ✓ Updated {$update[0]}: {$update[1]}\n";
        } else {
            echo "  ⚠ Not found: {$update[0]}\n";
        }
    }
    echo "\n";

    // Step 2: Update COA mappings for Bahan Pendukung
    echo "Step 2: Updating COA mappings for Bahan Pendukung...\n";
    $bahanPendukungUpdates = [
        ['Tepung Terigu', '1152'],
        ['Tepung Maizena', '1153'],
        ['Lada', '1154'],
        ['Bubuk Kaldu Ayam', '1155'],
        ['Bubuk Bawang Putih', '1156'],
        ['Minyak Goreng', '1151'],
        ['Air Galon', '1150'],
        ['Kemasan Makanan', '1157'],
    ];

    foreach ($bahanPendukungUpdates as $update) {
        $bp = \App\Models\BahanPendukung::where('nama_bahan', $update[0])->first();
        if ($bp) {
            $bp->coa_persediaan_id = $update[1];
            $bp->save();
            echo "  ✓ Updated {$update[0]}: {$update[1]}\n";
        } else {
            echo "  ⚠ Not found: {$update[0]}\n";
        }
    }
    echo "\n";

    // Step 3: Fix foreign key constraint
    echo "Step 3: Fixing foreign key constraint for pembelians...\n";
    try {
        DB::statement('ALTER TABLE pembelians DROP FOREIGN KEY pembelians_vendor_id_foreign');
        DB::statement('ALTER TABLE pembelians ADD CONSTRAINT pembelians_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE');
        echo "  ✓ Foreign key constraint fixed\n";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'check that column/key exists') !== false) {
            echo "  ⚠ Foreign key may already be correct\n";
        } else {
            throw $e;
        }
    }
    echo "\n";

    // Step 4: Clear caches
    echo "Step 4: Clearing caches...\n";
    \Artisan::call('config:clear');
    echo "  ✓ Config cache cleared\n";
    
    \Artisan::call('cache:clear');
    echo "  ✓ Application cache cleared\n";
    
    \Artisan::call('view:clear');
    echo "  ✓ View cache cleared\n";
    
    \Artisan::call('route:clear');
    echo "  ✓ Route cache cleared\n";
    echo "\n";

    // Step 5: Verification
    echo "Step 5: Verifying updates...\n";
    $ayamPotong = \App\Models\BahanBaku::where('nama_bahan', 'Ayam Potong')->first();
    if ($ayamPotong && $ayamPotong->coa_persediaan_id == '1141') {
        echo "  ✓ Ayam Potong COA: {$ayamPotong->coa_persediaan_id}\n";
    } else {
        echo "  ⚠ Ayam Potong COA verification failed\n";
    }

    $tepungTerigu = \App\Models\BahanPendukung::where('nama_bahan', 'Tepung Terigu')->first();
    if ($tepungTerigu && $tepungTerigu->coa_persediaan_id == '1152') {
        echo "  ✓ Tepung Terigu COA: {$tepungTerigu->coa_persediaan_id}\n";
    } else {
        echo "  ⚠ Tepung Terigu COA verification failed\n";
    }
    echo "\n";

    echo "==========================================\n";
    echo "✓ DEPLOYMENT COMPLETED SUCCESSFULLY\n";
    echo "==========================================\n\n";

    echo "Next steps:\n";
    echo "1. Test at: Transaksi > Pembelian > Tambah Pembelian\n";
    echo "2. Select 'Ayam Potong' as Bahan Baku\n";
    echo "3. Preview Jurnal should show 'Pers. Bahan Baku Ayam Potong' (badge: 1141)\n";
    echo "4. Try saving the transaction\n";
    echo "5. Delete this file after successful deployment\n\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
