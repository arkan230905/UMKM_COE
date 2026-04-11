<?php

// Reset retur status for testing
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseReturn;

echo "Resetting Retur Status for Testing\n";
echo "==================================\n\n";

$retur = PurchaseReturn::find(1);

if (!$retur) {
    echo "Retur not found\n";
    exit;
}

echo "Current status: {$retur->status}\n";

// Reset to acc_vendor for testing
$retur->status = 'pending';
$retur->save();

echo "Reset to: {$retur->status}\n";
echo "Next status should be: {$retur->next_status}\n";

echo "\nRetur is ready for testing the 'Kirim Barang' button!\n";