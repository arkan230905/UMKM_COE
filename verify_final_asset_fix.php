<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Final Asset Fix ===" . PHP_EOL;

// Test the corrected filtering logic
echo PHP_EOL . "Testing Corrected Asset Filtering:" . PHP_EOL;

$testCoas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun')
    ->orderBy('kode_akun')
    ->get();

echo "Total COAs: " . $testCoas->count() . PHP_EOL;

// Step 1: Filter by account type (now includes 'Aset')
$step1 = $testCoas->filter(function($coa) {
    return in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva', 'ASET', 'Aset']);
});

echo "After corrected type filter: " . $step1->count() . PHP_EOL;

// Step 2: Filter by account code (starts with 1) for ASET LANCAR
$asetLancar = $step1->filter(function($coa) {
    return substr($coa->kode_akun, 0, 1) === '1';
});

echo "ASET LANCAR (1xx): " . $asetLancar->count() . PHP_EOL;
foreach ($asetLancar as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . " (" . $coa->tipe_akun . ")" . PHP_EOL;
}

// Step 3: Filter by account code (starts with 2) for ASET TIDAK LANCAR
$asetTidakLancar = $step1->filter(function($coa) {
    return substr($coa->kode_akun, 0, 1) === '2';
});

echo PHP_EOL . "ASET TIDAK LANCAR (2xx): " . $asetTidakLancar->count() . PHP_EOL;
foreach ($asetTidakLancar as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . " (" . $coa->tipe_akun . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Expected Results ===" . PHP_EOL;
echo "ASET LANCAR should now show all 1xx accounts with tipe_akun = 'Aset'" . PHP_EOL;
echo "ASET TIDAK LANCAR should show all 2xx accounts with tipe_akun = 'Aset'" . PHP_EOL;
echo "Laporan Posisi Keuangan should now display these accounts!" . PHP_EOL;
