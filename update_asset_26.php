<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aset;

echo "Updating asset ID 26:\n";
echo "====================\n";

$aset = Aset::find(26);

if ($aset) {
    echo "Asset: " . $aset->nama_aset . "\n";
    echo "Metode: " . $aset->metode_penyusutan . "\n";
    
    // Hitung tarif untuk garis lurus
    $total = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);
    $nilaiDisusutkan = $total - (float)($aset->nilai_residu ?? 0);
    $umur = (int)($aset->umur_manfaat ?? $aset->umur_ekonomis_tahun ?? 1);
    
    if ($umur > 0 && $total > 0) {
        $tarif = ($nilaiDisusutkan / $total) * (100 / $umur);
        $aset->tarif_penyusutan = $tarif;
        $aset->save();
        
        echo "Updated tarif penyusutan: " . number_format($tarif, 2, ',', '.') . "%\n";
        
        $tahunan = $total * ($tarif / 100);
        echo "Penyusutan per tahun: Rp " . number_format($tahunan, 0, ',', '.') . "\n";
    }
    
} else {
    echo "Asset not found!\n";
}
