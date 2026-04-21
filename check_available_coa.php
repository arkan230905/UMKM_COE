<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Available COA ===" . PHP_EOL;

// Check available COA for cash/bank
echo PHP_EOL . "Available Kas/Bank COAs:" . PHP_EOL;
$kasBankCOAs = DB::table('coas')
    ->where(function($query) {
        $query->where('nama_akun', 'like', '%kas%')
              ->where('nama_akun', 'not like', '%bank%');
    })
    ->orWhere('kode_akun', '1101')
    ->orWhere('kode_akun', '101')
    ->orWhere('nama_akun', 'like', '%bank%')
    ->orWhere('kode_akun', '1102')
    ->orWhere('kode_akun', '102')
    ->get();

foreach ($kasBankCOAs as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . PHP_EOL;
}

echo PHP_EOL . "Available Penjualan COAs:" . PHP_EOL;
$penjualanCOAs = DB::table('coas')
    ->where('tipe_akun', 'Revenue')
    ->where(function($query) {
        $query->where('nama_akun', 'like', '%penjualan%')
              ->orWhere('nama_akun', 'like', '%sales%')
              ->orWhere('nama_akun', 'like', '%pendapatan%');
    })
    ->orWhere('kode_akun', '4101')
    ->orWhere('kode_akun', '401')
    ->get();

foreach ($penjualanCOAs as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . PHP_EOL;
}

echo PHP_EOL . "Available Material COAs:" . PHP_EOL;
$materialCOAs = DB::table('coas')
    ->where('kode_akun', 'like', '114%')
    ->orWhere('kode_akun', 'like', '115%')
    ->get();

foreach ($materialCOAs as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . PHP_EOL;
}

echo PHP_EOL . "Available BTKL/BOP COAs:" . PHP_EOL;
$expenseCOAs = DB::table('coas')
    ->where('kode_akun', '52')
    ->orWhere('kode_akun', '53')
    ->orWhere('nama_akun', 'like', '%tenaga kerja%')
    ->orWhere('nama_akun', 'like', '%overhead%')
    ->get();

foreach ($expenseCOAs as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . PHP_EOL;
}

echo PHP_EOL . "Available Persediaan Barang Jadi COAs:" . PHP_EOL;
$persediaanCOAs = DB::table('coas')
    ->where('kode_akun', 'like', '116%')
    ->orWhere('nama_akun', 'like', '%barang jadi%')
    ->get();

foreach ($persediaanCOAs as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . PHP_EOL;
}

echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
echo "Use these COA codes in JournalService:" . PHP_EOL;
echo "- Kas: 111 atau 112" . PHP_EOL;
echo "- Bank: 111 (Kas Bank)" . PHP_EOL;
echo "- Penjualan: 41" . PHP_EOL;
echo "- Material: Use COA from each material's coa_persediaan_id" . PHP_EOL;
echo "- BTKL: 52" . PHP_EOL;
echo "- BOP: 53" . PHP_EOL;
echo "- Persediaan Barang Jadi: 1161 atau 1162" . PHP_EOL;
