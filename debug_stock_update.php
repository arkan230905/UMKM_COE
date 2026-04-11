<?php

/**
 * Debug Stock Update Script
 * 
 * This script tests the stock update functionality to identify why
 * the master table stock is not being updated.
 */

require_once 'vendor/autoload.php';

use App\Models\BahanBaku;
use App\Models\BahanPendukung;

echo "=== STOCK UPDATE DEBUG SCRIPT ===\n\n";

// Test 1: Check if BahanBaku updateStok method works
echo "1. TESTING BAHAN BAKU STOCK UPDATE\n";
echo "-----------------------------------\n";

$bahanBaku = BahanBaku::first();
if ($bahanBaku) {
    echo "Testing with: {$bahanBaku->nama_bahan} (ID: {$bahanBaku->id})\n";
    echo "Current stock: {$bahanBaku->stok}\n";
    
    // Test adding 5 units
    $testQty = 5.0;
    echo "Adding {$testQty} units...\n";
    
    $result = $bahanBaku->updateStok($testQty, 'in', 'Debug test');
    
    $bahanBaku->refresh();
    echo "Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    echo "New stock: {$bahanBaku->stok}\n\n";
    
    // Revert the change
    echo "Reverting change...\n";
    $bahanBaku->updateStok($testQty, 'out', 'Debug test revert');
    $bahanBaku->refresh();
    echo "Final stock: {$bahanBaku->stok}\n\n";
} else {
    echo "No BahanBaku records found!\n\n";
}

// Test 2: Check if BahanPendukung updateStok method works
echo "2. TESTING BAHAN PENDUKUNG STOCK UPDATE\n";
echo "---------------------------------------\n";

$bahanPendukung = BahanPendukung::first();
if ($bahanPendukung) {
    echo "Testing with: {$bahanPendukung->nama_bahan} (ID: {$bahanPendukung->id})\n";
    echo "Current stock: {$bahanPendukung->stok}\n";
    
    // Test adding 3 units
    $testQty = 3.0;
    echo "Adding {$testQty} units...\n";
    
    $result = $bahanPendukung->updateStok($testQty, 'in', 'Debug test');
    
    $bahanPendukung->refresh();
    echo "Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    echo "New stock: {$bahanPendukung->stok}\n\n";
    
    // Revert the change
    echo "Reverting change...\n";
    $bahanPendukung->updateStok($testQty, 'out', 'Debug test revert');
    $bahanPendukung->refresh();
    echo "Final stock: {$bahanPendukung->stok}\n\n";
} else {
    echo "No BahanPendukung records found!\n\n";
}

// Test 3: Check recent purchase details
echo "3. CHECKING RECENT PURCHASE DETAILS\n";
echo "-----------------------------------\n";

$recentDetails = \App\Models\PembelianDetail::with(['bahanBaku', 'bahanPendukung'])
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentDetails as $detail) {
    echo "Detail ID: {$detail->id}\n";
    echo "Pembelian ID: {$detail->pembelian_id}\n";
    echo "Jumlah: {$detail->jumlah}\n";
    echo "Faktor Konversi: {$detail->faktor_konversi}\n";
    echo "Jumlah Satuan Utama: " . ($detail->jumlah_satuan_utama ?? 'NULL') . "\n";
    
    if ($detail->bahanBaku) {
        echo "Bahan Baku: {$detail->bahanBaku->nama_bahan}\n";
        echo "Current Stock: {$detail->bahanBaku->stok}\n";
    }
    
    if ($detail->bahanPendukung) {
        echo "Bahan Pendukung: {$detail->bahanPendukung->nama_bahan}\n";
        echo "Current Stock: {$detail->bahanPendukung->stok}\n";
    }
    
    echo "---\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
echo "If stock updates work in this test but not in purchases,\n";
echo "the issue is likely in the purchase controller logic.\n";
echo "Check the Laravel logs for detailed debugging information.\n";