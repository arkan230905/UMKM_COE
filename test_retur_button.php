<?php

// Test script to verify retur button functionality
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PurchaseReturn;

echo "Testing Retur Button Functionality\n";
echo "==================================\n\n";

// Get all returs
$returs = PurchaseReturn::with(['items', 'pembelian'])->get();

if ($returs->isEmpty()) {
    echo "No retur records found. Please create a retur first.\n";
    exit;
}

foreach ($returs as $retur) {
    echo "Retur ID: {$retur->id}\n";
    echo "Return Number: {$retur->return_number}\n";
    echo "Current Status: {$retur->status}\n";
    echo "Jenis Retur: {$retur->jenis_retur}\n";
    echo "Next Status: " . ($retur->next_status ?? 'None') . "\n";
    
    $badge = $retur->status_badge;
    echo "Status Badge: {$badge['text']} ({$badge['class']})\n";
    
    // Check if button should appear
    if ($retur->status == 'acc_vendor') {
        echo "✅ 'Kirim Barang' button SHOULD appear\n";
        echo "Expected next status: dikirim\n";
    } else {
        echo "ℹ️  Current status doesn't show 'Kirim Barang' button\n";
    }
    
    echo "Items count: " . $retur->items->count() . "\n";
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

// Test status flow
echo "Status Flow Test:\n";
echo "================\n";

$testRetur = $returs->first();
if ($testRetur) {
    echo "Testing with Retur ID: {$testRetur->id}\n";
    
    // Test each status transition
    $statuses = ['pending', 'acc_vendor', 'diproses', 'dikirim', 'selesai'];
    
    foreach ($statuses as $status) {
        $testRetur->status = $status;
        $nextStatus = $testRetur->next_status;
        $badge = $testRetur->status_badge;
        
        echo "Status: {$status} -> Next: " . ($nextStatus ?? 'None') . " | Badge: {$badge['text']}\n";
    }
}