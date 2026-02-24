<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Data Pembelian/Penjualan Ayam Kampung yang Sesungguhnya ===\n";

// Get all stock movements for Ayam Kampung
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

echo "\nSemua Stock Movements:\n";
foreach ($movements as $m) {
    echo "Tanggal: {$m->tanggal}, Type: {$m->ref_type}, Direction: {$m->direction}, Qty: {$m->qty} {$m->satuan}, Total: Rp " . number_format($m->total_cost, 0) . "\n";
}

// Check purchases table
echo "\n=== Data dari Tabel Pembelian ===\n";
$purchaseDetails = \DB::table('pembelian_details')
    ->join('pembelians', 'pembelians.id', '=', 'pembelian_details.pembelian_id')
    ->where('pembelian_details.bahan_baku_id', 2)
    ->select('pembelians.tanggal', 'pembelians.no_pembelian', 'pembelian_details.qty', 'pembelian_details.harga', 'pembelian_details.sub_total')
    ->orderBy('pembelians.tanggal', 'asc')
    ->get();

if ($purchaseDetails->count() > 0) {
    foreach ($purchaseDetails as $pd) {
        echo "Tanggal: {$pd->tanggal}, No: {$pd->no_pembelian}, Qty: {$pd->qty}, Harga: Rp " . number_format($pd->harga, 2) . ", Total: Rp " . number_format($pd->sub_total, 0) . "\n";
    }
} else {
    echo "Tidak ada data pembelian untuk Ayam Kampung\n";
}

// Check production/consumption
echo "\n=== Data Produksi/Penggunaan ===\n";
$productionDetails = \DB::table('production_details')
    ->join('productions', 'productions.id', '=', 'production_details.production_id')
    ->where('production_details.bahan_baku_id', 2)
    ->select('productions.tanggal', 'productions.no_production', 'production_details.qty', 'production_details.harga', 'production_details.sub_total')
    ->orderBy('productions.tanggal', 'asc')
    ->get();

if ($productionDetails->count() > 0) {
    foreach ($productionDetails as $pd) {
        echo "Tanggal: {$pd->tanggal}, No: {$pd->no_production}, Qty: {$pd->qty}, Harga: Rp " . number_format($pd->harga, 2) . ", Total: Rp " . number_format($pd->sub_total, 0) . "\n";
    }
} else {
    echo "Tidak ada data produksi untuk Ayam Kampung\n";
}

// Check if there are any manual adjustments
echo "\n=== Data Adjustment Manual ===\n";
$adjustments = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->where('ref_type', 'adjustment')
    ->get();

if ($adjustments->count() > 0) {
    foreach ($adjustments as $adj) {
        echo "Tanggal: {$adj->tanggal}, Direction: {$adj->direction}, Qty: {$adj->qty} {$adj->satuan}, Total: Rp " . number_format($adj->total_cost, 0) . "\n";
    }
} else {
    echo "Tidak ada data adjustment manual\n";
}
