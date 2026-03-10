<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK LOGIKA BTKL SEMUA PRODUK ===" . PHP_EOL;

// Cek semua produk
$produks = \App\Models\Produk::all();
foreach ($produks as $produk) {
    echo PHP_EOL . "Produk: {$produk->nama_produk} (ID: {$produk->id})" . PHP_EOL;
    
    // Cek BomJobCosting
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
    if ($bomJobCosting) {
        echo "  BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
        echo "  Total BTKL: {$bomJobCosting->total_btkl}" . PHP_EOL;
        echo "  Total BOP: {$bomJobCosting->total_bop}" . PHP_EOL;
        
        // Cek data BTKL
        $btklData = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
            ->where('bom_job_costing_id', $bomJobCosting->id)
            ->get();
        
        echo "  Jumlah data BTKL: " . $btklData->count() . PHP_EOL;
        foreach ($btklData as $btkl) {
            echo "    - {$btkl->nama_proses}: {$btkl->subtotal}" . PHP_EOL;
        }
        
        // Cek data BOP
        $bopData = \Illuminate\Support\Facades\DB::table('bom_job_bop')
            ->where('bom_job_costing_id', $bomJobCosting->id)
            ->get();
        
        echo "  Jumlah data BOP: " . $bopData->count() . PHP_EOL;
        foreach ($bopData as $bop) {
            echo "    - {$bop->nama_bop}: {$bop->jumlah}" . PHP_EOL;
        }
    } else {
        echo "  Tidak ada BomJobCosting" . PHP_EOL;
    }
}

echo PHP_EOL . "=== ANALISIS LOGIKA ===" . PHP_EOL;

// Cek apakah semua produk menggunakan logika yang sama
$ayamKetumbar = \App\Models\Produk::find(1);
$opakGeulis = \App\Models\Produk::find(2);
$ketempling = \App\Models\Produk::find(3);

echo "Ayam Ketumbar BTKL: " . ($ayamKetumbar->harga_pokok ?? 0) . PHP_EOL;
echo "Opak Geulis BTKL: " . ($opakGeulis->harga_pokok ?? 0) . PHP_EOL;
echo "Ketempling BTKL: " . ($ketempling->harga_pokok ?? 0) . PHP_EOL;
