<?php

echo "=== TEST BOP API FIXED ===\n\n";

echo "Testing BOP API endpoint with proper debugging...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Checking BOP data for user_id = 1...\n";
    $allBOP = \Illuminate\Support\Facades\DB::table('bop_proses')
        ->where('user_id', 1)
        ->count();
    
    echo "   Total BOP records: {$allBOP}\n";
    
    if ($allBOP > 0) {
        echo "\n2. Checking proses_produksi_id values...\n";
        $prosesIds = \Illuminate\Support\Facades\DB::table('bop_proses')
            ->where('user_id', 1)
            ->select('proses_produksi_id')
            ->distinct()
            ->pluck('proses_produksi_id')
            ->toArray();
        
        echo "   Available proses_produksi_id: [" . implode(', ', $prosesIds) . "]\n";
        
        echo "\n3. Testing with BTKL IDs [2,1]...\n";
        $btklIds = [2, 1];
        
        echo "   Querying for proses_produksi_id IN (" . implode(',', $btklIds) . ")\n";
        
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
        
        if ($rawBopData->count() > 0) {
            echo "\n4. Raw BOP data found:\n";
            foreach ($rawBopData as $bop) {
                echo "     ID: {$bop->id}, Proses ID: {$bop->proses_produksi_id}, Komponen: " . substr($bop->komponen_bop, 0, 50) . "...\n";
            }
            
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
                } else {
                    $processedBopData[] = [
                        'id' => $bop->id,
                        'komponen_bop' => $bop->komponen_bop,
                        'total_bop_per_produk' => $bop->total_bop_per_produk,
                        'total_bop_per_jam' => $bop->total_bop_per_jam
                    ];
                }
            }
            
            echo "\n5. Processed BOP data: " . count($processedBopData) . " records\n";
            foreach ($processedBopData as $bop) {
                echo "     - {$bop['komponen_bop']}: Rp " . number_format($bop['total_bop_per_produk'], 0, ',', '.') . "\n";
            }
            
            echo "\n6. JSON response:\n";
            $jsonResponse = json_encode($processedBopData);
            echo $jsonResponse . "\n";
            
        } else {
            echo "   ❌ No BOP data found for proses_produksi_id [2,1]\n";
            
            echo "\n4. Creating sample BOP data for testing...\n";
            $sampleBOP = [
                [
                    'user_id' => 1,
                    'proses_produksi_id' => 1,
                    'komponen_bop' => '[{"component":"Gas \/ BBM","rate_per_hour":67,"description":""}]',
                    'total_bop_per_produk' => 95,
                    'total_bop_per_jam' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 1,
                    'proses_produksi_id' => 2,
                    'komponen_bop' => '[{"component":"Listrik","rate_per_hour":278,"description":""}]',
                    'total_bop_per_produk' => 2327,
                    'total_bop_per_jam' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            \Illuminate\Support\Facades\DB::table('bop_proses')->insert($sampleBOP);
            echo "   ✅ Created sample BOP data\n";
            
            echo "\n5. Testing again after creating data...\n";
            $rawBopDataAfter = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
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
            
            echo "   Query result: {$rawBopDataAfter->count()} records\n";
        }
    } else {
        echo "   ❌ No BOP data exists for user_id = 1\n";
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
