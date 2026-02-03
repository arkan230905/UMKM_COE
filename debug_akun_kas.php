<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Coa;

echo "=== DEBUG: Akun Kas (1101-1103) ===" . PHP_EOL;

// Cari akun kas (kode 1101-1103 untuk kas dan bank)
$akunKas = Coa::where(function($query) {
        $query->where('kode_akun', 'like', '1101%')  // Kas
              ->orWhere('kode_akun', 'like', '1102%') // Bank
              ->orWhere('kode_akun', 'like', '1103%'); // Kas di Bank
    })
    ->orderBy('kode_akun')
    ->get();

echo "Total akun kas: " . $akunKas->count() . PHP_EOL . PHP_EOL;

foreach ($akunKas as $kas) {
    echo "- ID: {$kas->id} | Kode: {$kas->kode_akun} | Nama: {$kas->nama_akun}" . PHP_EOL;
}

echo PHP_EOL . "=== DEBUG: Semua COA dengan kode 1*** ===" . PHP_EOL;

$allCoa1 = Coa::where('kode_akun', 'like', '1%')
    ->orderBy('kode_akun')
    ->get();

echo "Total COA kode 1: " . $allCoa1->count() . PHP_EOL . PHP_EOL;

foreach ($allCoa1 as $coa) {
    echo "- {$coa->kode_akun} - {$coa->nama_akun}" . PHP_EOL;
}
