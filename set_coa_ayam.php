<?php

// Script to set COA Persediaan for Ayam items
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Setting COA for Ayam Items ===\n\n";

// Set COA for Ayam Potong
$ayamPotong = \App\Models\BahanBaku::where('nama_bahan', 'LIKE', '%Ayam Potong%')
    ->where('user_id', 3)
    ->first();

if ($ayamPotong) {
    $ayamPotong->coa_persediaan_id = '1141'; // Pers. Bahan Baku Ayam Potong
    $ayamPotong->save();
    echo "✅ Ayam Potong (ID: {$ayamPotong->id}) - COA set to 1141\n";
} else {
    echo "❌ Ayam Potong not found\n";
}

// Set COA for Ayam Kampung
$ayamKampung = \App\Models\BahanBaku::where('nama_bahan', 'LIKE', '%Ayam Kampung%')
    ->where('user_id', 3)
    ->first();

if ($ayamKampung) {
    $ayamKampung->coa_persediaan_id = '1142'; // Pers. Bahan Baku Ayam Kampung
    $ayamKampung->save();
    echo "✅ Ayam Kampung (ID: {$ayamKampung->id}) - COA set to 1142\n";
} else {
    echo "❌ Ayam Kampung not found\n";
}

// Set COA for other common items if they exist
$mappings = [
    'Jagung' => '1143',
    'Air' => '1150',
    'Minyak Goreng' => '1151',
    'Tepung Terigu' => '1152',
    'Tepung Maizena' => '1153',
    'Lada' => '1154',
];

foreach ($mappings as $namaB => $coaCode) {
    $item = \App\Models\BahanBaku::where('nama_bahan', 'LIKE', "%{$namaB}%")
        ->where('user_id', 3)
        ->first();
    
    if ($item) {
        $item->coa_persediaan_id = $coaCode;
        $item->save();
        echo "✅ {$namaB} (ID: {$item->id}) - COA set to {$coaCode}\n";
    }
}

echo "\n=== Done ===\n";
echo "\nNow refresh the Tambah Pembelian page and preview jurnal should show specific COA names.\n";
