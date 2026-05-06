<?php

echo "=== CHECK JABATANS TABLE STRUCTURE ===\n\n";

echo "Checking jabatans table structure...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Checking if jabatans table exists...\n";
    if (\Illuminate\Support\Facades\Schema::hasTable('jabatans')) {
        echo "✅ Table jabatans exists\n";
        
        echo "2. Getting table structure...\n";
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('jabatans');
        echo "Columns in jabatans: " . implode(', ', $columns) . "\n";
        
        echo "3. Sample data from jabatans:\n";
        $data = \Illuminate\Support\Facades\DB::table('jabatans')
            ->select('*')
            ->limit(3)
            ->get();
        
        if ($data->count() > 0) {
            foreach ($data as $row) {
                echo "  - ID: {$row->id}, ";
                if (isset($row->nama_jabatan)) {
                    echo "Nama: {$row->nama_jabatan}";
                } elseif (isset($row->nama)) {
                    echo "Nama: {$row->nama}";
                } elseif (isset($row->jabatan)) {
                    echo "Jabatan: {$row->jabatan}";
                } else {
                    echo "Unknown name field";
                }
                echo "\n";
            }
        } else {
            echo "No data in jabatans table\n";
        }
        
        echo "4. Testing corrected query...\n";
        $apiData = \Illuminate\Support\Facades\DB::table('proses_produksis as pp')
            ->leftJoin('jabatans as j', 'pp.jabatan_id', '=', 'j.id')
            ->where('pp.user_id', 1)
            ->select(
                'pp.id',
                'pp.kode_proses',
                'pp.nama_proses',
                'pp.jabatan_id',
                // Try different possible column names
                \Illuminate\Support\Facades\DB::raw('COALESCE(j.nama_jabatan, j.nama, j.jabatan, "Unknown") as nama_jabatan'),
                'pp.tarif_btkl',
                'pp.kapasitas_per_jam',
                'pp.btkl_per_produk',
                'pp.bop_per_produk'
            )
            ->orderBy('pp.nama_proses')
            ->limit(3)
            ->get();
        
        if ($apiData->count() > 0) {
            echo "✅ Corrected query works:\n";
            foreach ($apiData as $row) {
                echo "  - {$row->nama_proses} ({$row->kode_proses}) - {$row->nama_jabatan}\n";
            }
        } else {
            echo "❌ Even corrected query returns no data\n";
        }
        
    } else {
        echo "❌ Table jabatans does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
