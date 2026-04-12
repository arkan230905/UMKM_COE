<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST API CONTROLLER ===\n";

// Test controller method
$controller = new \App\Http\Controllers\PenjualanController();

try {
    // Simulate request
    $barcode = '8992000000001';
    echo "Testing findByBarcode with barcode: $barcode\n";
    
    // Call the method directly
    $response = $controller->findByBarcode($barcode);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content:\n";
    echo $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
