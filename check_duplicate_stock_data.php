<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Duplicate Stock Data ===" . PHP_EOL;

// Check stock movements for bahan pendukung
echo PHP_EOL . "=== Stock Movements for Bahan Pendukung ===" . PHP_EOL;

// Get a specific bahan pendukung (e.g., Tepung Terigu)
$bahanPendukung = DB::table('bahan_pendukungs')->where('nama_bahan', 'like', '%tepung terigu%')->first();
if ($bahanPendukung) {
    echo "Checking: " . $bahanPendukung->nama_bahan . " (ID: " . $bahanPendukung->id . ")" . PHP_EOL;
    echo "COA Persediaan: " . $bahanPendukung->coa_persediaan_id . PHP_EOL;
    echo "Saldo Awal: " . $bahanPendukung->saldo_awal . PHP_EOL;
    echo "Harga Satuan: " . $bahanPendukung->harga_satuan . PHP_EOL;
    
    // Check stock movements
    $movements = DB::table('stock_movements')
        ->where('item_type', 'bahan_pendukung')
        ->where('item_id', $bahanPendukung->id)
        ->orderBy('tanggal')
        ->get();
    
    echo PHP_EOL . "Stock Movements:" . PHP_EOL;
    foreach ($movements as $movement) {
        echo date('Y-m-d', strtotime($movement->tanggal)) . " - " . $movement->ref_type . " - " . $movement->direction . PHP_EOL;
        echo "  Qty: " . $movement->qty . PHP_EOL;
        echo "  Ref Type: " . $movement->ref_type . PHP_EOL;
        echo "  Ref ID: " . $movement->ref_id . PHP_EOL;
        echo "---" . PHP_EOL;
    }
}

// Check if there are multiple initial stock entries
echo PHP_EOL . "=== Check for Multiple Initial Stock Entries ===" . PHP_EOL;

$initialStockMovements = DB::table('stock_movements')
    ->where('ref_type', 'initial_stock')
    ->where('item_type', 'bahan_pendukung')
    ->orderBy('item_id')
    ->orderBy('tanggal')
    ->get();

echo "Found " . $initialStockMovements->count() . " initial stock movements for bahan pendukung:" . PHP_EOL;
foreach ($initialStockMovements as $movement) {
    $bahan = DB::table('bahan_pendukungs')->where('id', $movement->item_id)->first();
    echo "- " . $bahan->nama_bahan . " (ID: " . $movement->item_id . ") on " . $movement->tanggal . PHP_EOL;
    echo "  Qty: " . $movement->qty . PHP_EOL;
}

// Check if there are saldo_awal stock movements
echo PHP_EOL . "=== Check for Saldo Awal Stock Movements ===" . PHP_EOL;

$saldoAwalMovements = DB::table('stock_movements')
    ->where('ref_type', 'saldo_awal')
    ->where('item_type', 'bahan_pendukung')
    ->orderBy('item_id')
    ->orderBy('tanggal')
    ->get();

echo "Found " . $saldoAwalMovements->count() . " saldo_awal stock movements for bahan pendukung:" . PHP_EOL;
foreach ($saldoAwalMovements as $movement) {
    $bahan = DB::table('bahan_pendukungs')->where('id', $movement->item_id)->first();
    echo "- " . $bahan->nama_bahan . " (ID: " . $movement->item_id . ") on " . $movement->tanggal . PHP_EOL;
    echo "  Qty: " . $movement->qty . PHP_EOL;
}

// Check all ref_types for bahan pendukung
echo PHP_EOL . "=== All Ref Types for Bahan Pendukung ===" . PHP_EOL;
$allRefTypes = DB::table('stock_movements')
    ->where('item_type', 'bahan_pendukung')
    ->distinct()
    ->pluck('ref_type');

echo "Found ref types: " . implode(', ', $allRefTypes->toArray()) . PHP_EOL;

// Check what the controller actually processes
echo PHP_EOL . "=== Controller Data Processing Analysis ===" . PHP_EOL;

// Simulate what LaporanController does
$movementData = DB::table('stock_movements')
    ->where('item_type', 'bahan_pendukung')
    ->where('item_id', $bahanPendukung->id)
    ->orderBy('tanggal')
    ->get();

echo "Controller will process these movements:" . PHP_EOL;
foreach ($movementData as $m) {
    $keterangan = '';
    switch ($m->ref_type) {
        case 'initial_stock':
            $keterangan = 'Saldo Awal Bulan';
            break;
        case 'saldo_awal':
            $keterangan = 'Stok Awal';
            break;
        case 'purchase':
            $keterangan = 'Pembelian';
            break;
        case 'production':
            $keterangan = 'Pemakaian Produksi';
            break;
        default:
            $keterangan = $m->ref_type;
    }
    
    echo date('Y-m-d', strtotime($m->tanggal)) . " - " . $keterangan . " (" . $m->ref_type . ")" . PHP_EOL;
}
