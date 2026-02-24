<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek Data Pembelian yang Belum di-Sync ===\n";

// Get Ayam Kampung
$ayam = \App\Models\BahanBaku::find(2);
echo "Ayam Kampung ID: {$ayam->id}, Nama: {$ayam->nama_bahan}\n";

// Get all purchases for Ayam Kampung
$purchases = \DB::table('pembelian_details')
    ->join('pembelians', 'pembelians.id', '=', 'pembelian_details.pembelian_id')
    ->where('pembelian_details.bahan_baku_id', 2)
    ->where('pembelian_details.tipe_item', 'material')
    ->select('pembelians.*', 'pembelian_details.*')
    ->orderBy('pembelians.tanggal', 'asc')
    ->get();

echo "\nData Pembelian di Database:\n";
if ($purchases->count() > 0) {
    foreach ($purchases as $p) {
        echo "Tanggal: {$p->tanggal}, No: {$p->nomor_pembelian}, Qty: {$p->jumlah} {$p->satuan}, Harga: Rp " . number_format($p->harga_satuan, 2) . ", Total: Rp " . number_format($p->subtotal, 0) . "\n";
    }
} else {
    echo "Tidak ada data pembelian untuk Ayam Kampung di tabel pembelian_details\n";
}

// Check stock movements
echo "\nData Stock Movements:\n";
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->where('ref_type', 'purchase')
    ->orderBy('tanggal', 'asc')
    ->get();

if ($movements->count() > 0) {
    foreach ($movements as $m) {
        echo "Tanggal: {$m->tanggal}, Ref: {$m->ref_type}#{$m->ref_id}, Qty: {$m->qty} {$m->satuan}, Total: Rp " . number_format($m->total_cost, 0) . "\n";
    }
} else {
    echo "Tidak ada stock movements dari pembelian\n";
}

echo "\n=== Analisis ===\n";
if ($purchases->count() > 0 && $movements->count() > 0) {
    echo "Ada data pembelian dan stock movements, tapi mungkin tidak sinkron.\n";
    echo "Perlu sinkronisasi data pembelian ke stock movements.\n";
} elseif ($purchases->count() > 0) {
    echo "Ada data pembelian tapi tidak ada stock movements.\n";
    echo "Perlu membuat stock movements dari data pembelian.\n";
} else {
    echo "Tidak ada data pembelian sama sekali.\n";
    echo "Stock movements yang ada hanya adjustment manual.\n";
}

// Check if user wants to sync
echo "\n=== Opsi Sinkronisasi ===\n";
echo "1. Hapus semua stock movements lama dan buat dari data pembelian\n";
echo "2. Update stock movements yang ada untuk match dengan data pembelian\n";
echo "3. Biarkan data adjustment manual tetap tambahkan stock movements dari pembelian\n";
