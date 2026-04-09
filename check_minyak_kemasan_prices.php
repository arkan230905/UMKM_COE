<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING MINYAK GORENG AND KEMASAN PRICES ===\n";

// Check Minyak Goreng
echo "\n1. MINYAK GORENG:\n";
$minyakGoreng = DB::table('bahan_pendukungs')->where('id', 14)->first();
echo "Stock Report:\n";
echo "- Stock: {$minyakGoreng->stok} Liter\n";
echo "- Price: Rp " . number_format($minyakGoreng->harga_satuan, 2) . " per Liter\n";

$minyakBom = DB::table('bom_job_bahan_pendukung')
    ->where('bahan_pendukung_id', 14)
    ->first();

if ($minyakBom) {
    echo "BOM:\n";
    echo "- Quantity: {$minyakBom->jumlah} {$minyakBom->satuan}\n";
    echo "- Price: Rp " . number_format($minyakBom->harga_satuan, 2) . " per {$minyakBom->satuan}\n";
    echo "- Subtotal: Rp " . number_format($minyakBom->subtotal, 2) . "\n";
    
    // Calculate what the price should be
    if ($minyakBom->satuan == 'Mililiter') {
        $correctPrice = $minyakGoreng->harga_satuan / 1000; // Convert Liter to Mililiter
        echo "Expected BOM price: Rp " . number_format($correctPrice, 2) . " per Mililiter\n";
        echo "Difference: Rp " . number_format($minyakBom->harga_satuan - $correctPrice, 2) . "\n";
    }
}

// Check Kemasan
echo "\n2. KEMASAN:\n";
$kemasan = DB::table('bahan_pendukungs')->where('id', 22)->first();
echo "Stock Report:\n";
echo "- Stock: {$kemasan->stok} Pieces\n";
echo "- Price: Rp " . number_format($kemasan->harga_satuan, 2) . " per Pieces\n";

$kemasanBom = DB::table('bom_job_bahan_pendukung')
    ->where('bahan_pendukung_id', 22)
    ->first();

if ($kemasanBom) {
    echo "BOM:\n";
    echo "- Quantity: {$kemasanBom->jumlah} {$kemasanBom->satuan}\n";
    echo "- Price: Rp " . number_format($kemasanBom->harga_satuan, 2) . " per {$kemasanBom->satuan}\n";
    echo "- Subtotal: Rp " . number_format($kemasanBom->subtotal, 2) . "\n";
    
    // Expected price should match stock report
    echo "Expected BOM price: Rp " . number_format($kemasan->harga_satuan, 2) . " per Pieces\n";
    echo "Difference: Rp " . number_format($kemasanBom->harga_satuan - $kemasan->harga_satuan, 2) . "\n";
}

// Check stock movements to see actual prices
echo "\n3. STOCK MOVEMENTS VERIFICATION:\n";

echo "\nMinyak Goreng movements:\n";
$minyakMovements = DB::table('stock_movements')
    ->where('item_type', 'support')
    ->where('item_id', 14)
    ->orderBy('tanggal')
    ->get();

foreach ($minyakMovements as $m) {
    echo "  {$m->tanggal} | {$m->direction} | {$m->qty} {$m->satuan} | Rp " . number_format($m->unit_cost, 2) . " | {$m->ref_type}\n";
}

echo "\nKemasan movements:\n";
$kemasanMovements = DB::table('stock_movements')
    ->where('item_type', 'support')
    ->where('item_id', 22)
    ->orderBy('tanggal')
    ->get();

foreach ($kemasanMovements as $m) {
    echo "  {$m->tanggal} | {$m->direction} | {$m->qty} {$m->satuan} | Rp " . number_format($m->unit_cost, 2) . " | {$m->ref_type}\n";
}

echo "\n=== SUMMARY ===\n";
echo "Issues to fix:\n";

if ($minyakBom && $minyakBom->satuan == 'Mililiter') {
    $expectedMinyakPrice = $minyakGoreng->harga_satuan / 1000;
    if (abs($minyakBom->harga_satuan - $expectedMinyakPrice) > 1) {
        echo "❌ Minyak Goreng BOM price needs update\n";
        echo "   Current: Rp " . number_format($minyakBom->harga_satuan, 2) . " per Mililiter\n";
        echo "   Should be: Rp " . number_format($expectedMinyakPrice, 2) . " per Mililiter\n";
    }
}

if ($kemasanBom && abs($kemasanBom->harga_satuan - $kemasan->harga_satuan) > 1) {
    echo "❌ Kemasan BOM price needs update\n";
    echo "   Current: Rp " . number_format($kemasanBom->harga_satuan, 2) . " per Pieces\n";
    echo "   Should be: Rp " . number_format($kemasan->harga_satuan, 2) . " per Pieces\n";
}