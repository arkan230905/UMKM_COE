<?php

echo "=== INVESTIGATE BOP TABLE ===\n\n";

echo "Checking bop_proses table structure and data...\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "1. Checking if bop_proses table exists...\n";
    if (\Illuminate\Support\Facades\Schema::hasTable('bop_proses')) {
        echo "✅ Table bop_proses exists\n";
        
        echo "2. Getting table structure...\n";
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bop_proses');
        echo "Columns in bop_proses: " . implode(', ', $columns) . "\n";
        
        echo "3. Counting total records...\n";
        $totalRecords = \Illuminate\Support\Facades\DB::table('bop_proses')->count();
        echo "Total records in bop_proses: {$totalRecords}\n";
        
        echo "4. Checking records for user_id = 1...\n";
        $userRecords = \Illuminate\Support\Facades\DB::table('bop_proses')
            ->where('user_id', 1)
            ->count();
        echo "Records for user_id = 1: {$userRecords}\n";
        
        if ($userRecords > 0) {
            echo "5. Sample data for user_id = 1:\n";
            $data = \Illuminate\Support\Facades\DB::table('bop_proses')
                ->where('user_id', 1)
                ->select('id', 'nama_komponen', 'jumlah', 'tarif', 'total', 'proses_btkl_id')
                ->limit(5)
                ->get();
            
            foreach ($data as $row) {
                echo "  - ID: {$row->id}\n";
                echo "    Nama: {$row->nama_komponen}\n";
                echo "    Jumlah: {$row->jumlah}\n";
                echo "    Tarif: Rp " . number_format($row->tarif, 0, ',', '.') . "\n";
                echo "    Total: Rp " . number_format($row->total, 0, ',', '.') . "\n";
                echo "    Proses BTKL ID: {$row->proses_btkl_id}\n\n";
            }
        }
        
        echo "6. Checking proses_btkl_id values...\n";
        $prosesIds = \Illuminate\Support\Facades\DB::table('bop_proses')
            ->where('user_id', 1)
            ->select('proses_btkl_id')
            ->distinct()
            ->pluck('proses_btkl_id');
        
        echo "Available proses_btkl_ids: [" . implode(', ', $prosesIds->toArray()) . "]\n";
        
        echo "7. Testing BOP API with selected BTKL IDs [2,1]...\n";
        // Simulate API call
        $btklIds = [2,1];
        $bopData = \Illuminate\Support\Facades\DB::table('bop_proses as bp')
            ->leftJoin('coas as c', 'bp.coa_id', '=', 'c.id')
            ->whereIn('bp.proses_btkl_id', $btklIds)
            ->where('bp.user_id', 1)
            ->select(
                'bp.id',
                'bp.nama_komponen',
                'bp.jumlah',
                'bp.tarif',
                'bp.total',
                'c.nama_coa'
            )
            ->orderBy('bp.nama_komponen')
            ->get();
        
        echo "BOP API would return {$bopData->count()} records:\n";
        foreach ($bopData as $row) {
            echo "  - {$row->nama_komponen}: Rp " . number_format($row->total, 0, ',', '.') . "\n";
        }
        
    } else {
        echo "❌ Table bop_proses does not exist\n";
        
        echo "8. Checking alternative BOP tables...\n";
        $possibleTables = ['bop_komponens', 'bop_proses_komponen', 'bop', 'bop_details'];
        foreach ($possibleTables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                $count = \Illuminate\Support\Facades\DB::table($table)->count();
                echo "✅ Table {$table} exists with {$count} records\n";
            } else {
                echo "❌ Table {$table} does not exist\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";
