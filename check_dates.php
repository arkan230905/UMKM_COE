<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aset;

echo "Checking asset dates for ID 17:\n";
echo "============================\n";

$aset = Aset::find(17);

if ($aset) {
    echo "Asset: " . $aset->nama_aset . "\n";
    echo "tanggal_beli: " . ($aset->tanggal_beli ? $aset->tanggal_beli->format('Y-m-d F') : 'NULL') . "\n";
    echo "tanggal_akuisisi: " . ($aset->tanggal_akuisisi ? $aset->tanggal_akuisisi->format('Y-m-d F') : 'NULL') . "\n";
    
    // Test calculation
    $tahunan = $aset->hitungBebanPenyusutanTahunan();
    $tahunPertama = $aset->hitungPenyusutanTahunPertama();
    
    echo "\nCalculation:\n";
    echo "Annual: Rp " . number_format($tahunan, 2) . "\n";
    echo "First Year: Rp " . number_format($tahunPertama, 2) . "\n";
    
    // Manual calculation for February
    $expected = $tahunan * (11/12);
    echo "Expected (Feb): Rp " . number_format($expected, 2) . "\n";
    
} else {
    echo "Asset not found!\n";
}
