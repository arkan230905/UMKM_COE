<?php

echo "=== CREATE PROPER BOP DATA ===\n\n";

echo "Creating proper BOP data with correct format...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Clearing existing BOP data...\n";
    \Illuminate\Support\Facades\DB::table('bop_proses')
        ->where('user_id', 1)
        ->delete();
    
    echo "2. Creating proper BOP data for proses_produksi_id 1 and 2...\n";
    
    // Create proper BOP data with simple text format
    $properBOP = [
        [
            'user_id' => 1,
            'proses_produksi_id' => 1,
            'komponen_bop' => 'Listrik',
            'total_bop_per_produk' => 150000,
            'total_bop_per_jam' => 1500,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'user_id' => 1,
            'proses_produksi_id' => 1,
            'komponen_bop' => 'Gas BBM',
            'total_bop_per_produk' => 500000,
            'total_bop_per_jam' => 5000,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'user_id' => 1,
            'proses_produksi_id' => 2,
            'komponen_bop' => 'Penyusutan Mesin',
            'total_bop_per_produk' => 75000,
            'total_bop_per_jam' => 750,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'user_id' => 1,
            'proses_produksi_id' => 2,
            'komponen_bop' => 'Maintenance',
            'total_bop_per_produk' => 25000,
            'total_bop_per_jam' => 250,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    \Illuminate\Support\Facades\DB::table('bop_proses')->insert($properBOP);
    
    echo "✅ Created proper BOP data:\n";
    echo "  - Proses BTKL ID 1: Listrik (Rp 150,000)\n";
    echo "  - Proses BTKL ID 1: Gas BBM (Rp 500,000)\n";
    echo "  - Proses BTKL ID 2: Penyusutan Mesin (Rp 75,000)\n";
    echo "  - Proses BTKL ID 2: Maintenance (Rp 25,000)\n";
    
    echo "\n3. Testing API with selected BTKL IDs [2,1]...\n";
    $btklIdArray = [2, 1];
    $bopData = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
        ->whereIn('bp.proses_produksi_id', $btklIdArray)
        ->where('bp.user_id', 1)
        ->select(
            'bp.id',
            'bp.komponen_bop',
            'bp.total_bop_per_produk',
            'bp.total_bop_per_jam'
        )
        ->orderBy('bp.komponen_bop')
        ->get();
    
    echo "API returns {$bopData->count()} records:\n";
    foreach ($bopData as $row) {
        echo "  - {$row->komponen_bop}: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
    }
    
    echo "\n4. Creating JSON response test...\n";
    $jsonResponse = $bopData->toJson();
    echo "JSON response: {$jsonResponse}\n";
    
    echo "\n=== PROPER BOP DATA CREATED ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
