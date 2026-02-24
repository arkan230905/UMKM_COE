<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Air dengan Data Riil ===\n";

// Simulate controller logic for Air
$tipe = 'bahan_pendukung';
$from = '2026-02-01';
$to = '2026-02-03';
$itemId = 14; // Air

// Get item
$item = \App\Models\BahanPendukung::find($itemId);

// Calculate initial stock
$saldoAwalQty = 0.0;
$saldoAwalNilai = 0.0;

$before = \App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $itemId)
    ->whereDate('tanggal', '<', $from)
    ->get();

if ($before->isEmpty() && $item->stok > 0) {
    $saldoAwalQty = (float)($item->stok ?? 0);
    $saldoAwalNilai = $saldoAwalQty * (float)($item->harga_satuan ?? 0);
}

echo "Saldo awal per $from: $saldoAwalQty Liter = Rp " . number_format($saldoAwalNilai, 0) . "\n";

// Get movements
$movements = \App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $itemId)
    ->whereDate('tanggal', '>=', $from)
    ->whereDate('tanggal', '<=', $to)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

echo "Stock movements dalam periode: " . $movements->count() . " transaksi\n";

if ($movements->count() > 0) {
    foreach ($movements as $m) {
        echo "- {$m->tanggal}: {$m->ref_type} #{$m->ref_id}, {$m->qty} Liter @ Rp " . number_format($m->unit_cost, 2) . " = Rp " . number_format($m->total_cost, 0) . "\n";
    }
} else {
    echo "❌ Tidak ada stock movements untuk Air dalam periode ini\n";
}

echo "\n=== Hasil Kartu Stok yang Diharapkan ===\n";
echo "Jika tidak ada stock movements, kartu stok akan:\n";
echo "1. Menampilkan saldo awal dari master data (50 Liter @ Rp 1.000 = Rp 50.000)\n";
echo "2. Tidak ada transaksi pembelian/produksi\n";
echo "3. Total stok tetap 50 Liter\n";

echo "\n=== Solusi ===\n";
echo "Jika Anda ingin melihat data pembelian, Anda perlu:\n";
echo "1. Membuat transaksi pembelian untuk Air di halaman pembelian\n";
echo "2. Atau membuat adjustment manual jika ada perubahan stok\n";
echo "3. Data pembelian akan otomatis muncul di kartu stok setelah dibuat\n";
