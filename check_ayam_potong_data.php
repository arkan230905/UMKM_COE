<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Ayam Potong Production Data ===\n\n";

// Check ProduksiDetail table
$produksiDetails = DB::table('produksi_details')
    ->join('bahan_bakus', 'produksi_details.bahan_baku_id', '=', 'bahan_bakus.id')
    ->where('bahan_bakus.nama_bahan', 'Ayam Potong')
    ->select('produksi_details.*', 'bahan_bakus.nama_bahan')
    ->get();

echo "ProduksiDetail entries for Ayam Potong:\n";
foreach ($produksiDetails as $detail) {
    echo "- Produksi ID: {$detail->produksi_id}\n";
    echo "  Qty Resep: {$detail->qty_resep} {$detail->satuan_resep}\n";
    echo "  Qty Konversi: {$detail->qty_konversi}\n";
    echo "  Subtotal: {$detail->subtotal}\n\n";
}

// Check StockMovement table
$stockMovements = DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 2) // Ayam Potong ID
    ->where('ref_type', 'production')
    ->get();

echo "StockMovement entries for Ayam Potong (production):\n";
foreach ($stockMovements as $movement) {
    echo "- Date: {$movement->tanggal}\n";
    echo "  Direction: {$movement->direction}\n";
    echo "  Qty: {$movement->qty}\n";
    echo "  Qty as Input: " . ($movement->qty_as_input ?? 'null') . "\n";
    echo "  Satuan as Input: " . ($movement->satuan_as_input ?? 'null') . "\n";
    echo "  Ref ID: {$movement->ref_id}\n\n";
}

// Check actual production records
$produksis = DB::table('produksis')
    ->whereIn('id', $stockMovements->pluck('ref_id'))
    ->get();

echo "Production records:\n";
foreach ($produksis as $produksi) {
    echo "- Production ID: {$produksi->id}\n";
    echo "  Kode: {$produksi->kode_produksi}\n";
    echo "  Qty Produksi: {$produksi->qty_produksi}\n";
    echo "  Status: {$produksi->status}\n";
    echo "  Tanggal: {$produksi->tanggal}\n\n";
}

// Check BOM data for comparison
echo "BOM Data for comparison:\n";
$bomData = DB::table('bom_job_bbbs')
    ->join('bom_job_costings', 'bom_job_bbbs.bom_job_costing_id', '=', 'bom_job_costings.id')
    ->join('bahan_bakus', 'bom_job_bbbs.bahan_baku_id', '=', 'bahan_bakus.id')
    ->where('bahan_bakus.nama_bahan', 'Ayam Potong')
    ->select('bom_job_bbbs.*', 'bahan_bakus.nama_bahan', 'bom_job_costings.produk_id')
    ->get();

foreach ($bomData as $bom) {
    echo "- BOM ID: {$bom->id}\n";
    echo "  Produk ID: {$bom->produk_id}\n";
    echo "  Jumlah per unit: {$bom->jumlah}\n";
    echo "  Satuan: {$bom->satuan}\n";
    echo "  Subtotal: {$bom->subtotal}\n\n";
}