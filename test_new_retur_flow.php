<?php

// Test the new simplified retur flow
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseReturn;

echo "Testing New Retur Flow\n";
echo "=====================\n\n";

$retur = PurchaseReturn::find(1);

if (!$retur) {
    echo "Retur not found\n";
    exit;
}

echo "Retur ID: {$retur->id}\n";
echo "Return Number: {$retur->return_number}\n";
echo "Jenis Retur: {$retur->jenis_retur}\n\n";

// Test each status and expected buttons
$statuses = ['pending', 'disetujui', 'dikirim', 'selesai'];

foreach ($statuses as $status) {
    $retur->status = $status;
    $nextStatus = $retur->next_status;
    $badge = $retur->status_badge;
    
    echo "Status: {$status}\n";
    echo "Badge: {$badge['text']} ({$badge['class']})\n";
    echo "Next Status: " . ($nextStatus ?? 'None') . "\n";
    
    // Determine expected button
    $expectedButton = 'None';
    if ($status === 'pending') {
        $expectedButton = 'ACC Vendor';
    } elseif ($status === 'disetujui') {
        $expectedButton = 'Kirim Barang';
    } elseif ($status === 'dikirim') {
        if ($retur->jenis_retur === 'tukar_barang') {
            $expectedButton = 'Terima Barang';
        } elseif ($retur->jenis_retur === 'refund') {
            $expectedButton = 'Terima Uang';
        }
    }
    
    echo "Expected Button: {$expectedButton}\n";
    echo str_repeat('-', 40) . "\n\n";
}

// Reset to pending for actual testing
$retur->status = 'pending';
$retur->save();

echo "✅ Retur reset to 'pending' status for testing\n";
echo "🎯 Ready to test the new flow in the web interface!\n\n";

echo "Expected Flow:\n";
echo "1. pending → Click 'ACC Vendor' → disetujui\n";
echo "2. disetujui → Click 'Kirim Barang' → dikirim (stock reduced)\n";
if ($retur->jenis_retur === 'tukar_barang') {
    echo "3. dikirim → Click 'Terima Barang' → selesai (stock added)\n";
} else {
    echo "3. dikirim → Click 'Terima Uang' → selesai (no stock change)\n";
}