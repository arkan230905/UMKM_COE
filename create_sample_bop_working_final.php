<?php

echo "=== CREATE SAMPLE BOP DATA (WORKING FINAL) ===\n\n";

echo "Creating sample BOP data with correct column names...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Creating sample BOP data for proses_bduksi_id 1 and 2...\n";
    
    // Create sample BOP data for testing
    $sampleBOP = [
        [
            'user_id' => 1,
            'proses_bduksi_id' => 1,
            'komponen_bop' => 'Listrik',
            'total_bop_per_produk' => 150000,
            'total_bop_per_jam' => 1500,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'user_id' => 1,
            'proses_bduksi_id' => 2,
            'komponen_bop' => 'Gas BBM',
            'total_bop_per_produk' => 500000,
            'total_bop_per_jam' => 5000,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    \Illuminate\Support\Facades\DB::table('bop_proses')->insert($sampleBOP);
    
    echo "✅ Created sample BOP data:\n";
    echo "  - Proses BTKL ID 1: Listrik (Rp 150,000)\n";
    echo "  - Proses BTKL ID 2: Gas BBM (Rp 500,000)\n";
    
    echo "\n2. Testing API with selected BTKL IDs [2,1]...\n";
    // Simulate the API call
    $btklIdArray = [2, 1];
    $bopData = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
        ->leftJoin('coas as c', 'bp.coa_id', '=', 'c.id')
        ->whereIn('bp.proses_bduksi_id', $btklIdArray)
        ->where('bp.user_id', 1)
        ->select(
            'bp.id',
            'bp.komponen_bop',
            'bp.total_bop_per_produk',
            'bp.total_bop_per_jam',
            'c.nama_coa'
        )
        ->orderBy('bp.komponen_bop')
        ->get();
    
    echo "API returns {$bopData->count()} records:\n";
    foreach ($bopData as $row) {
        echo "  - {$row->komponen_bop}: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
    }
    
    echo "\n=== SAMPLE BOP DATA CREATED AND TESTED ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
