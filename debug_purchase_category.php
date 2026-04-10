<?php

require_once 'vendor/autoload.php';

use App\Models\Pembelian;

// Get the purchase from the URL (ID 4 based on screenshot)
$pembelian = Pembelian::with(['details.bahanBaku', 'details.bahanPendukung'])->find(4);

if ($pembelian) {
    echo "Purchase ID: {$pembelian->id}\n";
    echo "Vendor: {$pembelian->vendor->nama}\n";
    echo "Details:\n";
    
    foreach ($pembelian->details as $detail) {
        if ($detail->bahan_baku_id) {
            echo "- Bahan Baku: {$detail->bahanBaku->nama_bahan}\n";
        }
        if ($detail->bahan_pendukung_id) {
            echo "- Bahan Pendukung: {$detail->bahanPendukung->nama_bahan}\n";
        }
    }
    
    // Check category logic
    $hasBahanBaku = $pembelian->details->where('bahan_baku_id', '!=', null)->count() > 0;
    $hasBahanPendukung = $pembelian->details->where('bahan_pendukung_id', '!=', null)->count() > 0;
    
    echo "\nAnalysis:\n";
    echo "Has Bahan Baku: " . ($hasBahanBaku ? 'Yes' : 'No') . "\n";
    echo "Has Bahan Pendukung: " . ($hasBahanPendukung ? 'Yes' : 'No') . "\n";
    
    if ($hasBahanBaku && !$hasBahanPendukung) {
        $kategori = 'bahan_baku';
    } elseif ($hasBahanPendukung && !$hasBahanBaku) {
        $kategori = 'bahan_pendukung';
    } else {
        $kategori = 'mixed';
    }
    
    echo "Category should be: {$kategori}\n";
    
    // Check what the view should display
    $showBahanBaku = ($kategori !== 'bahan_pendukung') ? 'block' : 'none';
    $showBahanPendukung = ($kategori !== 'bahan_baku') ? 'block' : 'none';
    
    echo "\nDisplay Logic:\n";
    echo "Bahan Baku section: {$showBahanBaku}\n";
    echo "Bahan Pendukung section: {$showBahanPendukung}\n";
    
} else {
    echo "Purchase not found\n";
}