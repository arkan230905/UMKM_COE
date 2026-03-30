<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek COA yang sudah ada dengan kode akun yang dibutuhkan ===\n";

$neededCoas = [
    '5101' => 'HPP Penjualan',
    '5111' => 'Beban Konsumsi Ayam Kampung',
    '5112' => 'Beban Konsumsi Minyak Goreng',
    '5113' => 'Beban Konsumsi Gas',
    '5114' => 'Beban Konsumsi Ketumbar Bubuk',
    '5115' => 'Beban Konsumsi Bawang Putih',
    '5116' => 'Beban Konsumsi Merica Bubuk',
    '5117' => 'Beban Konsumsi Bawang Merah',
    '5118' => 'Beban Konsumsi Kemasan',
];

foreach($neededCoas as $kodeAkun => $namaAkun) {
    $existingCoa = DB::table('coas')->where('kode_akun', $kodeAkun)->first();
    if($existingCoa) {
        echo "✓ Kode {$kodeAkun} sudah ada dengan ID {$existingCoa->id}: {$existingCoa->nama_akun}\n";
    } else {
        echo "❌ Kode {$kodeAkun} belum ada\n";
    }
}

echo "\n=== Cek semua COA dengan kode 5xxx (Beban) ===\n";
$expenseCoas = DB::table('coas')->where('kode_akun', 'like', '5%')->orderBy('kode_akun')->get();
foreach($expenseCoas as $coa) {
    echo "ID {$coa->id}: {$coa->kode_akun} - {$coa->nama_akun}\n";
}
