<?php

echo "=== CHECK BTKL DATA WITH LARAVEL ===\n\n";

echo "Using Laravel to check BTKL data...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Checking if proses_produksis table exists...\n";
    if (\Illuminate\Support\Facades\Schema::hasTable('proses_produksis')) {
        echo "✅ Table proses_produksis exists\n";
        
        echo "2. Counting records in proses_produksis...\n";
        $total = \Illuminate\Support\Facades\DB::table('proses_produksis')->count();
        echo "Total records: {$total}\n";
        
        if ($total > 0) {
            echo "3. Checking records for user_id = 1...\n";
            $userTotal = \Illuminate\Support\Facades\DB::table('proses_produksis')
                ->where('user_id', 1)
                ->count();
            echo "Records for user_id = 1: {$userTotal}\n";
            
            if ($userTotal > 0) {
                echo "4. Sample data for user_id = 1:\n";
                $data = \Illuminate\Support\Facades\DB::table('proses_produksis')
                    ->where('user_id', 1)
                    ->select('id', 'kode_proses', 'nama_proses', 'jabatan_id')
                    ->limit(3)
                    ->get();
                
                foreach ($data as $row) {
                    echo "  - ID: {$row->id}, Kode: {$row->kode_proses}, Nama: {$row->nama_proses}, Jabatan ID: {$row->jabatan_id}\n";
                }
                
                echo "5. Testing the exact API query:\n";
                $apiData = \Illuminate\Support\Facades\DB::table('proses_produksis as pp')
                    ->leftJoin('jabatans as j', 'pp.jabatan_id', '=', 'j.id')
                    ->where('pp.user_id', 1)
                    ->select(
                        'pp.id',
                        'pp.kode_proses',
                        'pp.nama_proses',
                        'j.nama_jabatan',
                        'pp.tarif_btkl',
                        'pp.kapasitas_per_jam',
                        'pp.btkl_per_produk',
                        'pp.bop_per_produk'
                    )
                    ->orderBy('pp.nama_proses')
                    ->limit(3)
                    ->get();
                
                if ($apiData->count() > 0) {
                    echo "✅ API query returns data:\n";
                    foreach ($apiData as $row) {
                        echo "  - {$row->nama_proses} ({$row->kode_proses}) - {$row->nama_jabatan}\n";
                    }
                } else {
                    echo "❌ API query returns no data!\n";
                }
            } else {
                echo "❌ No data for user_id = 1\n";
            }
        } else {
            echo "❌ No data in proses_produksis table\n";
        }
    } else {
        echo "❌ Table proses_produksis does not exist\n";
        
        echo "3. Checking other possible BTKL tables...\n";
        $possibleTables = ['proses_btkl', 'btkl', 'proses', 'produksi'];
        foreach ($possibleTables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                $total = \Illuminate\Support\Facades\DB::table($table)->count();
                echo "✅ Table {$table} exists with {$total} records\n";
            } else {
                echo "❌ Table {$table} does not exist\n";
            }
        }
    }
    
    echo "4. Checking all available tables...\n";
    $tables = \Illuminate\Support\Facades\Schema::getTableListing();
    echo "Available tables: " . implode(', ', array_slice($tables, 0, 10)) . "...\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
