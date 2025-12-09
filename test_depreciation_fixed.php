<?php

require_once 'vendor/autoload.php';

use App\Services\AssetDepreciationService;
use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\JurnalUmum;

// Mock data based on Excel example
$asset = new class extends Asset {
    public $id = 1;
    public $nama_aset = 'Vehicle A';
    public $harga_perolehan = 500000000;
    public $nilai_sisa = 0;
    public $umur_ekonomis = 5;
    public $tanggal_perolehan = '2011-02-01';
    public $metode_penyusutan = 'saldo_menurun';
    public $expense_coa_id = 1;
    public $accum_depr_coa_id = 2;
};

try {
    $service = new AssetDepreciationService();
    $service->computeAndPost($asset);
    
    // Display results in Indonesian format
    echo "Double Declining Balance Depreciation\n";
    echo "Asset: " . $asset->nama_aset . "\n";
    echo "Cost: Rp " . number_format($asset->harga_perolehan, 0, ',', '.') . "\n";
    echo "Residual: Rp " . number_format($asset->nilai_sisa, 0, ',', '.') . "\n";
    echo "Useful Life: " . $asset->umur_ekonomis . " years\n";
    echo "Rate: " . ((1 / $asset->umur_ekonomis) * 2 * 100) . "%\n\n";

    echo "Year\tDepreciation\t\t\tAccumulated\t\tBook Value\n";
    echo "----\t-----------\t\t\t-----------\t----------\n";

    $depreciations = AssetDepreciation::where('asset_id', $asset->id)
        ->orderBy('tahun')
        ->get();

    foreach ($depreciations as $dep) {
        echo $dep->tahun . "\t";
        echo "Rp " . str_pad(number_format($dep->beban_penyusutan, 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\t";
        echo "Rp " . str_pad(number_format($dep->akumulasi_penyusutan, 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\t";
        echo "Rp " . str_pad(number_format($dep->nilai_buku_akhir, 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
