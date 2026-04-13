<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUGGING PURCHASE DETAILS ===\n\n";

// Check if jumlah_satuan_utama column exists
$columns = \Schema::getColumnListing('pembelian_details');
echo "Columns in pembelian_details: " . implode(', ', $columns) . "\n\n";

// Check purchase details
$totalDetails = \DB::table('pembelian_details')->count();
echo "Total purchase details: {$totalDetails}\n";

if (in_array('jumlah_satuan_utama', $columns)) {
    $withJumlahSatuanUtama = \DB::table('pembelian_details')->whereNotNull('jumlah_satuan_utama')->count();
    echo "Details with jumlah_satuan_utama: {$withJumlahSatuanUtama}\n";
} else {
    echo "jumlah_satuan_utama column does not exist\n";
}

// Show sample purchase details for Ayam Potong
echo "\nSample purchase details for Ayam Potong (ID: 1):\n";
$ayamDetails = \DB::table('pembelian_details')
    ->where('bahan_baku_id', 1)
    ->get();

foreach ($ayamDetails as $detail) {
    echo "  Detail ID: {$detail->id}\n";
    echo "  Jumlah: {$detail->jumlah}\n";
    echo "  Satuan: {$detail->satuan}\n";
    echo "  Faktor Konversi: " . ($detail->faktor_konversi ?? 'null') . "\n";
    if (isset($detail->jumlah_satuan_utama)) {
        echo "  Jumlah Satuan Utama: {$detail->jumlah_satuan_utama}\n";
    }
    echo "  Calculated: " . ($detail->jumlah * ($detail->faktor_konversi ?? 1)) . "\n";
    echo "\n";
}