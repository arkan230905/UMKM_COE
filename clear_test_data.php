<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Menghapus Data Test Stock Movements ===\n";

// Hapus semua stock movements untuk bahan pendukung (support type)
$deleted = \App\Models\StockMovement::where('item_type', 'support')->delete();

echo "Menghapus $deleted stock movements untuk bahan pendukung...\n";

// Cek data pembelian riil dari tabel pembelian_details
echo "\n=== Cek Data Pembelian Riil ===\n";
$purchaseDetails = \DB::table('pembelian_details')
    ->join('pembelians', 'pembelians.id', '=', 'pembelian_details.pembelian_id')
    ->where('pembelian_details.tipe_item', 'bahan_pendukung')
    ->select('pembelians.tanggal', 'pembelians.nomor_pembelian', 'pembelian_details.bahan_pendukung_id', 'pembelian_details.jumlah', 'pembelian_details.satuan', 'pembelian_details.harga_satuan', 'pembelian_details.subtotal')
    ->orderBy('pembelians.tanggal', 'asc')
    ->get();

if ($purchaseDetails->count() > 0) {
    echo "Data pembelian riil untuk bahan pendukung:\n";
    foreach ($purchaseDetails as $pd) {
        $bahanPendukung = \App\Models\BahanPendukung::find($pd->bahan_pendukung_id);
        echo "Tanggal: {$pd->tanggal}, No: {$pd->nomor_pembelian}, Item: " . ($bahanPendukung->nama_bahan ?? 'Unknown') . ", Qty: {$pd->jumlah} {$pd->satuan}, Harga: Rp " . number_format($pd->harga_satuan, 2) . ", Total: Rp " . number_format($pd->subtotal, 0) . "\n";
    }
} else {
    echo "❌ Tidak ada data pembelian riil untuk bahan pendukung di database\n";
    echo "Ini sebabnya tidak ada data pembelian yang muncul di kartu stok\n";
}

// Cek stok master data
echo "\n=== Cek Stok Master Data ===\n";
$bahanPendukungs = \App\Models\BahanPendukung::where('stok', '>', 0)->get();
if ($bahanPendukungs->count() > 0) {
    echo "Stok master data untuk bahan pendukung:\n";
    foreach ($bahanPendukungs as $bp) {
        echo "- {$bp->nama_bahan}: {$bp->stok} " . ($bp->satuanRelation->nama_satuan ?? 'N/A') . " @ Rp " . number_format($bp->harga_satuan, 2) . "\n";
    }
} else {
    echo "Tidak ada stok master data untuk bahan pendukung\n";
}

echo "\n=== Rekomendasi ===\n";
echo "1. Jika ada pembelian riil, sistem akan menampilkan data tersebut\n";
echo "2. Jika tidak ada pembelian riil, kartu stok akan kosong atau hanya menampilkan saldo awal dari master data\n";
echo "3. Data test sudah dihapus, sekarang hanya data riil yang akan muncul\n";
