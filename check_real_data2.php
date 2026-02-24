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
    ->where('pembelian_details.tipe_item', 'material')
    ->select('pembelians.tanggal', 'pembelians.nomor_pembelian', 'pembelian_details.jumlah', 'pembelian_details.satuan', 'pembelian_details.harga_satuan', 'pembelian_details.subtotal')
    ->orderBy('pembelians.tanggal', 'asc')
    ->get();

if ($purchaseDetails->count() > 0) {
    foreach ($purchaseDetails as $pd) {
        echo "Tanggal: {$pd->tanggal}, No: {$pd->nomor_pembelian}, Qty: {$pd->jumlah} {$pd->satuan}, Harga: Rp " . number_format($pd->harga_satuan, 2) . ", Total: Rp " . number_format($pd->subtotal, 0) . "\n";
    }
} else {
    echo "Tidak ada data pembelian untuk Ayam Kampung\n";
}

// Check production/consumption
echo "\n=== Data Produksi/Penggunaan ===\n";
// Check if production table exists
if (\Illuminate\Support\Facades\Schema::hasTable('productions')) {
    $productionDetails = \DB::table('production_details')
        ->join('productions', 'productions.id', '=', 'production_details.production_id')
        ->where('production_details.bahan_baku_id', 2)
        ->select('productions.tanggal', 'productions.nomor_production', 'production_details.jumlah', 'production_details.satuan', 'production_details.harga_satuan', 'production_details.subtotal')
        ->orderBy('productions.tanggal', 'asc')
        ->get();

    if ($productionDetails->count() > 0) {
        foreach ($productionDetails as $pd) {
            echo "Tanggal: {$pd->tanggal}, No: {$pd->nomor_production}, Qty: {$pd->jumlah} {$pd->satuan}, Harga: Rp " . number_format($pd->harga_satuan, 2) . ", Total: Rp " . number_format($pd->subtotal, 0) . "\n";
        }
    } else {
        echo "Tidak ada data produksi untuk Ayam Kampung\n";
    }
} else {
    echo "Tabel productions tidak ada\n";
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

echo "\n=== Rekomendasi ===\n";
echo "Berdasarkan data stock movements, transaksi yang ada:\n";
echo "1. 31/01/2026: Adjustment 30 Ekor (ini bukan pembelian riil)\n";
echo "2. 02/02/2026: Purchase 8 Ekor + 10 Ekor\n";
echo "3. 03/02/2026: Purchase 5 Ekor\n";
echo "\nJika ada pembelian riil yang tidak tercatat di stock movements,\n";
echo "maka perlu sinkronisasi data antara tabel pembelian dan stock movements.\n";
