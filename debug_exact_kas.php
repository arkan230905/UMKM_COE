<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Coa;

echo "=== DEBUG: Exact Kas Query ===" . PHP_EOL;

// Test exact query
$akunKas = Coa::whereIn('kode_akun', ['101', '102'])
    ->orderBy('kode_akun')
    ->get();

echo "Total akun kas dengan exact query: " . $akunKas->count() . PHP_EOL . PHP_EOL;

foreach ($akunKas as $kas) {
    echo "- ID: {$kas->id} | Kode: {$kas->kode_akun} | Nama: {$kas->nama_akun}" . PHP_EOL;
}

echo PHP_EOL . "=== DEBUG: Check if 101 and 102 exist ===" . PHP_EOL;

$kas101 = Coa::where('kode_akun', '101')->first();
$kas102 = Coa::where('kode_akun', '102')->first();

echo "101 exists: " . ($kas101 ? 'YES' : 'NO') . PHP_EOL;
if ($kas101) {
    echo "  - ID: {$kas101->id}, Nama: {$kas101->nama_akun}" . PHP_EOL;
}

echo "102 exists: " . ($kas102 ? 'YES' : 'NO') . PHP_EOL;
if ($kas102) {
    echo "  - ID: {$kas102->id}, Nama: {$kas102->nama_akun}" . PHP_EOL;
}
