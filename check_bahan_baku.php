<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;

echo "=== DAFTAR BAHAN BAKU ===\n";
$bahanBakus = BahanBaku::select('id', 'nama_bahan', 'stok')->get();

foreach ($bahanBakus as $bahan) {
    echo "ID: {$bahan->id} | {$bahan->nama_bahan} | Stok: {$bahan->stok}\n";
}

echo "\n=== MENCARI AYAM POTONG ===\n";
$ayamPotong = BahanBaku::where('nama_bahan', 'like', '%ayam%')->get();
foreach ($ayamPotong as $ayam) {
    echo "ID: {$ayam->id} | {$ayam->nama_bahan} | Stok: {$ayam->stok}\n";
}

?>