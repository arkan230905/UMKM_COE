<?php

// Test retur total calculation with PPN
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseReturn;

echo "Testing Retur Total Calculation with PPN\n";
echo "=======================================\n\n";

$retur = PurchaseReturn::with('items')->find(1);

if (!$retur) {
    echo "Retur not found\n";
    exit;
}

echo "Retur ID: {$retur->id}\n";
echo "Return Number: {$retur->return_number}\n";
echo "Items count: " . $retur->items->count() . "\n\n";

// Calculate totals step by step
$subtotal = $retur->total_retur; // Sum of item subtotals
$ppnRate = 0.11; // 11%
$ppnAmount = $subtotal * $ppnRate;
$totalWithPpn = $subtotal + $ppnAmount;

echo "CALCULATION BREAKDOWN:\n";
echo "=====================\n";
echo "Subtotal (from items): Rp " . number_format($subtotal, 0, ',', '.') . "\n";
echo "PPN Rate: {$ppnRate} (11%)\n";
echo "PPN Amount: Rp " . number_format($ppnAmount, 0, ',', '.') . "\n";
echo "Total with PPN: Rp " . number_format($totalWithPpn, 0, ',', '.') . "\n\n";

// Test model accessors
echo "MODEL ACCESSORS:\n";
echo "================\n";
echo "total_retur: Rp " . number_format($retur->total_retur, 0, ',', '.') . "\n";
echo "ppn_amount: Rp " . number_format($retur->ppn_amount, 0, ',', '.') . "\n";
echo "total_with_ppn: Rp " . number_format($retur->total_with_ppn, 0, ',', '.') . "\n";
echo "total_with_ppn_formatted: {$retur->total_with_ppn_formatted}\n\n";

// Verify consistency
echo "VERIFICATION:\n";
echo "=============\n";
if (abs($ppnAmount - $retur->ppn_amount) < 0.01) {
    echo "✅ PPN calculation matches\n";
} else {
    echo "❌ PPN calculation mismatch\n";
}

if (abs($totalWithPpn - $retur->total_with_ppn) < 0.01) {
    echo "✅ Total with PPN calculation matches\n";
} else {
    echo "❌ Total with PPN calculation mismatch\n";
}

echo "\n";
echo "EXPECTED IN LAPORAN:\n";
echo "===================\n";
echo "Subtotal: Rp " . number_format($subtotal, 0, ',', '.') . "\n";
echo "PPN 11%: Rp " . number_format($ppnAmount, 0, ',', '.') . "\n";
echo "TOTAL: Rp " . number_format($totalWithPpn, 0, ',', '.') . " ← This should match detail page\n";