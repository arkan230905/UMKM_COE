<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING STOCK REPORT DISPLAY PRICES ===\n";

// Simulate how the stock report calculates and displays prices
echo "\n1. MINYAK GORENG CALCULATION:\n";
$minyakMovements = DB::table('stock_movements')
    ->where('item_type', 'support')
    ->where('item_id', 14)
    ->orderBy('tanggal')
    ->get();

$runningQty = 0;
$runningValue = 0;

echo "Stock movements calculation:\n";
foreach ($minyakMovements as $m) {
    if ($m->direction == 'in') {
        $runningQty += $m->qty;
        $runningValue += $m->total_cost;
    } else {
        $runningQty -= $m->qty;
        $runningValue -= $m->total_cost;
    }
    echo "  {$m->tanggal}: {$m->direction} {$m->qty}L @ Rp{$m->unit_cost} → Running: {$runningQty}L, Value: Rp{$runningValue}\n";
}

$finalAvgPrice = $runningQty > 0 ? $runningValue / $runningQty : 0;
echo "Final weighted average: Rp " . number_format($finalAvgPrice, 2) . " per Liter\n";

// Check what the master data shows
$minyak = DB::table('bahan_pendukungs')->where('id', 14)->first();
echo "Master data shows: Rp " . number_format($minyak->harga_satuan, 2) . " per Liter\n";

if (abs($finalAvgPrice - $minyak->harga_satuan) > 1) {
    echo "❌ Discrepancy between calculated and master data!\n";
} else {
    echo "✅ Master data matches calculation\n";
}

echo "\n2. KEMASAN CALCULATION:\n";
$kemasanMovements = DB::table('stock_movements')
    ->where('item_type', 'support')
    ->where('item_id', 22)
    ->orderBy('tanggal')
    ->get();

$runningQty = 0;
$runningValue = 0;

echo "Stock movements calculation:\n";
foreach ($kemasanMovements as $m) {
    if ($m->direction == 'in') {
        $runningQty += $m->qty;
        $runningValue += $m->total_cost;
    } else {
        $runningQty -= $m->qty;
        $runningValue -= $m->total_cost;
    }
    echo "  {$m->tanggal}: {$m->direction} {$m->qty} pcs @ Rp{$m->unit_cost} → Running: {$runningQty} pcs, Value: Rp{$runningValue}\n";
}

$finalAvgPrice = $runningQty > 0 ? $runningValue / $runningQty : 0;
echo "Final weighted average: Rp " . number_format($finalAvgPrice, 2) . " per Pieces\n";

// Check what the master data shows
$kemasan = DB::table('bahan_pendukungs')->where('id', 22)->first();
echo "Master data shows: Rp " . number_format($kemasan->harga_satuan, 2) . " per Pieces\n";

if (abs($finalAvgPrice - $kemasan->harga_satuan) > 1) {
    echo "❌ Discrepancy between calculated and master data!\n";
} else {
    echo "✅ Master data matches calculation\n";
}

echo "\n3. WHAT STOCK REPORT SHOULD SHOW:\n";
echo "Minyak Goreng:\n";
echo "  - Per Liter: Rp " . number_format($finalAvgPrice, 2) . "\n";
echo "  - Per Mililiter: Rp " . number_format($finalAvgPrice / 1000, 2) . "\n";

$kemasanFinalPrice = ($kemasan->stok > 0) ? (($kemasan->stok * $kemasan->harga_satuan) / $kemasan->stok) : $kemasan->harga_satuan;
echo "Kemasan:\n";
echo "  - Per Pieces: Rp " . number_format($kemasan->harga_satuan, 2) . "\n";

echo "\n4. BOM COMPARISON:\n";
$minyakBom = DB::table('bom_job_bahan_pendukung')->where('bahan_pendukung_id', 14)->first();
$kemasanBom = DB::table('bom_job_bahan_pendukung')->where('bahan_pendukung_id', 22)->first();

$expectedMinyakBomPrice = $minyak->harga_satuan / 1000; // Convert to per mL
echo "Minyak Goreng BOM should be: Rp " . number_format($expectedMinyakBomPrice, 2) . " per mL\n";
echo "Minyak Goreng BOM currently: Rp " . number_format($minyakBom->harga_satuan, 2) . " per mL\n";

echo "Kemasan BOM should be: Rp " . number_format($kemasan->harga_satuan, 2) . " per Pieces\n";
echo "Kemasan BOM currently: Rp " . number_format($kemasanBom->harga_satuan, 2) . " per Pieces\n";