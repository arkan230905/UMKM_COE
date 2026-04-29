<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Penjualan Journal Balance Fix...\n\n";

// Create a mock penjualan object similar to the one causing error
$mockPenjualan = (object) [
    'id' => 999,
    'payment_method' => 'cash',
    'total' => 500000,
    'biaya_ongkir' => 0,
    'biaya_ppn' => 55000,
    'grand_total' => 555000,
    'user_id' => 1,
    'coa_id' => null,
    'tanggal' => '2026-04-30',
    'details' => [] // Empty details like in the error case
];

echo "Mock Penjualan Data:\n";
echo "====================\n";
echo "Total: " . number_format($mockPenjualan->total, 0, ',', '.') . "\n";
echo "Biaya Ongkir: " . number_format($mockPenjualan->biaya_ongkir, 0, ',', '.') . "\n";
echo "Biaya PPN: " . number_format($mockPenjualan->biaya_ppn, 0, ',', '.') . "\n";
echo "Grand Total: " . number_format($mockPenjualan->grand_total, 0, ',', '.') . "\n";
echo "Details: " . count($mockPenjualan->details) . " (empty)\n\n";

// Test the old calculation (causing the error)
echo "OLD Calculation (causing error):\n";
echo "=================================\n";

$oldSubtotalProduk = 0;
foreach ($mockPenjualan->details as $d) {
    $oldSubtotalProduk += (float)($d->subtotal ?? ((float)$d->harga_satuan * (float)$d->jumlah));
}

if ($oldSubtotalProduk <= 0) {
    $oldSubtotalProduk = (float)($mockPenjualan->subtotal_produk ?? $mockPenjualan->total ?? 0)
                        - (float)($mockPenjualan->biaya_ongkir ?? 0)
                        - (float)($mockPenjualan->total_ppn ?? $mockPenjualan->biaya_ppn ?? 0);
}

echo "Subtotal Produk (OLD): " . number_format($oldSubtotalProduk, 0, ',', '.') . "\n";
echo "Biaya PPN: " . number_format($mockPenjualan->biaya_ppn, 0, ',', '.') . "\n";
echo "Total Credit (OLD): " . number_format($oldSubtotalProduk + $mockPenjualan->biaya_ppn, 0, ',', '.') . "\n";
echo "Total Debit: " . number_format($mockPenjualan->grand_total, 0, ',', '.') . "\n";
echo "Balance (OLD): " . number_format($mockPenjualan->grand_total - ($oldSubtotalProduk + $mockPenjualan->biaya_ppn), 0, ',', '.') . "\n";
echo "Status: " . ($mockPenjualan->grand_total == ($oldSubtotalProduk + $mockPenjualan->biaya_ppn) ? "BALANCED" : "NOT BALANCED") . "\n\n";

// Test the new calculation (fixed)
echo "NEW Calculation (fixed):\n";
echo "========================\n";

$newSubtotalProduk = 0;
foreach ($mockPenjualan->details as $d) {
    $newSubtotalProduk += (float)($d->subtotal ?? ((float)$d->harga_satuan * (float)$d->jumlah));
}

if ($newSubtotalProduk <= 0) {
    $newSubtotalProduk = (float)($mockPenjualan->subtotal_produk ?? $mockPenjualan->total ?? 0)
                        - (float)($mockPenjualan->biaya_ongkir ?? 0);
    // Don't subtract PPN - total is already before PPN, grand_total includes PPN
}

echo "Subtotal Produk (NEW): " . number_format($newSubtotalProduk, 0, ',', '.') . "\n";
echo "Biaya PPN: " . number_format($mockPenjualan->biaya_ppn, 0, ',', '.') . "\n";
echo "Total Credit (NEW): " . number_format($newSubtotalProduk + $mockPenjualan->biaya_ppn, 0, ',', '.') . "\n";
echo "Total Debit: " . number_format($mockPenjualan->grand_total, 0, ',', '.') . "\n";
echo "Balance (NEW): " . number_format($mockPenjualan->grand_total - ($newSubtotalProduk + $mockPenjualan->biaya_ppn), 0, ',', '.') . "\n";
echo "Status: " . ($mockPenjualan->grand_total == ($newSubtotalProduk + $mockPenjualan->biaya_ppn) ? "BALANCED" : "NOT BALANCED") . "\n\n";

// Expected journal entries
echo "Expected Journal Entries (NEW):\n";
echo "===============================\n";
echo "DEBIT:\n";
echo "  Kas (112): " . number_format($mockPenjualan->grand_total, 0, ',', '.') . "\n";
echo "CREDIT:\n";
echo "  Penjualan (41): " . number_format($newSubtotalProduk, 0, ',', '.') . "\n";
echo "  PPN Keluaran (212): " . number_format($mockPenjualan->biaya_ppn, 0, ',', '.') . "\n";
echo "TOTAL DEBIT: " . number_format($mockPenjualan->grand_total, 0, ',', '.') . "\n";
echo "TOTAL CREDIT: " . number_format($newSubtotalProduk + $mockPenjualan->biaya_ppn, 0, ',', '.') . "\n";
echo "FINAL STATUS: " . ($mockPenjualan->grand_total == ($newSubtotalProduk + $mockPenjualan->biaya_ppn) ? "BALANCED - NO ERROR" : "NOT BALANCED - STILL ERROR") . "\n\n";

echo "Penjualan balance fix test completed!\n";
