<?php

// Test script to simulate the updateStatus request
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\ReturController;
use App\Services\StockService;
use App\Services\JournalService;
use Illuminate\Http\Request;

echo "Testing UpdateStatus Method Directly\n";
echo "===================================\n\n";

try {
    // Create services
    $stockService = new StockService();
    $journalService = new JournalService();
    
    // Create controller
    $controller = new ReturController();
    
    // Create a mock request
    $request = new Request();
    
    // Test with retur ID 1
    $returId = 1;
    
    echo "Testing updateStatus for retur ID: {$returId}\n";
    
    // Get current retur data
    $retur = \App\Models\PurchaseReturn::find($returId);
    if (!$retur) {
        echo "❌ Retur not found\n";
        exit;
    }
    
    echo "Current status: {$retur->status}\n";
    echo "Jenis retur: {$retur->jenis_retur}\n";
    echo "Next status: " . ($retur->next_status ?? 'None') . "\n\n";
    
    if (!$retur->next_status) {
        echo "❌ No next status available\n";
        exit;
    }
    
    echo "Calling updateStatus method...\n";
    
    // Call the method directly
    $response = $controller->updateStatus($request, $returId, $stockService, $journalService);
    
    echo "✅ Method executed successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    
    // Check if status was updated
    $retur->refresh();
    echo "New status: {$retur->status}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}