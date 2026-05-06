<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING CONTROLLER ENDPOINT DIRECTLY ===\n\n";

// Create a mock request
use Illuminate\Http\Request;

try {
    // Test the controller method directly
    $controller = new \App\Http\Controllers\MasterData\BopController();
    
    echo "1. Testing showProses method directly...\n";
    
    // Call the method with ID 2
    $response = $controller->showProses(2);
    
    echo "2. Controller executed successfully\n";
    echo "   Response type: " . get_class($response) . "\n";
    
    // Check if it's a view response
    if (method_exists($response, 'getData')) {
        echo "   View data available\n";
        
        // Get the view data
        $viewData = $response->getData();
        echo "   View variables: " . implode(', ', array_keys($viewData)) . "\n";
        
        if (isset($viewData['totalBopPerProduk'])) {
            echo "   totalBopPerProduk: " . $viewData['totalBopPerProduk'] . "\n";
        }
        
        if (isset($viewData['komponenBop'])) {
            echo "   komponenBop count: " . count($viewData['komponenBop']) . "\n";
            foreach ($viewData['komponenBop'] as $i => $komponen) {
                echo "     Component " . ($i+1) . ": " . $komponen['component'] . " -> " . $komponen['rate_per_produk'] . "\n";
            }
        }
    } else {
        echo "   No view data available\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== ENDPOINT TEST COMPLETE ===\n";
