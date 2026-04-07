<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING MINYAK GORENG AND KEMASAN BOM PRICES ===\n";

// 1. Calculate correct Minyak Goreng weighted average
echo "\n1. FIXING MINYAK GORENG...\n";
$minyakMovements = DB::table('stock_movements')
    ->where('item_type', 'support')
    ->where('item_id', 14)
    ->orderBy('tanggal')
    ->get();

$runningQty = 0;
$runningValue = 0;

foreach ($minyakMovements as $m) {
    if ($m->direction == 'in') {
        $runningQty += $m->qty;
        $runningValue += $m->total_cost;
    } else {
        $runningQty -= $m->qty;
        $runningValue -= $m->total_cost;
    }
}

$correctMinyakPricePerLiter = $runningQty > 0 ? $runningValue / $runningQty : 0;
$correctMinyakPricePerML = $correctMinyakPricePerLiter / 1000;

echo "Calculated weighted average: Rp " . number_format($correctMinyakPricePerLiter, 2) . " per Liter\n";
echo "Converted to mL: Rp " . number_format($correctMinyakPricePerML, 2) . " per mL\n";

// Update Minyak Goreng BOM
$minyakBom = DB::table('bom_job_bahan_pendukung')
    ->where('bahan_pendukung_id', 14)
    ->first();

if ($minyakBom) {
    $correctSubtotal = $minyakBom->jumlah * $correctMinyakPricePerML;
    
    DB::table('bom_job_bahan_pendukung')
        ->where('id', $minyakBom->id)
        ->update([
            'harga_satuan' => $correctMinyakPricePerML,
            'subtotal' => $correctSubtotal
        ]);
    
    echo "Updated BOM: {$minyakBom->jumlah} mL @ Rp " . number_format($correctMinyakPricePerML, 2) . " = Rp " . number_format($correctSubtotal, 2) . "\n";
}

// 2. Calculate correct Kemasan weighted average
echo "\n2. FIXING KEMASAN...\n";
$kemasanMovements = DB::table('stock_movements')
    ->where('item_type', 'support')
    ->where('item_id', 22)
    ->orderBy('tanggal')
    ->get();

$runningQty = 0;
$runningValue = 0;

foreach ($kemasanMovements as $m) {
    if ($m->direction == 'in') {
        $runningQty += $m->qty;
        $runningValue += $m->total_cost;
    } else {
        $runningQty -= $m->qty;
        $runningValue -= $m->total_cost;
    }
}

$correctKemasanPrice = $runningQty > 0 ? $runningValue / $runningQty : 0;

echo "Calculated weighted average: Rp " . number_format($correctKemasanPrice, 2) . " per Pieces\n";

// Update Kemasan BOM
$kemasanBom = DB::table('bom_job_bahan_pendukung')
    ->where('bahan_pendukung_id', 22)
    ->first();

if ($kemasanBom) {
    $correctSubtotal = $kemasanBom->jumlah * $correctKemasanPrice;
    
    DB::table('bom_job_bahan_pendukung')
        ->where('id', $kemasanBom->id)
        ->update([
            'harga_satuan' => $correctKemasanPrice,
            'subtotal' => $correctSubtotal
        ]);
    
    echo "Updated BOM: {$kemasanBom->jumlah} Pieces @ Rp " . number_format($correctKemasanPrice, 2) . " = Rp " . number_format($correctSubtotal, 2) . "\n";
}

// 3. Update master data to match calculations
echo "\n3. UPDATING MASTER DATA...\n";
DB::table('bahan_pendukungs')
    ->where('id', 14)
    ->update([
        'harga_satuan' => $correctMinyakPricePerLiter
    ]);

DB::table('bahan_pendukungs')
    ->where('id', 22)
    ->update([
        'harga_satuan' => $correctKemasanPrice
    ]);

echo "Updated Minyak Goreng master data: Rp " . number_format($correctMinyakPricePerLiter, 2) . " per Liter\n";
echo "Updated Kemasan master data: Rp " . number_format($correctKemasanPrice, 2) . " per Pieces\n";

// 4. Recalculate BOM totals
echo "\n4. RECALCULATING BOM TOTALS...\n";
$bomJobCosting = DB::table('bom_job_costings')->where('produk_id', 2)->first();

$totalBahanPendukung = DB::table('bom_job_bahan_pendukung')
    ->where('bom_job_costing_id', $bomJobCosting->id)
    ->sum('subtotal');

$newTotalHPP = $bomJobCosting->total_bbb + $totalBahanPendukung + $bomJobCosting->total_btkl + $bomJobCosting->total_bop;

// Update BOM Job Costing
DB::table('bom_job_costings')
    ->where('id', $bomJobCosting->id)
    ->update([
        'total_bahan_pendukung' => $totalBahanPendukung,
        'total_hpp' => $newTotalHPP
    ]);

// Update product price
DB::table('produks')
    ->where('id', 2)
    ->update([
        'harga_pokok' => $newTotalHPP
    ]);

echo "Previous HPP: Rp " . number_format($bomJobCosting->total_hpp, 2) . "\n";
echo "New HPP: Rp " . number_format($newTotalHPP, 2) . "\n";
echo "Change: Rp " . number_format($newTotalHPP - $bomJobCosting->total_hpp, 2) . "\n";

echo "\n✅ MINYAK GORENG AND KEMASAN PRICES NOW MATCH STOCK CALCULATIONS!\n";