<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $pegawai = App\Models\Pegawai::first();
    if (!$pegawai) {
        echo "No pegawai found\n";
        exit;
    }
    
    echo "Found pegawai: " . $pegawai->nama . " (ID: " . $pegawai->id . ")\n";
    
    $penggajian = new App\Models\Penggajian([
        'pegawai_id' => $pegawai->id,
        'tanggal_penggajian' => now()->format('Y-m-d'),
        'coa_kasbank' => '1101',
        'gaji_pokok' => 3000000,
        'tarif_per_jam' => 15000,
        'tunjangan' => 500000,
        'asuransi' => 200000,
        'bonus' => 0,
        'potongan' => 0,
        'total_jam_kerja' => 0,
        'total_gaji' => 3700000,
    ]);
    
    echo "Attempting to save...\n";
    $result = $penggajian->save();
    
    if ($result) {
        echo "SUCCESS: Penggajian saved with ID: " . $penggajian->id . "\n";
    } else {
        echo "FAILED: Save returned false\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
