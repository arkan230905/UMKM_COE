<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking production record ID 3...\n\n";

// Get production record with details
$produksi = \App\Models\Produksi::with([
    'produk',
    'details.bahanBaku.satuan',
    'details.bahanBaku.subSatuan1',
    'details.bahanBaku.subSatuan2', 
    'details.bahanBaku.subSatuan3',
    'details.bahanPendukung.satuanRelation',
    'details.bahanPendukung.subSatuan1',
    'details.bahanPendukung.subSatuan2',
    'details.bahanPendukung.subSatuan3'
])->find(3);

if (!$produksi) {
    echo "Production record not found\n";
    exit;
}

echo "Production: {$produksi->produk->nama_produk}\n";
echo "Date: {$produksi->tanggal_produksi}\n";
echo "Quantity: {$produksi->jumlah_produksi}\n\n";

echo "Production Details:\n";
echo "==================\n";

foreach ($produksi->details as $detail) {
    echo "Detail ID: {$detail->id}\n";
    echo "Recipe Qty: {$detail->qty_resep}\n";
    echo "Recipe Unit: {$detail->satuan_resep}\n";
    
    if ($detail->bahan_baku_id && $detail->bahanBaku) {
        $bahan = $detail->bahanBaku;
        echo "Material: {$bahan->nama_bahan} (Bahan Baku)\n";
        echo "Main Unit: " . ($bahan->satuan->nama ?? 'N/A') . "\n";
        
        // Show conversion data
        echo "Conversion Data:\n";
        echo "  Sub Satuan 1: " . ($bahan->subSatuan1->nama ?? 'N/A') . " - Konversi: " . ($bahan->sub_satuan_1_konversi ?? 'N/A') . " - Nilai: " . ($bahan->sub_satuan_1_nilai ?? 'N/A') . "\n";
        echo "  Sub Satuan 2: " . ($bahan->subSatuan2->nama ?? 'N/A') . " - Konversi: " . ($bahan->sub_satuan_2_konversi ?? 'N/A') . " - Nilai: " . ($bahan->sub_satuan_2_nilai ?? 'N/A') . "\n";
        echo "  Sub Satuan 3: " . ($bahan->subSatuan3->nama ?? 'N/A') . " - Konversi: " . ($bahan->sub_satuan_3_konversi ?? 'N/A') . " - Nilai: " . ($bahan->sub_satuan_3_nilai ?? 'N/A') . "\n";
        
        // Test conversion
        $satuanBahan = $bahan->satuan->nama ?? 'unit';
        echo "Converting {$detail->qty_resep} {$detail->satuan_resep} to {$satuanBahan}:\n";
        
        // Test current method
        $currentResult = $bahan->konversiBerdasarkanProduksi($detail->qty_resep, $detail->satuan_resep, $satuanBahan);
        echo "  Current result: {$currentResult}\n";
        
        // Test with nilai instead of konversi
        $testResult = $bahan->convertToSubUnit($detail->qty_resep, $detail->satuan_resep, $satuanBahan);
        echo "  convertToSubUnit result: {$testResult}\n";
        
    } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
        $bahan = $detail->bahanPendukung;
        echo "Material: {$bahan->nama_bahan} (Bahan Pendukung)\n";
        echo "Main Unit: " . ($bahan->satuanRelation->nama ?? 'N/A') . "\n";
        
        // Show conversion data
        echo "Conversion Data:\n";
        echo "  Sub Satuan 1: " . ($bahan->subSatuan1->nama ?? 'N/A') . " - Konversi: " . ($bahan->sub_satuan_1_konversi ?? 'N/A') . " - Nilai: " . ($bahan->sub_satuan_1_nilai ?? 'N/A') . "\n";
        echo "  Sub Satuan 2: " . ($bahan->subSatuan2->nama ?? 'N/A') . " - Konversi: " . ($bahan->sub_satuan_2_konversi ?? 'N/A') . " - Nilai: " . ($bahan->sub_satuan_2_nilai ?? 'N/A') . "\n";
        echo "  Sub Satuan 3: " . ($bahan->subSatuan3->nama ?? 'N/A') . " - Konversi: " . ($bahan->sub_satuan_3_konversi ?? 'N/A') . " - Nilai: " . ($bahan->sub_satuan_3_nilai ?? 'N/A') . "\n";
        
        // Test conversion
        $satuanBahan = $bahan->satuanRelation->nama ?? 'unit';
        echo "Converting {$detail->qty_resep} {$detail->satuan_resep} to {$satuanBahan}:\n";
        
        // Test current method
        $currentResult = $bahan->konversiBerdasarkanProduksi($detail->qty_resep, $detail->satuan_resep, $satuanBahan);
        echo "  Current result: {$currentResult}\n";
        
        // Test with nilai instead of konversi
        $testResult = $bahan->convertToSubUnit($detail->qty_resep, $detail->satuan_resep, $satuanBahan);
        echo "  convertToSubUnit result: {$testResult}\n";
    }
    
    echo "\n";
}