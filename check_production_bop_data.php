<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Production BOP data...\n";

// Check BOP data for production processes
$productionId = 1;

echo "\n=== BOP Data for Production ID: {$productionId} ===\n";

// Check if there are any BOP records
$bopRecords = \Illuminate\Support\Facades\DB::table('bop_proses')
    ->where('user_id', 1)
    ->get();

echo "Total BOP records: " . $bopRecords->count() . "\n";

if ($bopRecords->count() > 0) {
    foreach ($bopRecords as $bop) {
        echo "\nBOP Record ID: {$bop->id}\n";
        echo "  Nama: " . $bop->nama_bop_proses . "\n";
        echo "  Proses ID: " . $bop->proses_produksi_id . "\n";
        echo "  Total BOP Per Jam: Rp " . number_format($bop->total_bop_per_jam, 0, ',', '.') . "\n";
        echo "  BOP Per Unit: Rp " . number_format($bop->bop_per_unit, 0, ',', '.') . "\n";
        echo "  Komponen BOP: " . $bop->komponen_bop . "\n";
        echo "  Is Active: " . $bop->is_active . "\n";
        echo "  Periode: " . $bop->periode . "\n";
    }
} else {
    echo "No BOP records found\n";
}

// Check production processes
echo "\n=== Production Processes ===\n";
$productionProcesses = \Illuminate\Support\Facades\DB::table('proses_produksis')
    ->where('user_id', 1)
    ->get();

echo "Total production processes: " . $productionProcesses->count() . "\n";

foreach ($productionProcesses as $process) {
    echo "\nProcess ID: {$process->id}\n";
    echo "  Nama: " . $process->nama_proses . "\n";
    echo "  Urutan: " . $process->urutan . "\n";
    
    // Check if there's BOP data for this process
    $bopForProcess = \Illuminate\Support\Facades\DB::table('bop_proses')
        ->where('proses_produksi_id', $process->id)
        ->where('user_id', 1)
        ->first();
    
    if ($bopForProcess) {
        echo "  BOP Found: Rp " . number_format($bopForProcess->bop_per_unit, 0, ',', '.') . "\n";
    } else {
        echo "  BOP: NOT FOUND\n";
    }
}

echo "\nProduction BOP data check completed!\n";
