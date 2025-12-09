<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aset;
use App\Models\AssetDepreciation;

echo "Checking latest straight line asset:\n";
echo "===================================\n";

$assets = Aset::where('metode_penyusutan', 'garis_lurus')->orderBy('id', 'desc')->first();

if ($assets) {
    echo "Asset: " . $assets->nama_aset . " (ID: " . $assets->id . ")\n";
    echo "Metode: " . $assets->metode_penyusutan . "\n";
    echo "Bulan Mulai: " . ($assets->bulan_mulai ?? 'NULL') . "\n";
    echo "Tanggal: " . ($assets->tanggal_akuisisi ?? $assets->tanggal_beli ?? 'NULL') . "\n";
    
    // Check depreciation schedule
    $depreciation = AssetDepreciation::where('asset_id', $assets->id)->orderBy('tahun')->get();
    
    echo "\nDepreciation Schedule:\n";
    foreach ($depreciation as $row) {
        echo "Tahun " . $row->tahun . ": Beban=" . number_format($row->beban_penyusutan, 2) . ", Akumulasi=" . number_format($row->akumulasi_penyusutan, 2) . "\n";
    }
    
    // Check expected calculation
    $total = (float)$assets->harga_perolehan + (float)($assets->biaya_perolehan ?? 0);
    $nilaiDisusutkan = $total - (float)($assets->nilai_residu ?? 0);
    $umur = (int)($assets->umur_manfaat ?? $assets->umur_ekonomis_tahun ?? 1);
    $tahunan = $nilaiDisusutkan / $umur;
    
    echo "\nExpected Annual: Rp " . number_format($tahunan, 2) . "\n";
    
} else {
    echo "No straight line asset found!\n";
}
