<?php

echo "=== CREATE BOP DATA SAMPLE ===\n\n";

echo "Creating BOP data sample based on your database structure...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Clearing existing BOP data for user_id = 1...\n";
    \Illuminate\Support\Facades\DB::table('bop_proses')
        ->where('user_id', 1)
        ->delete();
    
    echo "2. Creating BOP data sample for proses_produksi_id 1 and 2...\n";
    
    // Create BOP data based on your database example
    $sampleBOP = [
        [
            'user_id' => 1,
            'proses_produksi_id' => 1,
            'listrik_per_jam' => 0.00,
            'gas_bbm_per_jam' => 0.00,
            'penyusutan_mesin_per_jam' => 0.00,
            'maintenance_per_jam' => 0.00,
            'gaji_mandor_per_jam' => 0.00,
            'lain_lain_per_jam' => 0.00,
            'komponen_bop' => '[{"component":"Gas \/ BBM","rate_per_hour":67,"description":""},{"component":"Air & Kebersihan","rate_per_hour":28,"description":""}]',
            'total_bop_per_produk' => 95.00,
            'total_biaya_per_produk' => 261.67,
            'total_bop_per_jam' => 0.00,
            'kapasitas_per_jam' => 120,
            'bop_per_unit' => 95.0000,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'budget' => 0.00,
            'aktual' => 0.00
        ],
        [
            'user_id' => 1,
            'proses_produksi_id' => 2,
            'listrik_per_jam' => 0.00,
            'gas_bbm_per_jam' => 0.00,
            'penyusutan_mesin_per_jam' => 0.00,
            'maintenance_per_jam' => 0.00,
            'gaji_mandor_per_jam' => 0.00,
            'lain_lain_per_jam' => 0.00,
            'komponen_bop' => '[{"component":"Listrik","rate_per_hour":278,"description":""},{"component":"Susu","rate_per_hour":649,"description":""},{"component":"Keju","rate_per_hour":1000,"description":""},{"component":"Cup","rate_per_hour":400,"description":""}]',
            'total_bop_per_produk' => 2327.00,
            'total_biaya_per_produk' => 2610.33,
            'total_bop_per_jam' => 0.00,
            'kapasitas_per_jam' => 60,
            'bop_per_unit' => 2327.0000,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'budget' => 0.00,
            'aktual' => 0.00
        ]
    ];
    
    \Illuminate\Support\Facades\DB::table('bop_proses')->insert($sampleBOP);
    
    echo "✅ Created BOP data sample:\n";
    echo "  - Proses BTKL ID 1: Gas / BBM, Air & Kebersihan (Total: Rp 95)\n";
    echo "  - Proses BTKL ID 2: Listrik, Susu, Keju, Cup (Total: Rp 2,327)\n";
    
    echo "\n3. Testing API with BTKL IDs [2,1]...\n";
    $btklIds = [2, 1];
    
    $rawBopData = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
        ->whereIn('bp.proses_produksi_id', $btklIds)
        ->where('bp.user_id', 1)
        ->select(
            'bp.id',
            'bp.proses_produksi_id',
            'bp.komponen_bop',
            'bp.total_bop_per_produk',
            'bp.total_bop_per_jam'
        )
        ->orderBy('bp.id')
        ->get();
    
    echo "   Query result: {$rawBopData->count()} records\n";
    
    // Process the BOP data
    $processedBopData = [];
    foreach ($rawBopData as $bop) {
        $komponenData = json_decode($bop->komponen_bop, true);
        
        if (is_array($komponenData)) {
            foreach ($komponenData as $component) {
                $processedBopData[] = [
                    'id' => $bop->id,
                    'komponen_bop' => $component['component'] ?? 'Unknown',
                    'total_bop_per_produk' => $component['rate_per_hour'] ?? 0,
                    'total_bop_per_jam' => $component['rate_per_hour'] ?? 0
                ];
            }
        }
    }
    
    echo "   Processed data: " . count($processedBopData) . " records\n";
    foreach ($processedBopData as $bop) {
        echo "     - {$bop['komponen_bop']}: Rp " . number_format($bop['total_bop_per_produk'], 0, ',', '.') . "\n";
    }
    
    echo "\n4. JSON response for API:\n";
    $jsonResponse = json_encode($processedBopData);
    echo $jsonResponse . "\n";
    
    echo "\n=== BOP DATA SAMPLE CREATED ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
