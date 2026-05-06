<?php

echo "=== DEEP DEBUG BOP DATA ===\n\n";

echo "Investigating BOP data connection with selected BTKL IDs...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Checking BOP data for selected BTKL IDs [2,1]...\n";
    
    // Check if there are ANY BOP records for these BTKL IDs
    $btklIds = [2, 1];
    
    echo "2. Checking individual BTKL IDs:\n";
    foreach ($btklIds as $btklId) {
        $count = \Illuminate\Support\Facades\DB::table('bop_proses')
            ->where('user_id', 1)
            ->where('proses_btkl_id', $btklId)
            ->count();
        
        echo "  - BTKL ID {$btklId}: {$count} BOP records\n";
        
        if ($count > 0) {
            $sample = \Illuminate\Support\Facades\DB::table('bop_proses')
                ->where('user_id', 1)
                ->where('proses_btkl_id', $btklId)
                ->select('komponen_bop', 'total_bop_per_produk')
                ->get();
            
            echo "    Sample BOP for BTKL ID {$btklId}:\n";
            foreach ($sample as $row) {
                echo "      - {$row->komponen_bop}: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
            }
        }
    }
    
    echo "\n3. Testing the exact API query:\n";
    $bopData = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
        ->leftJoin('coas as c', 'bp.coa_id', '=', 'c.id')
        ->whereIn('bp.proses_btkl_id', $btklIds)
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
    
    echo "API query returned {$bopData->count()} records\n";
    
    if ($bopData->count() > 0) {
        echo "✅ BOP Data Found:\n";
        foreach ($bopData as $row) {
            echo "  - {$row->komponen_bop}: Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . "\n";
        }
    } else {
        echo "❌ No BOP data found\n";
        
        echo "4. Checking if there are ANY BOP records at all:\n";
        $allBOP = \Illuminate\Support\Facades\DB::table('bop_proses')
            ->where('user_id', 1)
            ->count();
        echo "Total BOP records for user_id = 1: {$allBOP}\n";
        
        if ($allBOP > 0) {
            echo "Sample BOP records:\n";
            $samples = \Illuminate\Support\Facades\DB::table('bop_proses')
                ->where('user_id', 1)
                ->select('proses_btkl_id', 'komponen_bop', 'total_bop_per_produk')
                ->limit(5)
                ->get();
            
            foreach ($samples as $row) {
                echo "  - BTKL ID {$row->proses_btkl_id}: {$row->komponen_bop} (Rp " . number_format($row->total_bop_per_produk, 0, ',', '.') . ")\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== DEEP DEBUG COMPLETE ===\n";
