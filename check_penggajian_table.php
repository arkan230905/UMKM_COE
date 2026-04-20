<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== CHECKING PENGGAJIAN TABLE STRUCTURE ===\n\n";

try {
    // Check if table exists
    if (!Schema::hasTable('penggajians')) {
        echo "ERROR: penggajians table does not exist!\n";
        exit;
    }

    echo "✓ penggajians table exists\n\n";

    // Check required columns
    $requiredColumns = [
        'id',
        'pegawai_id',
        'tanggal_penggajian',
        'coa_kasbank',
        'gaji_pokok',
        'tarif_per_jam',
        'tunjangan',
        'tunjangan_jabatan',
        'tunjangan_transport',
        'tunjangan_konsumsi',
        'total_tunjangan',
        'asuransi',
        'bonus',
        'potongan',
        'total_jam_kerja',
        'total_gaji',
        'status_pembayaran',
    ];

    echo "Checking required columns:\n";
    foreach ($requiredColumns as $column) {
        if (Schema::hasColumn('penggajians', $column)) {
            echo "✓ {$column}\n";
        } else {
            echo "✗ {$column} - MISSING!\n";
        }
    }

    echo "\n=== SAMPLE DATA FROM LATEST RECORD ===\n";
    
    // Get latest record
    $latest = DB::table('penggajians')->latest('id')->first();
    if ($latest) {
        echo "Latest record (ID: {$latest->id}):\n";
        foreach ($latest as $column => $value) {
            echo "  {$column}: " . ($value ?? 'NULL') . "\n";
        }
    } else {
        echo "No records found in penggajians table.\n";
    }

    echo "\n=== TABLE SCHEMA ===\n";
    $columns = DB::select("DESCRIBE penggajians");
    foreach ($columns as $column) {
        echo "{$column->Field} - {$column->Type} - " . ($column->Null === 'YES' ? 'NULL' : 'NOT NULL') . " - Default: " . ($column->Default ?? 'NULL') . "\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETED ===\n";