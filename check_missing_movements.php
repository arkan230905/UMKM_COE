<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Missing Movements for Bahan Pendukung ===" . PHP_EOL;

// Get a specific bahan pendukung
$bahanPendukung = DB::table('bahan_pendukungs')
    ->where('nama_bahan', 'like', '%tepung terigu%')
    ->first();

if ($bahanPendukung) {
    echo "Checking: " . $bahanPendukung->nama_bahan . " (ID: " . $bahanPendukung->id . ")" . PHP_EOL;
    
    // Check all stock movements for this item
    $movements = DB::table('stock_movements')
        ->where('item_type', 'bahan_pendukung')
        ->where('item_id', $bahanPendukung->id)
        ->orderBy('tanggal')
        ->get();
    
    echo PHP_EOL . "All Stock Movements:" . PHP_EOL;
    foreach ($movements as $movement) {
        echo date('Y-m-d', strtotime($movement->tanggal)) . " - " . $movement->ref_type . " - " . $movement->direction . PHP_EOL;
        echo "  Qty: " . $movement->qty . PHP_EOL;
        echo "  Unit Cost: " . $movement->unit_cost . PHP_EOL;
        echo "  Total Cost: " . $movement->total_cost . PHP_EOL;
        echo "---" . PHP_EOL;
    }
    
    // Check specifically for production and purchase movements
    echo PHP_EOL . "=== Production and Purchase Movements ===" . PHP_EOL;
    
    $productionMovements = DB::table('stock_movements')
        ->where('item_type', 'bahan_pendukung')
        ->where('item_id', $bahanPendukung->id)
        ->where('ref_type', 'production')
        ->get();
    
    echo "Production Movements: " . $productionMovements->count() . PHP_EOL;
    foreach ($productionMovements as $m) {
        echo "  " . date('Y-m-d', strtotime($m->tanggal)) . " - Qty: " . $m->qty . PHP_EOL;
    }
    
    $purchaseMovements = DB::table('stock_movements')
        ->where('item_type', 'bahan_pendukung')
        ->where('item_id', $bahanPendukung->id)
        ->where('ref_type', 'purchase')
        ->get();
    
    echo "Purchase Movements: " . $purchaseMovements->count() . PHP_EOL;
    foreach ($purchaseMovements as $m) {
        echo "  " . date('Y-m-d', strtotime($m->tanggal)) . " - Qty: " . $m->qty . PHP_EOL;
    }
    
    // Check if there are any movements at all
    if ($movements->isEmpty()) {
        echo PHP_EOL . "NO STOCK MOVEMENTS FOUND!" . PHP_EOL;
        echo "This explains why the stock report is empty." . PHP_EOL;
    }
}

// Check if the issue is with the continue 2 statement
echo PHP_EOL . "=== Analyzing the continue 2 Impact ===" . PHP_EOL;
echo "The continue 2 statement skips to the next iteration of the outer loop." . PHP_EOL;
echo "This might be skipping ALL movements, not just initial_stock." . PHP_EOL;
echo PHP_EOL;
echo "Problem: continue 2 exits the foreach($movements as $m) loop" . PHP_EOL;
echo "This means we're not processing ANY movements for bahan pendukung." . PHP_EOL;
