<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debugging Pelunasan Create ===\n";

try {
    // Test the controller method directly
    $request = new Illuminate\Http\Request();
    $controller = new App\Http\Controllers\PelunasanUtangController();
    
    echo "Testing controller create method...\n";
    $response = $controller->create();
    
    echo "Response type: " . get_class($response) . "\n";
    
    if ($response instanceof Illuminate\View\View) {
        echo "View name: " . $response->getName() . "\n";
        echo "View data keys: " . implode(', ', array_keys($response->getData())) . "\n";
        
        $data = $response->getData();
        if (isset($data['pembayarans'])) {
            echo "Pembayarans count: " . $data['pembayarans']->count() . "\n";
        }
        if (isset($data['akunKas'])) {
            echo "AkunKas count: " . $data['akunKas']->count() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";