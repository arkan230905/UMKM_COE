<?php

// Test ACC Vendor action
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\ReturController;
use App\Models\PurchaseReturn;

echo "Testing ACC Vendor Action\n";
echo "========================\n\n";

$retur = PurchaseReturn::find(1);
echo "Current status: {$retur->status}\n";

if ($retur->status !== 'pending') {
    echo "Resetting to pending status...\n";
    $retur->status = 'pending';
    $retur->save();
}

try {
    $controller = new ReturController();
    $response = $controller->acc(1);
    
    echo "✅ ACC method executed successfully\n";
    
    // Check new status
    $retur->refresh();
    echo "New status: {$retur->status}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}