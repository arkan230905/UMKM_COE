<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Coa;

echo "=== DEBUG: COA 503 Detail ===" . PHP_EOL;

$coa503 = Coa::where('kode_akun', '503')->first();

if ($coa503) {
    echo "COA 503 ditemukan:" . PHP_EOL;
    echo "- Kode: {$coa503->kode_akun}" . PHP_EOL;
    echo "- Nama: {$coa503->nama_akun}" . PHP_EOL;
    echo "- Kategori: {$coa503->kategori_akun}" . PHP_EOL;
    echo "- Tipe: {$coa503->tipe_akun}" . PHP_EOL;
    echo "- Header: " . ($coa503->is_akun_header ? 'Yes' : 'No') . PHP_EOL;
    echo "- Saldo Normal: {$coa503->saldo_normal}" . PHP_EOL;
    echo "- Keterangan: " . ($coa503->keterangan ?? 'NULL') . PHP_EOL;
} else {
    echo "COA 503 tidak ditemukan!" . PHP_EOL;
}

echo PHP_EOL . "=== Test Relasi dari BopLainnya ===" . PHP_EOL;

$bopLainnya = \App\Models\BopLainnya::where('kode_akun', '503')
    ->with(['coa'])
    ->first();

if ($bopLainnya) {
    echo "BopLainnya dengan kode 503:" . PHP_EOL;
    echo "- ID: {$bopLainnya->id}" . PHP_EOL;
    echo "- Budget: {$bopLainnya->budget}" . PHP_EOL;
    echo "- COA Relation: " . ($bopLainnya->coa ? 'EXISTS' : 'NULL') . PHP_EOL;
    
    if ($bopLainnya->coa) {
        echo "- COA dari relation: {$bopLainnya->coa->kode_akun} - {$bopLainnya->coa->nama_akun}" . PHP_EOL;
    }
} else {
    echo "BopLainnya dengan kode 503 tidak ditemukan!" . PHP_EOL;
}
