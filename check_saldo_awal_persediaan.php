<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Saldo Awal Persediaan ===" . PHP_EOL;

// Check bahan baku data
echo PHP_EOL . "=== Bahan Baku Data ===" . PHP_EOL;
$bahanBakus = DB::table('bahan_bakus')
    ->select('id', 'nama', 'saldo_awal', 'harga_satuan', 'coa_persediaan_id')
    ->get();

foreach ($bahanBakus as $bahan) {
    $totalSaldo = $bahan->saldo_awal * $bahan->harga_satuan;
    echo $bahan->nama . PHP_EOL;
    echo "  ID: " . $bahan->id . PHP_EOL;
    echo "  Saldo Awal: " . $bahan->saldo_awal . PHP_EOL;
    echo "  Harga Satuan: " . $bahan->harga_satuan . PHP_EOL;
    echo "  COA Persediaan: " . $bahan->coa_persediaan_id . PHP_EOL;
    echo "  Total Saldo: " . $totalSaldo . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Check bahan pendukung data
echo PHP_EOL . "=== Bahan Pendukung Data ===" . PHP_EOL;
$bahanPendukungs = DB::table('bahan_pendukungs')
    ->select('id', 'nama', 'saldo_awal', 'harga_satuan', 'coa_persediaan_id')
    ->get();

foreach ($bahanPendukungs as $bahan) {
    $totalSaldo = $bahan->saldo_awal * $bahan->harga_satuan;
    echo $bahan->nama . PHP_EOL;
    echo "  ID: " . $bahan->id . PHP_EOL;
    echo "  Saldo Awal: " . $bahan->saldo_awal . PHP_EOL;
    echo "  Harga Satuan: " . $bahan->harga_satuan . PHP_EOL;
    echo "  COA Persediaan: " . $bahan->coa_persediaan_id . PHP_EOL;
    echo "  Total Saldo: " . $totalSaldo . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Check specific items mentioned by user
echo PHP_EOL . "=== Specific Items Check ===" . PHP_EOL;

// Ayam Potong
$ayamPotong = DB::table('bahan_bakus')->where('nama', 'like', '%ayam potong%')->first();
if ($ayamPotong) {
    echo "Ayam Potong:" . PHP_EOL;
    echo "  Saldo Awal: " . $ayamPotong->saldo_awal . PHP_EOL;
    echo "  Harga Satuan: " . $ayamPotong->harga_satuan . PHP_EOL;
    echo "  Total: " . ($ayamPotong->saldo_awal * $ayamPotong->harga_satuan) . PHP_EOL;
    echo "  COA: " . $ayamPotong->coa_persediaan_id . PHP_EOL;
}

// Ayam Kampung
$ayamKampung = DB::table('bahan_bakus')->where('nama', 'like', '%ayam kampung%')->first();
if ($ayamKampung) {
    echo PHP_EOL . "Ayam Kampung:" . PHP_EOL;
    echo "  Saldo Awal: " . $ayamKampung->saldo_awal . PHP_EOL;
    echo "  Harga Satuan: " . $ayamKampung->harga_satuan . PHP_EOL;
    echo "  Total: " . ($ayamKampung->saldo_awal * $ayamKampung->harga_satuan) . PHP_EOL;
    echo "  COA: " . $ayamKampung->coa_persediaan_id . PHP_EOL;
}

// Bebek
$bebek = DB::table('bahan_bakus')->where('nama', 'like', '%bebek%')->first();
if ($bebek) {
    echo PHP_EOL . "Bebek:" . PHP_EOL;
    echo "  Saldo Awal: " . $bebek->saldo_awal . PHP_EOL;
    echo "  Harga Satuan: " . $bebek->harga_satuan . PHP_EOL;
    echo "  Total: " . ($bebek->saldo_awal * $bebek->harga_satuan) . PHP_EOL;
    echo "  COA: " . $bebek->coa_persediaan_id . PHP_EOL;
}

// Bubuk Bawang Putih
$bawangPutih = DB::table('bahan_pendukungs')->where('nama', 'like', '%bawang putih%')->first();
if ($bawangPutih) {
    echo PHP_EOL . "Bubuk Bawang Putih:" . PHP_EOL;
    echo "  Saldo Awal: " . $bawangPutih->saldo_awal . PHP_EOL;
    echo "  Harga Satuan: " . $bawangPutih->harga_satuan . PHP_EOL;
    echo "  Total: " . ($bawangPutih->saldo_awal * $bawangPutih->harga_satuan) . PHP_EOL;
    echo "  COA: " . $bawangPutih->coa_persediaan_id . PHP_EOL;
}

// Calculate expected totals
echo PHP_EOL . "=== Expected Saldo Awal Calculations ===" . PHP_EOL;

// Total bahan baku by COA
$totalBahanBaku = DB::table('bahan_bakus')
    ->where('saldo_awal', '>', 0)
    ->sum(DB::raw('saldo_awal * harga_satuan'));
echo "Total Bahan Baku: " . $totalBahanBaku . PHP_EOL;

// Total bahan pendukung by COA
$totalBahanPendukung = DB::table('bahan_pendukungs')
    ->where('saldo_awal', '>', 0)
    ->sum(DB::raw('saldo_awal * harga_satuan'));
echo "Total Bahan Pendukung: " . $totalBahanPendukung . PHP_EOL;

// Check COA 115 (parent account)
echo PHP_EOL . "=== COA 115 Analysis ===" . PHP_EOL;
$coa115Items = DB::table('bahan_pendukungs')
    ->where('coa_persediaan_id', '115')
    ->where('saldo_awal', '>', 0)
    ->get();

echo "Items under COA 115:" . PHP_EOL;
foreach ($coa115Items as $item) {
    $total = $item->saldo_awal * $item->harga_satuan;
    echo "  " . $item->nama . ": " . $total . PHP_EOL;
}

echo "Total COA 115: " . $coa115Items->sum(function($item) {
    return $item->saldo_awal * $item->harga_satuan;
}) . PHP_EOL;
