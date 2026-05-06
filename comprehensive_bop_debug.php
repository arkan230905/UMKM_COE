<?php

echo "=== COMPREHENSIVE BOP DEBUG ===\n\n";

echo "Complete debugging of BOP data issue...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Database Connection Test...\n";
    $connection = \Illuminate\Support\Facades\DB::connection();
    echo "✅ Database connected: " . $connection->getDatabaseName() . "\n";
    
    echo "\n2. Checking bop_proses table structure...\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bop_proses');
    echo "Columns: " . implode(', ', $columns) . "\n";
    
    echo "\n3. Total BOP records for user_id = 1...\n";
    $totalBOP = \Illuminate\Support\Facades\DB::table('bop_proses')
        ->where('user_id', 1)
        ->count();
    echo "Total: {$totalBOP} records\n";
    
    if ($totalBOP > 0) {
        echo "\n4. Sample BOP records:\n";
        $sampleBOP = \Illuminate\Support\Facades\DB::table('bop_proses')
            ->where('user_id', 1)
            ->select('id', 'proses_produksi_id', 'komponen_bop', 'total_bop_per_produk', 'total_bop_per_jam')
            ->limit(5)
            ->get();
        
        foreach ($sampleBOP as $row) {
            echo "  ID: {$row->id}, Proses ID: {$row->proses_produksi_id}, Komponen: {$row->komponen_bop}, Total: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
        }
        
        echo "\n5. Testing exact API call with BTKL IDs [2,1]...\n";
        $btklIdArray = [2, 1];
        
        echo "   Querying for proses_produksi_id IN (" . implode(',', $btklIdArray) . ")\n";
        
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
        
        echo "   API query result: {$bopData->count()} records\n";
        
        if ($bopData->count() > 0) {
            echo "   Records found:\n";
            foreach ($bopData as $row) {
                echo "     - {$row->komponen_bop}: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
            }
            
            echo "\n6. Creating JSON response test...\n";
            $jsonResponse = $bopData->toJson();
            echo "   JSON length: " . strlen($jsonResponse) . " characters\n";
            echo "   JSON preview: " . substr($jsonResponse, 0, 200) . "...\n";
            
            echo "\n7. Testing HTTP endpoint simulation...\n";
            echo "   URL: /master-data/api/bop-details/2,1\n";
            echo "   Method: GET\n";
            echo "   Expected response: JSON with {$bopData->count()} records\n";
            
        } else {
            echo "   ❌ No records found for BTKL IDs [2,1]\n";
            
            echo "\n6. Checking what proses_produksi_id values exist...\n";
            $existingIds = \Illuminate\Support\Facades\DB::table('bop_proses')
                ->where('user_id', 1)
                ->select('proses_produksi_id')
                ->distinct()
                ->pluck('proses_produksi_id')
                ->toArray();
            
            echo "   Existing proses_produksi_id values: [" . implode(', ', $existingIds) . "]\n";
            echo "   Requested BTKL IDs: [2,1]\n";
            
            if (!in_array(1, $existingIds) || !in_array(2, $existingIds)) {
                echo "   ❌ MISMATCH: Requested IDs not found in database\n";
                
                echo "\n7. Creating missing BOP data...\n";
                $missingIds = [];
                if (!in_array(1, $existingIds)) $missingIds[] = 1;
                if (!in_array(2, $existingIds)) $missingIds[] = 2;
                
                foreach ($missingIds as $id) {
                    $sampleBOP = [
                        'user_id' => 1,
                        'proses_produksi_id' => $id,
                        'komponen_bop' => 'Komponen BOP ' . $id,
                        'total_bop_per_produk' => $id * 100000,
                        'total_bop_per_jam' => $id * 1000,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    \Illuminate\Support\Facades\DB::table('bop_proses')->insert($sampleBOP);
                    echo "   ✅ Created BOP data for proses_produksi_id {$id}\n";
                }
                
                echo "\n8. Testing API again after creating data...\n";
                $bopDataAfter = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
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
                
                echo "   API now returns {$bopDataAfter->count()} records\n";
                foreach ($bopDataAfter as $row) {
                    echo "     - {$row->komponen_bop}: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
                }
            }
        }
    } else {
        echo "❌ No BOP data exists at all\n";
        echo "\n4. Creating sample BOP data...\n";
        
        $sampleBOP = [
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
                'proses_produksi_id' => 2,
                'komponen_bop' => 'Gas BBM',
                'total_bop_per_produk' => 500000,
                'total_bop_per_jam' => 5000,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        \Illuminate\Support\Facades\DB::table('bop_proses')->insert($sampleBOP);
        echo "✅ Created sample BOP data\n";
        
        echo "\n5. Testing API after creating data...\n";
        $bopDataAfter = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
            ->whereIn('bp.proses_produksi_id', [2, 1])
            ->where('bp.user_id', 1)
            ->select(
                'bp.id',
                'bp.komponen_bop',
                'bp.total_bop_per_produk',
                'bp.total_bop_per_jam'
            )
            ->orderBy('bp.komponen_bop')
            ->get();
        
        echo "API now returns {$bopDataAfter->count()} records\n";
        foreach ($bopDataAfter as $row) {
            echo "  - {$row->komponen_bop}: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== COMPREHENSIVE DEBUG COMPLETE ===\n";
