<?php

/**
 * Script untuk memperbaiki harga_satuan di penjualan_details yang bernilai 0
 * Script ini akan mengisi harga_satuan dengan harga_jual dari produk
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PenjualanDetail;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

echo "Memulai perbaikan harga_satuan di penjualan_details...\n\n";

// Ambil semua detail penjualan yang harga_satuannya 0 atau null
$details = PenjualanDetail::with('produk')
    ->where(function($query) {
        $query->whereNull('harga_satuan')
              ->orWhere('harga_satuan', 0);
    })
    ->get();

echo "Ditemukan " . $details->count() . " detail penjualan dengan harga_satuan = 0 atau null\n\n";

$updated = 0;
$skipped = 0;

foreach ($details as $detail) {
    if (!$detail->produk) {
        echo "SKIP: Detail ID {$detail->id} - Produk tidak ditemukan\n";
        $skipped++;
        continue;
    }
    
    $hargaJual = $detail->produk->harga_jual ?? 0;
    
    if ($hargaJual == 0) {
        echo "SKIP: Detail ID {$detail->id} - Produk '{$detail->produk->nama_produk}' tidak memiliki harga_jual\n";
        $skipped++;
        continue;
    }
    
    // Update harga_satuan
    $detail->harga_satuan = $hargaJual;
    $detail->save();
    
    echo "UPDATE: Detail ID {$detail->id} - Produk '{$detail->produk->nama_produk}' - Harga: Rp " . number_format($hargaJual, 0, ',', '.') . "\n";
    $updated++;
}

echo "\n===========================================\n";
echo "Selesai!\n";
echo "Total diupdate: {$updated}\n";
echo "Total diskip: {$skipped}\n";
echo "===========================================\n";
