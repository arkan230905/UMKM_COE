<?php

echo "=== ADD DEBUG LOGGING TO CONTROLLER ===\n\n";

echo "Adding debug logging to BiayaBahanController...\n";

$controllerFile = 'c:\UMKM_COE\app\Http\Controllers\BiayaBahanController.php';
$controllerContent = file_get_contents($controllerFile);

// Find the index method and add debug logging
$indexMethodPattern = '/public function index\(Request \$request\)\s*\{.*?\n    \}/s';

$debugCode = '
    // DEBUG: Add logging
    \Log::info("=== BIAYA BAHAN CONTROLLER DEBUG ===");
    \Log::info("User ID: " . auth()->id());
    \Log::info("User exists: " . (auth()->check() ? "YES" : "NO"));
    
    $query = Produk::query()->where(\'user_id\', auth()->id());
    $produks = $query->orderBy(\'nama_produk\')->get();
    \Log::info("Products found: " . $produks->count());
    
    foreach ($produks as $produk) {
        \Log::info("Product: " . $produk->nama_produk . " (ID: " . $produk->id . ")");
        
        $bbbData = DB::table(\'bom_job_bbb as bbb\')
            ->leftJoin(\'bahan_bakus as bb\', \'bbb.bahan_baku_id\', \'=\', \'bb.id\')
            ->leftJoin(\'satuans as s\', \'bb.satuan_id\', \'=\', \'s.id\')
            ->where(\'bbb.user_id\', auth()->id())
            ->where(\'bbb.produk_id\', $produk->id)
            ->select(
                \'bbb.id\',
                \'bb.nama_bahan\',
                \'bbb.jumlah as qty\',
                \'bbb.satuan\',
                \'bbb.harga_satuan\',
                \'bbb.subtotal\',
                \'s.nama as satuan_nama\'
            )
            ->get();
        
        \Log::info("BBB records for product " . $produk->id . ": " . $bbbData->count());
        
        if ($bbbData->count() > 0) {
            foreach ($bbbData as $bbb) {
                \Log::info("  - " . $bbb->nama_bahan . ": " . $bbb->subtotal);
            }
        }
    }
    
    \Log::info("Final produkBiaya count: " . count($produkBiaya));
    \Log::info("=== END DEBUG ===");
    ';

// Find where to insert the debug code (after the existing logic)
$insertPosition = strpos($controllerContent, 'return view(\'master-data.biaya-bahan.index\'');

if ($insertPosition !== false) {
    // Insert debug code before the return statement
    $newControllerContent = substr_replace($controllerContent, $debugCode . "\n        return", $insertPosition, 6);
    
    file_put_contents($controllerFile, $newControllerContent);
    echo "✅ Added debug logging to BiayaBahanController\n";
    echo "✅ Check storage/logs/laravel.log for debug output\n";
    echo "✅ Visit the page and then check the logs\n";
} else {
    echo "❌ Could not find insertion point in controller\n";
}

echo "\n=== DEBUG LOGGING ADDED ===\n";
