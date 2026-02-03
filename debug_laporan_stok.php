<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG LAPORAN STOK BAHAN PENDUKUNG ===\n\n";

// 1. Cek semua stock movements
echo "1. SEMUA STOCK MOVEMENTS:\n";
$allMovements = \DB::table('stock_movements')->get();
echo "Total stock movements: " . $allMovements->count() . "\n\n";

foreach ($allMovements as $movement) {
    echo "- ID: {$movement->id}\n";
    echo "  Tanggal: {$movement->tanggal}\n";
    echo "  Item Type: {$movement->item_type}\n";
    echo "  Item ID: {$movement->item_id}\n";
    echo "  Direction: {$movement->direction}\n";
    echo "  Qty: {$movement->qty}\n";
    echo "  Unit Cost: {$movement->unit_cost}\n";
    echo "  Ref Type: {$movement->ref_type}\n";
    echo "  Ref ID: {$movement->ref_id}\n";
    echo "---\n";
}

// 2. Cek stock movements untuk bahan pendukung
echo "\n2. STOCK MOVEMENTS BAHAN PENDUKUNG:\n";
$supportMovements = \DB::table('stock_movements')
    ->where('item_type', 'support')
    ->get();

echo "Total support movements: " . $supportMovements->count() . "\n\n";

foreach ($supportMovements as $movement) {
    echo "- ID: {$movement->id}\n";
    echo "  Tanggal: {$movement->tanggal}\n";
    echo "  Item ID: {$movement->item_id}\n";
    echo "  Direction: {$movement->direction}\n";
    echo "  Qty: {$movement->qty}\n";
    echo "  Unit Cost: {$movement->unit_cost}\n";
    echo "  Ref Type: {$movement->ref_type}\n";
    echo "  Ref ID: {$movement->ref_id}\n";
    echo "---\n";
}

// 3. Cek bahan pendukung yang ada
echo "\n3. DATA BAHAN PENDUKUNG:\n";
$bahanPendukungs = \App\Models\BahanPendukung::all();
foreach ($bahanPendukungs as $bp) {
    echo "- ID: {$bp->id}, Nama: {$bp->nama_bahan}, Stok: {$bp->stok}\n";
}

// 4. Cek stock movements untuk Air Bersih
echo "\n4. STOCK MOVEMENTS AIR BERSIH:\n";
$airBersih = \App\Models\BahanPendukung::where('nama_bahan', 'Air Bersih')->first();
if ($airBersih) {
    echo "Air Bersih ID: {$airBersih->id}\n";
    
    $airMovements = \DB::table('stock_movements')
        ->where('item_type', 'support')
        ->where('item_id', $airBersih->id)
        ->get();
    
    echo "Total movements untuk Air Bersih: " . $airMovements->count() . "\n";
    
    foreach ($airMovements as $movement) {
        echo "- Tanggal: {$movement->tanggal}, Direction: {$movement->direction}, Qty: {$movement->qty}, Ref: {$movement->ref_type}#{$movement->ref_id}\n";
    }
} else {
    echo "Air Bersih tidak ditemukan!\n";
}

// 5. Cek periode yang diminta
echo "\n5. CEK PERIODE YANG DIMINTA:\n";
$dariTanggal = '2026-02-01';
$sampaiTanggal = '2026-02-05';

echo "Periode: {$dariTanggal} - {$sampaiTanggal}\n";

$periodMovements = \DB::table('stock_movements')
    ->where('item_type', 'support')
    ->whereBetween('tanggal', [$dariTanggal, $sampaiTanggal])
    ->get();

echo "Total movements dalam periode: " . $periodMovements->count() . "\n";

foreach ($periodMovements as $movement) {
    echo "- Tanggal: {$movement->tanggal}, Item ID: {$movement->item_id}, Direction: {$movement->direction}, Qty: {$movement->qty}\n";
}

echo "\n=== SELESAI ===\n";
