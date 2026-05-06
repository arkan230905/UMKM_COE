<?php

echo "=== DEBUG BOP DATA ISSUE ===\n\n";

echo "Investigating why BOP data is empty despite BTKL selection...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Testing BOP API with selected BTKL IDs [2,1]...\n";
    
    // Simulate the exact API call
    $btklIds = [2, 1];
    
    $bopData = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
        ->leftJoin('coas as c', 'bp.coa_id', '=', 'c.id')
        ->whereIn('bp.proses_btkl_id', $btklIds)
        ->where('bp.user_id', 1) // Simulate logged-in user
        ->select(
            'bp.id',
            'bp.komponen_bop',
            'bp.jumlah',
            'bp.tarif',
            'bp.total',
            'c.nama_coa'
        )
        ->orderBy('bp.komponen_bop')
        ->get();
    
    echo "BOP query returned {$bopData->count()} records\n";
    
    if ($bopData->count() > 0) {
        echo "✅ BOP data found:\n";
        foreach ($bopData as $row) {
            echo "  - {$row->komponen_bop}\n";
            echo "    Jumlah: {$row->jumlah}\n";
            echo "    Tarif: Rp " . number_format($row->tarif, 0, ',', '.') . "\n";
            echo "    Total: Rp " . number_format($row->total, 0, ',', '.') . "\n";
            echo "    COA: {$row->nama_coa}\n\n";
        }
    } else {
        echo "❌ No BOP data found\n";
        
        echo "2. Checking if there are ANY BOP records for user_id = 1...\n";
        $allBOP = \Illuminate\Support\Facades\DB::table('bop_proses')
            ->where('user_id', 1)
            ->get();
        
        echo "Total BOP records for user_id = 1: {$allBOP->count()}\n";
        
        if ($allBOP->count() > 0) {
            echo "Sample BOP records:\n";
            foreach ($allBOP->take(3) as $row) {
                echo "  - Proses BTKL ID: {$row->proses_btkl_id}\n";
                echo "    Komponen: {$row->komponen_bop}\n";
                echo "    Total: Rp " . number_format($row->total, 0, ',', '.') . "\n\n";
            }
        }
        
        echo "3. Checking proses_btkl_id distribution...\n";
        $prosesIds = \Illuminate\Support\Facades\DB::table('bop_proses')
            ->where('user_id', 1)
            ->select('proses_btkl_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->groupBy('proses_btkl_id')
            ->get();
        
        echo "Proses BTKL IDs with BOP data:\n";
        foreach ($prosesIds as $row) {
            echo "  - Proses BTKL ID {$row->proses_btkl_id}: {$row->count} records\n";
        }
        
        echo "4. Checking if selected BTKL IDs [2,1] have BOP data...\n";
        foreach ($btklIds as $id) {
            $count = \Illuminate\Support\Facades\DB::table('bop_proses')
                ->where('user_id', 1)
                ->where('proses_btkl_id', $id)
                ->count();
            echo "  - Proses BTKL ID {$id}: {$count} BOP records\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
