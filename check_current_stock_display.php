<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Current Stock Display vs Production Data ===" . PHP_EOL;

// Production transaction data (what user sees in production page)
echo "Production Transaction Data (Page /transaksi/produksi/2):" . PHP_EOL;
echo "Ayam Potong:" . PHP_EOL;
echo "- Resep (Total): 160 Potong" . PHP_EOL;
echo "- Konversi ke Satuan Bahan: 40 Kilogram" . PHP_EOL;
echo "- Harga Satuan: Rp 8.000 / Potong" . PHP_EOL;
echo "- Subtotal: Rp 1.280.000" . PHP_EOL;

// Check actual data from database
echo PHP_EOL . "=== Database Data ===" . PHP_EOL;

// Produksi detail
$produksiDetail = DB::table('produksi_details')
    ->where('produksi_id', 2)
    ->where('bahan_baku_id', 1)
    ->first();

echo "Produksi Detail:" . PHP_EOL;
echo "Qty Resep: " . $produksiDetail->qty_resep . PHP_EOL;
echo "Satuan Resep: " . $produksiDetail->satuan_resep . PHP_EOL;
echo "Qty Konversi: " . $produksiDetail->qty_konversi . PHP_EOL;
echo "Satuan: " . $produksiDetail->satuan . PHP_EOL;
echo "Harga Satuan: " . $produksiDetail->harga_satuan . PHP_EOL;
echo "Subtotal: " . $produksiDetail->subtotal . PHP_EOL;

// Stock movement
$movement = DB::table('stock_movements')
    ->where('ref_type', 'production')
    ->where('ref_id', 2)
    ->where('item_id', 1)
    ->first();

echo PHP_EOL . "Stock Movement:" . PHP_EOL;
echo "Qty (stored): " . $movement->qty . PHP_EOL;
echo "Unit Cost: " . $movement->unit_cost . PHP_EOL;
echo "Total Cost: " . $movement->total_cost . PHP_EOL;
echo "Qty as Input: " . $movement->qty_as_input . PHP_EOL;
echo "Satuan as Input: " . $movement->satuan_as_input . PHP_EOL;

// Check what the stock report SHOULD show
echo PHP_EOL . "=== What Stock Report Should Show ===" . PHP_EOL;

echo "For Kilogram unit:" . PHP_EOL;
echo "- Production Qty: 40 Kilogram (from stored qty)" . PHP_EOL;
echo "- Production Price: Rp 8.000 / Potong ÷ 4 = Rp 2.000 / Kilogram" . PHP_EOL;
echo "- Production Total: Rp 1.280.000" . PHP_EOL;

echo PHP_EOL . "For Potong unit:" . PHP_EOL;
echo "- Production Qty: 160 Potong (from qty_as_input)" . PHP_EOL;
echo "- Production Price: Rp 8.000 / Potong" . PHP_EOL;
echo "- Production Total: Rp 1.280.000" . PHP_EOL;

echo PHP_EOL . "For Gram unit:" . PHP_EOL;
echo "- Production Qty: 40.000 Gram (40 kg × 1000)" . PHP_EOL;
echo "- Production Price: Rp 2.000 / Kilogram ÷ 1000 = Rp 2 / Gram" . PHP_EOL;
echo "- Production Total: Rp 1.280.000" . PHP_EOL;

echo PHP_EOL . "For Ons unit:" . PHP_EOL;
echo "- Production Qty: 400 Ons (40 kg × 10)" . PHP_EOL;
echo "- Production Price: Rp 2.000 / Kilogram ÷ 10 = Rp 200 / Ons" . PHP_EOL;
echo "- Production Total: Rp 1.280.000" . PHP_EOL;
