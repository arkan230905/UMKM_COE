<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aset;

echo "Updating asset dates to February 2025:\n";
echo "===================================\n";

$aset = Aset::find(17);

if ($aset) {
    echo "Before update:\n";
    echo "tanggal_beli: " . ($aset->tanggal_beli ? $aset->tanggal_beli->format('Y-m-d F') : 'NULL') . "\n";
    echo "tanggal_akuisisi: " . ($aset->tanggal_akuisisi ? $aset->tanggal_akuisisi->format('Y-m-d F') : 'NULL') . "\n";
    
    // Update to February 1, 2025
    $aset->tanggal_beli = '2025-02-01';
    $aset->tanggal_akuisisi = '2025-02-01';
    $aset->save();
    
    echo "\nAfter update:\n";
    echo "tanggal_beli: " . ($aset->tanggal_beli ? $aset->tanggal_beli->format('Y-m-d F') : 'NULL') . "\n";
    echo "tanggal_akuisisi: " . ($aset->tanggal_akuisisi ? $aset->tanggal_akuisisi->format('Y-m-d F') : 'NULL') . "\n";
    
    // Test calculation
    $tahunan = $aset->hitungBebanPenyusutanTahunan();
    $tahunPertama = $aset->hitungPenyusutanTahunPertama();
    
    echo "\nUpdated calculation:\n";
    echo "Annual: Rp " . number_format($tahunan, 2) . "\n";
    echo "First Year (11 months): Rp " . number_format($tahunPertama, 2) . "\n";
    echo "Formula: (11/12) × Rp " . number_format($tahunan, 2) . " = Rp " . number_format($tahunPertama, 2) . "\n";
    
} else {
    echo "Asset not found!\n";
}
