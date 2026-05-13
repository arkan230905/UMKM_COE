<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK MANUAL ADJUSTMENT ===\n\n";

// Cari data adjustment untuk item_id = 2 (material)
$adjustments = \DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 2)
    ->where('ref_type', 'adjustment')
    ->orderBy('tanggal')
    ->get();

if ($adjustments->isEmpty()) {
    echo "❌ Tidak ada data adjustment untuk material ID 2\n";
} else {
    echo "✅ Ditemukan " . $adjustments->count() . " data adjustment:\n\n";
    
    foreach ($adjustments as $adj) {
        echo "ID: {$adj->id}\n";
        echo "Tanggal: {$adj->tanggal}\n";
        echo "Direction: {$adj->direction}\n";
        echo "Qty: {$adj->qty}\n";
        echo "Unit Cost: {$adj->unit_cost}\n";
        echo "Total Cost: {$adj->total_cost}\n";
        echo "Ref Type: {$adj->ref_type}\n";
        echo "Ref ID: {$adj->ref_id}\n";
        echo "Keterangan: {$adj->keterangan}\n";
        echo "Created at: {$adj->created_at}\n";
        echo "---\n\n";
    }
    
    echo "\n=== SOLUSI ===\n";
    echo "Jika data adjustment ini tidak diketahui asalnya dan ingin dihapus:\n\n";
    
    foreach ($adjustments as $adj) {
        echo "DELETE FROM stock_movements WHERE id = {$adj->id};\n";
    }
}

echo "\n=== CEK SEMUA STOCK MOVEMENTS UNTUK MATERIAL ID 2 ===\n\n";

$allMovements = \DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 2)
    ->orderBy('tanggal')
    ->get();

echo "Total movements: " . $allMovements->count() . "\n\n";

foreach ($allMovements as $m) {
    echo "{$m->tanggal} | {$m->ref_type} | {$m->direction} | Qty: {$m->qty} | ID: {$m->id}\n";
}
