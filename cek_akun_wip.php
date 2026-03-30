<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CEK AKUN PERSEDIAAN DALAM PROSES (WIP)" . PHP_EOL;
echo "======================================" . PHP_EOL;

// Cek semua akun dengan kata "proses" atau "WIP"
$wipAccounts = \DB::table('coas')
    ->where('nama_akun', 'like', '%proses%')
    ->orWhere('nama_akun', 'like', '%WIP%')
    ->orWhere('kode_akun', 'like', '160%')
    ->orderBy('kode_akun')
    ->get();

echo "Akun yang mungkin untuk WIP:" . PHP_EOL;
foreach ($wipAccounts as $account) {
    echo "- " . $account->kode_akun . " | " . $account->nama_akun . PHP_EOL;
}

// Cek akun 1604 secara spesifik
echo PHP_EOL . "Cek akun 1604:" . PHP_EOL;
$akun1604 = \DB::table('coas')->where('kode_akun', '1604')->first();
if ($akun1604) {
    echo "- " . $akun1604->kode_akun . " | " . $akun1604->nama_akun . " | " . $akun1604->tipe_akun . PHP_EOL;
} else {
    echo "- Akun 1604 tidak ditemukan" . PHP_EOL;
}

// Cek semua akun 1600 series
echo PHP_EOL . "Semua akun 1600 series:" . PHP_EOL;
$akun1600Series = \DB::table('coas')
    ->where('kode_akun', 'like', '160%')
    ->orderBy('kode_akun')
    ->get();

foreach ($akun1600Series as $account) {
    echo "- " . $account->kode_akun . " | " . $account->nama_akun . " | " . $account->tipe_akun . PHP_EOL;
}
