<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK BOM JOB COSTING KETAMPLING ===" . PHP_EOL;

// 1. Cek semua BomJobCosting
$allBomJobCosting = \App\Models\BomJobCosting::all();
echo "Total BomJobCosting: " . $allBomJobCosting->count() . PHP_EOL;

foreach ($allBomJobCosting as $bom) {
    echo "- ID: {$bom->id}, Produk: {$bom->produk->nama_produk}, Produk ID: {$bom->produk_id}" . PHP_EOL;
}

// 2. Cek khusus untuk ketempling (produk_id = 3)
$ketemplingBom = \App\Models\BomJobCosting::where('produk_id', 3)->first();
if ($ketemplingBom) {
    echo PHP_EOL . "BomJobCosting Ketempling DITEMUKAN:" . PHP_EOL;
    echo "- ID: {$ketemplingBom->id}" . PHP_EOL;
    echo "- Total BBB: Rp " . number_format($ketemplingBom->total_bbb, 2, ',', '.') . PHP_EOL;
    echo "- Total BTKL: Rp " . number_format($ketemplingBom->total_btkl, 2, ',', '.') . PHP_EOL;
    echo "- Total BOP: Rp " . number_format($ketemplingBom->total_bop, 2, ',', '.') . PHP_EOL;
    echo "- Total HPP: Rp " . number_format($ketemplingBom->total_hpp, 2, ',', '.') . PHP_EOL;
} else {
    echo PHP_EOL . "BomJobCosting Ketempling TIDAK DITEMUKAN!" . PHP_EOL;
}

// 3. Cek apakah ada data BBB, BTKL, BOP untuk ketempling
echo PHP_EOL . "CEK DATA LAINNYA:" . PHP_EOL;

// Cek BBB
$bbbCount = \App\Models\BomJobBBB::whereHas('bomJobCosting', function($query) {
    $query->where('produk_id', 3);
})->count();
echo "Jumlah BomJobBBB untuk ketempling: {$bbbCount}" . PHP_EOL;

// Cek BTKL
$btklCount = \App\Models\BomJobBTKL::whereHas('bomJobCosting', function($query) {
    $query->where('produk_id', 3);
})->count();
echo "Jumlah BomJobBTKL untuk ketempling: {$btklCount}" . PHP_EOL;

// Cek BOP
$bopCount = \App\Models\BomJobBOP::whereHas('bomJobCosting', function($query) {
    $query->where('produk_id', 3);
})->count();
echo "Jumlah BomJobBOP untuk ketempling: {$bopCount}" . PHP_EOL;
