<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SIMPLE TEST BIAYA BAHAN ===\n\n";

echo "1. TEST CONTROLLER SYNTAX:\n\n";

try {
    include_once 'c:\UMKM_COE\app\Http\Controllers\BiayaBahanController.php';
    
    if (class_exists('App\Http\Controllers\BiayaBahanController')) {
        echo "✅ BiayaBahanController loads successfully\n";
    } else {
        echo "❌ BiayaBahanController failed to load\n";
    }
    
} catch (\Exception $e) {
    echo "Error loading controller: " . $e->getMessage() . "\n";
}

echo "\n2. TEST DATA STRUCTURE:\n\n";

try {
    // Simulate the fixed logic
    $produks = \App\Models\Produk::where('user_id', 1)->get();
    
    $produkBiaya = [];
    
    foreach ($produks as $produk) {
        // Get BBB data
        $bbbData = \Illuminate\Support\Facades\DB::table('bom_job_bbb as bbb')
            ->leftJoin('bahan_bakus as bb', 'bbb.bahan_baku_id', '=', 'bb.id')
            ->leftJoin('satuans as s', 'bb.satuan_id', '=', 's.id')
            ->where('bbb.user_id', 1)
            ->where('bbb.produk_id', $produk->id)
            ->select(
                'bbb.id',
                'bb.nama_bahan',
                'bbb.jumlah as qty',
                'bbb.satuan',
                'bbb.harga_satuan',
                'bbb.subtotal',
                's.nama as satuan_nama'
            )
            ->get();
        
        $totalBiayaBahanBaku = $bbbData->sum('subtotal');
        
        if ($bbbData->count() > 0) {
            $produkBiaya[] = [
                'produk' => $produk,
                'total_biaya_bahan' => $totalBiayaBahanBaku,
                'total_biaya_bahan_baku' => $totalBiayaBahanBaku
            ];
            
            echo "✅ Product: " . $produk->nama_produk . " - Total: " . $totalBiayaBahanBaku . "\n";
        }
    }
    
    echo "\nTotal entries: " . count($produkBiaya) . "\n";
    
} catch (\Exception $e) {
    echo "Error testing: " . $e->getMessage() . "\n";
}

echo "\n3. EXPECTED RESULT:\n\n";

echo "The page should now show:\n";
echo "- Jasuke\n";
echo "- Total Biaya Bahan Baku: Rp 2.500\n";
echo "- Total Biaya Bahan: Rp 2.500\n";
echo "- Detail: Jagung (50 Kilogram) - Rp 2.500\n\n";

echo "=== TEST COMPLETE ===\n";
