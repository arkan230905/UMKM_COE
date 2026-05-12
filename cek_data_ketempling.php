<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DATA KETAMPLING ===" . PHP_EOL;

// Cek produk ketempling
$produk = \App\Models\Produk::find(3);
if (!$produk) {
    echo "Produk ketempling tidak ditemukan!" . PHP_EOL;
    exit;
}

echo "Produk: {$produk->nama_produk}" . PHP_EOL;
echo "Harga Pokok: " . ($produk->harga_pokok ?? 0) . PHP_EOL;

// Cek BomJobCosting
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
if ($bomJobCosting) {
    echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
    echo "Total BBB: {$bomJobCosting->total_bbb}" . PHP_EOL;
    echo "Total Bahan Pendukung: {$bomJobCosting->total_bahan_pendukung}" . PHP_EOL;
    echo "Total BTKL: {$bomJobCosting->total_btkl}" . PHP_EOL;
    echo "Total BOP: {$bomJobCosting->total_bop}" . PHP_EOL;
} else {
    echo "BomJobCosting tidak ditemukan!" . PHP_EOL;
}

// Cek data BTKL untuk ketempling
$btklData = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
    ->leftJoin('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
    ->leftJoin('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id ?? 0)
    ->get();

echo "Jumlah data BTKL: " . $btklData->count() . PHP_EOL;

// Cek data BOP untuk ketempling
$bopData = \Illuminate\Support\Facades\DB::table('bom_job_bop')
    ->where('bom_job_bop.bom_job_costing_id', $bomJobCosting->id ?? 0)
    ->get();

echo "Jumlah data BOP: " . $bopData->count() . PHP_EOL;

// Cek default BOP values
$defaultBOP = \Illuminate\Support\Facades\DB::table('bops')
    ->where('periode', '2026-02')
    ->get();

echo "Default BOP data count: " . $defaultBOP->count() . PHP_EOL;
foreach ($defaultBOP as $bop) {
    echo "- {$bop->nama_biaya}: {$bop->jumlah}" . PHP_EOL;
}
