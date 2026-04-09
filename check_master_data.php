<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING MASTER DATA SATUAN ===\n";

// Check bahan pendukung dengan satuan
$items = [
    ['table' => 'bahan_pendukungs', 'ids' => [14, 18, 21, 22]], // Minyak Goreng, Lada, Bubuk Bawang Putih, Kemasan
    ['table' => 'bahan_bakus', 'ids' => [6]] // Ayam Kampung
];

foreach($items as $itemGroup) {
    echo "\n=== " . strtoupper($itemGroup['table']) . " ===\n";
    
    foreach($itemGroup['ids'] as $id) {
        $item = DB::table($itemGroup['table'])->where('id', $id)->first();
        if ($item) {
            echo "ID: {$item->id}\n";
            echo "Nama: {$item->nama_bahan}\n";
            echo "Satuan ID: " . ($item->satuan_id ?? 'NULL') . "\n";
            
            // Get satuan info
            if (isset($item->satuan_id) && $item->satuan_id) {
                $satuan = DB::table('satuans')->where('id', $item->satuan_id)->first();
                echo "Satuan Nama: " . ($satuan->nama ?? 'N/A') . "\n";
            }
            
            echo "Harga Satuan: " . ($item->harga_satuan ?? 'NULL') . "\n";
            echo "Sub Satuan 1 ID: " . ($item->sub_satuan_1_id ?? 'NULL') . "\n";
            echo "Sub Satuan 1 Nilai: " . ($item->sub_satuan_1_nilai ?? 'NULL') . "\n";
            echo "Sub Satuan 2 ID: " . ($item->sub_satuan_2_id ?? 'NULL') . "\n";
            echo "Sub Satuan 2 Nilai: " . ($item->sub_satuan_2_nilai ?? 'NULL') . "\n";
            echo "---\n";
        }
    }
}