<?php

/**
 * Debug script untuk melihat kenapa stok tidak berubah saat edit
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG STOK ISSUE ===\n\n";

// Ambil satu bahan pendukung untuk test
$bahanPendukung = \App\Models\BahanPendukung::where('user_id', auth()->id())->first();

if (!$bahanPendukung) {
    echo "Tidak ada BahanPendukung untuk user ini\n";
    exit;
}

echo "Bahan Pendukung: {$bahanPendukung->nama_bahan}\n";
echo "ID: {$bahanPendukung->id}\n";
echo "Saldo Awal (DB): {$bahanPendukung->saldo_awal}\n\n";

// Cek semua StockMovement untuk item ini
echo "=== STOCK MOVEMENTS ===\n";
$movements = \App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $bahanPendukung->id)
    ->where('user_id', $bahanPendukung->user_id)
    ->get();

echo "Total movements: " . $movements->count() . "\n\n";

foreach ($movements as $movement) {
    echo "- ID: {$movement->id}\n";
    echo "  Direction: {$movement->direction}\n";
    echo "  Qty: {$movement->qty}\n";
    echo "  Ref Type: {$movement->ref_type}\n";
    echo "  Keterangan: {$movement->keterangan}\n";
    echo "  Created: {$movement->created_at}\n\n";
}

// Hitung stock dengan berbagai cara
echo "=== PERHITUNGAN STOK ===\n";

// Cara 1: Semua movements
$stockInAll = \App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $bahanPendukung->id)
    ->where('user_id', $bahanPendukung->user_id)
    ->where('direction', 'in')
    ->sum('qty');

$stockOutAll = \App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $bahanPendukung->id)
    ->where('user_id', $bahanPendukung->user_id)
    ->where('direction', 'out')
    ->sum('qty');

echo "1. Semua movements:\n";
echo "   Stock In: {$stockInAll}\n";
echo "   Stock Out: {$stockOutAll}\n";
echo "   Net: " . ($stockInAll - $stockOutAll) . "\n";
echo "   Stok = saldo_awal + net = {$bahanPendukung->saldo_awal} + " . ($stockInAll - $stockOutAll) . " = " . ($bahanPendukung->saldo_awal + $stockInAll - $stockOutAll) . "\n\n";

// Cara 2: Exclude stock_adjustment
$stockInExclude = \App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $bahanPendukung->id)
    ->where('user_id', $bahanPendukung->user_id)
    ->where('direction', 'in')
    ->where('ref_type', '!=', 'stock_adjustment')
    ->sum('qty');

$stockOutExclude = \App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $bahanPendukung->id)
    ->where('user_id', $bahanPendukung->user_id)
    ->where('direction', 'out')
    ->where('ref_type', '!=', 'stock_adjustment')
    ->sum('qty');

echo "2. Exclude stock_adjustment:\n";
echo "   Stock In: {$stockInExclude}\n";
echo "   Stock Out: {$stockOutExclude}\n";
echo "   Net: " . ($stockInExclude - $stockOutExclude) . "\n";
echo "   Stok = saldo_awal + net = {$bahanPendukung->saldo_awal} + " . ($stockInExclude - $stockOutExclude) . " = " . ($bahanPendukung->saldo_awal + $stockInExclude - $stockOutExclude) . "\n\n";

// Cara 3: Dari accessor
echo "3. Dari accessor:\n";
echo "   stok_real_time: {$bahanPendukung->stok_real_time}\n";
echo "   stok: {$bahanPendukung->stok}\n\n";

echo "=== KESIMPULAN ===\n";
if ($bahanPendukung->stok == $bahanPendukung->saldo_awal) {
    echo "✅ Stok sama dengan saldo_awal (tidak ada movements yang dihitung)\n";
} else {
    echo "❌ Stok berbeda dengan saldo_awal\n";
    echo "   Saldo Awal: {$bahanPendukung->saldo_awal}\n";
    echo "   Stok Tampil: {$bahanPendukung->stok}\n";
    echo "   Selisih: " . ($bahanPendukung->stok - $bahanPendukung->saldo_awal) . "\n";
}

echo "\n=== SELESAI ===\n";
