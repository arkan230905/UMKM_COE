<?php

echo "=== FINAL BOP INVESTIGATION ===\n\n";

echo "Complete investigation of BOP data issue...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Checking if there are ANY BOP records at all...\n";
    $allBOP = \Illuminate\Support\Facades\DB::table('bop_proses')
        ->where('user_id', 1)
        ->count();
    
    echo "Total BOP records for user_id = 1: {$allBOP}\n";
    
    if ($allBOP > 0) {
        echo "✅ BOP data exists in database\n";
        
        echo "2. Checking BOP records by proses_bduksi_id...\n";
        $bopByProses = \Illuminate\Support\Facades\DB::table('bop_proses')
            ->where('user_id', 1)
            ->select('proses_bduksi_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->groupBy('proses_bduksi_id')
            ->get();
        
        echo "BOP records by proses_bduksi_id:\n";
        foreach ($bopByProses as $row) {
            echo "  - Proses BTKL ID {$row->proses_bduksi_id}: {$row->count} records\n";
        }
        
        echo "3. Checking if selected BTKL IDs [2,1] have BOP data...\n";
        $selectedIds = [2, 1];
        foreach ($selectedIds as $id) {
            $count = \Illuminate\Support\Facades\DB::table('bop_proses')
                ->where('user_id', 1)
                ->where('proses_bduksi_id', $id)
                ->count();
            
            echo "  - Proses BTKL ID {$id}: {$count} BOP records\n";
        }
        
        echo "4. Testing API with selected IDs [2,1]...\n";
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
        
        echo "API would return {$bopData->count()} records:\n";
        if ($bopData->count() > 0) {
            foreach ($bopData as $row) {
                echo "  - {$row->komponen_bop}: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
            }
        } else {
            echo "  ❌ No BOP data found for selected BTKL IDs\n";
        }
        
        echo "\n5. Summary:\n";
        echo "  - Total BOP records: {$allBOP}\n";
        echo "  - Selected BTKL IDs: [2,1]\n";
        echo "  - BOP records for selected IDs: {$bopData->count()}\n";
        
        if ($bopData->count() === 0 && $allBOP > 0) {
            echo "  ❌ ISSUE: BOP data exists but not found for selected BTKL IDs\n";
            echo "  🔍 Possible causes:\n";
            echo "    - Wrong proses_bduksi_id values\n";
            echo "    - No BOP data for selected BTKL IDs\n";
            echo "    - Data relationship issue\n";
        } else {
            echo "  ✅ BOP data should display correctly\n";
        }
        
    } else {
        echo "❌ No BOP data exists in database\n";
        echo "  🔍 This means BOP table is empty\n";
        echo "  💡 Solution: Need to add BOP data first\n";
        
        echo "\n6. Creating sample BOP data for testing...\n";
        // Create sample BOP data for proses_bduksi_id = 1 and 2
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
        
        echo "\n7. Testing API again after adding data...\n";
        $bopDataAfter = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
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
        
        echo "API now returns {$bopDataAfter->count()} records:\n";
        foreach ($bopDataAfter as $row) {
            echo "  - {$row->komponen_bop}: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== FINAL INVESTIGATION COMPLETE ===\n";
