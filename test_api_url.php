<?php

echo "=== TEST API URL ACCESS ===\n\n";

echo "Testing API URL access with proper authentication...\n";

// Create a simple test script to check API endpoint
$testScript = '<?php
// Test script for API endpoint
require_once __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate authenticated user
Auth::loginUsingId(1);

try {
    $produkId = 2; // Test with product ID 2
    
    echo "Testing BTKL API for product ID: $produkId\n";
    echo "Authenticated user ID: " . auth()->id() . "\n";
    
    $btklData = DB::table("proses_produksis as pp")
        ->leftJoin("jabatans as j", "pp.jabatan_id", "=", "j.id")
        ->where("pp.user_id", auth()->id())
        ->select(
            "pp.id",
            "pp.kode_proses",
            "pp.nama_proses",
            "j.nama as nama_jabatan",
            "pp.tarif_btkl",
            "pp.satuan_btkl",
            "pp.kapasitas_per_jam",
            DB::raw("(pp.tarif_btkl / NULLIF(pp.kapasitas_per_jam, 0)) as btkl_per_produk"),
            DB::raw("0 as bop_per_produk")
        )
        ->orderBy("pp.nama_proses")
        ->get();
    
    echo "Records found: " . $btklData->count() . "\n";
    
    if ($btklData->count() > 0) {
        echo "Data:\n";
        foreach ($btklData as $row) {
            echo "- {$row->nama_proses} ({$row->kode_proses}) - {$row->nama_jabatan}\n";
        }
    }
    
    // Return JSON response
    header("Content-Type: application/json");
    echo json_encode($btklData->toArray());
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
';

// Create test file
$testFile = 'c:\UMKM_COE\public\test-api.php';
file_put_contents($testFile, $testScript);

echo "✅ Created test API script at: /test-api.php\n";
echo "You can access it at: http://127.0.0.1:8000/test-api.php\n";
echo "This will test the API endpoint with proper authentication.\n\n";

echo "=== TEST API URL CREATED ===\n";
