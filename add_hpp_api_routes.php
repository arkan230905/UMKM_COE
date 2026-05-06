<?php

echo "=== ADD HPP API ROUTES ===\n\n";

echo "Adding API routes for dynamic HPP component data...\n";

$routeFile = 'c:\UMKM_COE\routes\web.php';
$routeContent = file_get_contents($routeFile);

// Add API routes for BBB and BTKL data
$apiRoutes = '
// API Routes for HPP Component Data
Route::prefix(\'api\')->group(function () {
    Route::get(\'bbb-data/{produk_id}\', function ($produkId) {
        try {
            $bbbData = DB::table(\'bom_job_bbb as bbb\')
                ->leftJoin(\'bahan_bakus as bb\', \'bbb.bahan_baku_id\', \'=\', \'bb.id\')
                ->leftJoin(\'satuans as s\', \'bb.satuan_id\', \'=\', \'s.id\')
                ->where(\'bbb.user_id\', auth()->id())
                ->where(\'bbb.produk_id\', $produkId)
                ->select(
                    \'bbb.id\',
                    \'bb.nama_bahan\',
                    \'bbb.jumlah\',
                    \'bbb.satuan\',
                    \'bbb.harga_satuan\',
                    \'bbb.subtotal\',
                    \'s.nama as satuan_nama\'
                )
                ->orderBy(\'bbb.created_at\', \'desc\')
                ->get();
            
            return response()->json($bbbData);
        } catch (\Exception $e) {
            return response()->json([\'error\' => $e->getMessage()], 500);
        }
    });
    
    Route::get(\'btkl-data/{produk_id}\', function ($produkId) {
        try {
            // Get all BTKL processes available
            $btklData = DB::table(\'proses_btkl as pb\')
                ->leftJoin(\'jabatans as j\', \'pb.jabatan_id\', \'=\', \'j.id\')
                ->where(\'pb.user_id\', auth()->id())
                ->select(
                    \'pb.id\',
                    \'pb.kode_proses\',
                    \'pb.nama_proses\',
                    \'j.nama_jabatan\',
                    \'pb.tarif_btkl\',
                    \'pb.kapasitas_per_jam\',
                    \'pb.btkl_per_produk\',
                    \'pb.bop_per_produk\'
                )
                ->orderBy(\'pb.nama_proses\')
                ->get();
            
            return response()->json($btklData);
        } catch (\Exception $e) {
            return response()->json([\'error\' => $e->getMessage()], 500);
        }
    });
});
';

// Find a good place to add the API routes (before the closing bracket of master-data group)
$insertPosition = strpos($routeContent, '}); // End master-data group');
if ($insertPosition !== false) {
    $newRouteContent = substr_replace($routeContent, $apiRoutes . "\n}); // End master-data group", $insertPosition, strlen('}); // End master-data group'));
    file_put_contents($routeFile, $newRouteContent);
    echo "✅ Added API routes for HPP component data\n";
    echo "✅ Routes added:\n";
    echo "  - GET /api/bbb-data/{produk_id} - Get BBB data for product\n";
    echo "  - GET /api/btkl-data/{produk_id} - Get BTKL data for product\n";
} else {
    echo "❌ Could not find insertion point for API routes\n";
}

echo "\n=== API ROUTES ADDED ===\n";
