<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug COA Data Issue ===" . PHP_EOL;

// Check what's actually in the coas table
echo PHP_EOL . "=== Raw COA Data ===" . PHP_EOL;

$rawCoas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun', 'saldo_awal')
    ->orderBy('kode_akun')
    ->limit(20)
    ->get();

foreach ($rawCoas as $coa) {
    echo $coa->kode_akun . " - " . $coa->nama_akun . " - " . $coa->tipe_akun . " - " . $coa->kategori_akun . PHP_EOL;
}

// Check specifically for asset accounts
echo PHP_EOL . "=== Asset Accounts Only ===" . PHP_EOL;

$assetAccounts = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun', 'saldo_awal')
    ->where('tipe_akun', 'ASET')
    ->orderBy('kode_akun')
    ->get();

echo "Found " . $assetAccounts->count() . " accounts with tipe_akun = 'ASET'" . PHP_EOL;
foreach ($assetAccounts as $coa) {
    echo $coa->kode_akun . " - " . $coa->nama_akun . " - " . $coa->tipe_akun . PHP_EOL;
}

// Check if there are accounts with different case
echo PHP_EOL . "=== All Account Types (Case Sensitive) ===" . PHP_EOL;

$allTypes = DB::table('coas')
    ->select('tipe_akun', DB::raw('COUNT(*) as count'))
    ->groupBy('tipe_akun')
    ->orderBy('tipe_akun')
    ->get();

foreach ($allTypes as $type) {
    echo $type->tipe_akun . ": " . $type->count . " accounts" . PHP_EOL;
}

// Test the exact filtering logic step by step
echo PHP_EOL . "=== Step-by-Step Filtering Test ===" . PHP_EOL;

$testCoas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun')
    ->orderBy('kode_akun')
    ->get();

echo "Total COAs: " . $testCoas->count() . PHP_EOL;

// Step 1: Filter by account type
$step1 = $testCoas->filter(function($coa) {
    return in_array($coa->tipe_akun, ['Asset', 'asset', 'Aktiva', 'ASET']);
});

echo "After type filter: " . $step1->count() . PHP_EOL;

// Step 2: Filter by account code (starts with 1)
$step2 = $step1->filter(function($coa) {
    return substr($coa->kode_akun, 0, 1) === '1';
});

echo "After code filter (1xx): " . $step2->count() . PHP_EOL;

foreach ($step2 as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . " (" . $coa->tipe_akun . ")" . PHP_EOL;
}

// Check if the issue is with the GROUP BY in the main query
echo PHP_EOL . "=== Check GROUP BY Issue ===" . PHP_EOL;

$groupedCoas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', DB::raw('MIN(tipe_akun) as min_tipe'))
    ->groupBy('kode_akun', 'nama_akun', 'tipe_akun')
    ->orderBy('kode_akun')
    ->limit(10)
    ->get();

echo "GROUP BY results:" . PHP_EOL;
foreach ($groupedCoas as $coa) {
    echo $coa->kode_akun . " - " . $coa->nama_akun . " - " . $coa->tipe_akun . " (MIN: " . $coa->min_tipe . ")" . PHP_EOL;
}
