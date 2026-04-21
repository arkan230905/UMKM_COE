<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Purchase Data ===" . PHP_EOL;

echo "Current Data:" . PHP_EOL;
echo "Purchase: 50 Ekor" . PHP_EOL;
echo "Stock Movement: 50 kg" . PHP_EOL;
echo "Manual Conversion: 150 Potong (but shows 120 in data)" . PHP_EOL;

echo PHP_EOL . "User Expected:" . PHP_EOL;
echo "Purchase: 40 Ekor" . PHP_EOL;
echo "Stock Movement: 40 kg" . PHP_EOL;
echo "Manual Conversion: 120 Potong" . PHP_EOL;

echo PHP_EOL . "=== Fixing Data ===" . PHP_EOL;

// 1. Update purchase detail from 50 Ekor to 40 Ekor
$updatedPurchase = DB::table('pembelian_details')
    ->where('pembelian_id', 1)
    ->where('bahan_baku_id', 1)
    ->update([
        'jumlah' => 40,
        'subtotal' => 40 * 32000 // 40 × 32,000 = 1,280,000
    ]);

echo "Purchase detail updated: " . ($updatedPurchase ? "SUCCESS" : "FAILED") . PHP_EOL;

// 2. Update stock movement from 50 kg to 40 kg
$updatedMovement = DB::table('stock_movements')
    ->where('ref_type', 'purchase')
    ->where('ref_id', 1)
    ->where('item_id', 1)
    ->update([
        'qty' => 40.0000,
        'total_cost' => 1280000.00, // 40 × 32,000
        'manual_conversion_data' => json_encode([
            'sub_satuan_id' => 6,
            'sub_satuan_nama' => 'Potong',
            'manual_conversion_factor' => 3.0000,
            'jumlah_sub_satuan' => 120.00
        ])
    ]);

echo "Stock movement updated: " . ($updatedMovement ? "SUCCESS" : "FAILED") . PHP_EOL;

// 3. Update purchase total
$updatedPurchaseTotal = DB::table('pembelians')
    ->where('id', 1)
    ->update([
        'total' => 1280000.00
    ]);

echo "Purchase total updated: " . ($updatedPurchaseTotal ? "SUCCESS" : "FAILED") . PHP_EOL;

echo PHP_EOL . "=== Verification ===" . PHP_EOL;

// Verify the updates
$pembelianDetail = DB::table('pembelian_details')
    ->where('pembelian_id', 1)
    ->where('bahan_baku_id', 1)
    ->first();

$movement = DB::table('stock_movements')
    ->where('ref_type', 'purchase')
    ->where('ref_id', 1)
    ->where('item_id', 1)
    ->first();

echo "Updated Purchase Detail:" . PHP_EOL;
echo "Jumlah: " . $pembelianDetail->jumlah . " Ekor" . PHP_EOL;
echo "Subtotal: " . $pembelianDetail->subtotal . PHP_EOL;

echo PHP_EOL . "Updated Stock Movement:" . PHP_EOL;
echo "Qty: " . $movement->qty . " kg" . PHP_EOL;
echo "Total Cost: " . $movement->total_cost . PHP_EOL;
echo "Manual Conversion: " . $movement->manual_conversion_data . PHP_EOL;

echo PHP_EOL . "Expected Results in Stock Report:" . PHP_EOL;
echo "Kilogram: 40 kg (was 50 kg)" . PHP_EOL;
echo "Potong: 120 Potong (was 150 Potong)" . PHP_EOL;
echo "Total: Rp 1,280,000 (was Rp 1,600,000)" . PHP_EOL;
