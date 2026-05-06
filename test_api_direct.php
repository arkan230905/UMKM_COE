<?php

echo "=== TEST API ENDPOINT DIRECTLY ===\n\n";

echo "Testing BTKL API endpoint directly...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Testing API query directly...\n";
    
    // Simulate the exact API query
    $btklData = \Illuminate\Support\Facades\DB::table('proses_produksis as pp')
        ->leftJoin('jabatans as j', 'pp.jabatan_id', '=', 'j.id')
        ->where('pp.user_id', 1) // Simulate logged-in user
        ->select(
            'pp.id',
            'pp.kode_proses',
            'pp.nama_proses',
            'j.nama as nama_jabatan',
            'pp.tarif_btkl',
            'pp.satuan_btkl',
            'pp.kapasitas_per_jam',
            \Illuminate\Support\Facades\DB::raw('(pp.tarif_btkl / NULLIF(pp.kapasitas_per_jam, 0)) as btkl_per_produk'),
            \Illuminate\Support\Facades\DB::raw('0 as bop_per_produk')
        )
        ->orderBy('pp.nama_proses')
        ->get();
    
    echo "Query returned {$btklData->count()} records\n";
    
    if ($btklData->count() > 0) {
        echo "✅ Data found:\n";
        foreach ($btklData as $row) {
            echo "  - ID: {$row->id}\n";
            echo "    Kode: {$row->kode_proses}\n";
            echo "    Nama: {$row->nama_proses}\n";
            echo "    Jabatan: {$row->nama_jabatan}\n";
            echo "    Tarif: Rp " . number_format($row->tarif_btkl, 0, ',', '.') . "/{$row->satuan_btkl}\n";
            echo "    Kapasitas: {$row->kapasitas_per_jam} pcs/jam\n";
            echo "    BTKL/pcs: Rp " . number_format($row->btkl_per_produk, 0, ',', '.') . "\n";
            echo "    BOP/pcs: Rp " . number_format($row->bop_per_produk, 0, ',', '.') . "\n\n";
        }
        
        echo "2. Testing JSON response format...\n";
        $jsonData = $btklData->toArray();
        echo "JSON data prepared with " . count($jsonData) . " items\n";
        
    } else {
        echo "❌ No data found for user_id = 1\n";
        
        echo "3. Checking if there are any records at all...\n";
        $allData = \Illuminate\Support\Facades\DB::table('proses_produksis')->get();
        echo "Total records in proses_produksis: {$allData->count()}\n";
        
        if ($allData->count() > 0) {
            echo "Sample records:\n";
            foreach ($allData->take(3) as $row) {
                echo "  - User ID: {$row->user_id}, Kode: {$row->kode_proses}, Nama: {$row->nama_proses}\n";
            }
        }
        
        echo "4. Checking user_id distribution...\n";
        $userIds = \Illuminate\Support\Facades\DB::table('proses_produksis')
            ->select('user_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->get();
        
        echo "User ID distribution:\n";
        foreach ($userIds as $row) {
            echo "  - User ID {$row->user_id}: {$row->count} records\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== API TEST COMPLETE ===\n";
