<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing BOP Proses insert with keterangan column...\n";

try {
    // Test inserting a BOP Proses record with keterangan
    $testData = [
        'nama_bop_proses' => 'Test BOP',
        'proses_produksi_id' => 1,
        'komponen_bop' => json_encode([
            ["component" => "Test Component", "rate_per_hour" => 50, "description" => "Test"]
        ]),
        'total_bop_per_jam' => 50.00,
        'kapasitas_per_jam' => 1,
        'bop_per_unit' => 50.00,
        'keterangan' => 'Test keterangan field',
        'is_active' => 1,
        'periode' => '2026-04',
        'user_id' => 1
    ];
    
    $insertId = \Illuminate\Support\Facades\DB::table('bop_proses')->insertGetId($testData);
    echo "BOP Proses test record inserted with ID: {$insertId}\n";
    
    // Test reading the record
    $record = \Illuminate\Support\Facades\DB::table('bop_proses')->find($insertId);
    echo "Keterangan field value: " . ($record->keterangan ?? 'NULL') . "\n";
    
    // Clean up test record
    \Illuminate\Support\Facades\DB::table('bop_proses')->delete($insertId);
    echo "Test record cleaned up\n";
    
    echo "\nBOP Proses insert test completed successfully!\n";
    echo "No more 'keterangan column not found' errors!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
