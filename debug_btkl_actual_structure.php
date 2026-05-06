<?php

echo "=== DEBUG BTKL ACTUAL STRUCTURE ===\n\n";

echo "Checking actual proses_produksis table structure and data...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Getting actual table structure...\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('proses_produksis');
    echo "Columns in proses_produksis: " . implode(', ', $columns) . "\n\n";
    
    echo "2. Checking data for user_id = 1 (logged-in user)...\n";
    $userData = \Illuminate\Support\Facades\DB::table('proses_produksis')
        ->where('user_id', 1)
        ->select('id', 'kode_proses', 'nama_proses', 'tarif_btkl', 'satuan_btkl', 'kapasitas_per_jam', 'jabatan_id')
        ->get();
    
    echo "Found {$userData->count()} records for user_id = 1:\n";
    foreach ($userData as $row) {
        echo "  - ID: {$row->id}, Kode: {$row->kode_proses}, Nama: {$row->nama_proses}\n";
        echo "    Tarif: {$row->tarif_btkl} {$row->satuan_btkl}, Kapasitas: {$row->kapasitas_per_jam}, Jabatan ID: {$row->jabatan_id}\n";
    }
    echo "\n";
    
    echo "3. Testing current API query...\n";
    try {
        $apiData = \Illuminate\Support\Facades\DB::table('proses_produksis as pp')
            ->leftJoin('jabatans as j', 'pp.jabatan_id', '=', 'j.id')
            ->where('pp.user_id', 1)
            ->select(
                'pp.id',
                'pp.kode_proses',
                'pp.nama_proses',
                'j.nama as nama_jabatan',
                'pp.tarif_btkl',
                'pp.kapasitas_per_jam',
                'pp.btkl_per_produk',
                'pp.bop_per_produk'
            )
            ->orderBy('pp.nama_proses')
            ->get();
        
        echo "API query returned {$apiData->count()} records\n";
        if ($apiData->count() > 0) {
            foreach ($apiData as $row) {
                echo "  - {$row->nama_proses} ({$row->kode_proses}) - {$row->nama_jabatan}\n";
            }
        }
    } catch (Exception $e) {
        echo "API query error: " . $e->getMessage() . "\n";
    }
    
    echo "\n4. Checking if btkl_per_produk and bop_per_produk columns exist...\n";
    if (in_array('btkl_per_produk', $columns)) {
        echo "✅ btkl_per_produk column exists\n";
    } else {
        echo "❌ btkl_per_produk column does not exist\n";
        echo "   Available columns: " . implode(', ', $columns) . "\n";
    }
    
    if (in_array('bop_per_produk', $columns)) {
        echo "✅ bop_per_produk column exists\n";
    } else {
        echo "❌ bop_per_produk column does not exist\n";
    }
    
    echo "\n5. Testing corrected query without missing columns...\n";
    try {
        $correctedData = \Illuminate\Support\Facades\DB::table('proses_produksis as pp')
            ->leftJoin('jabatans as j', 'pp.jabatan_id', '=', 'j.id')
            ->where('pp.user_id', 1)
            ->select(
                'pp.id',
                'pp.kode_proses',
                'pp.nama_proses',
                'j.nama as nama_jabatan',
                'pp.tarif_btkl',
                'pp.satuan_btkl',
                'pp.kapasitas_per_jam',
                \Illuminate\Support\Facades\DB::raw('(pp.tarif_btkl / pp.kapasitas_per_jam) as btkl_per_produk'),
                \Illuminate\Support\Facades\DB::raw('0 as bop_per_produk')
            )
            ->orderBy('pp.nama_proses')
            ->get();
        
        echo "Corrected query returned {$correctedData->count()} records:\n";
        foreach ($correctedData as $row) {
            echo "  - {$row->nama_proses} ({$row->kode_proses}) - {$row->nama_jabatan}\n";
            echo "    BTKL/pcs: Rp " . number_format($row->btkl_per_produk, 0, ',', '.') . "\n";
        }
    } catch (Exception $e) {
        echo "Corrected query error: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
