<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG BOP CONTROLLER INDEX ===\n";

try {
    $controller = new \App\Http\Controllers\MasterData\BopController();
    
    // Call index method
    $result = $controller->index();
    
    echo "Controller index() executed successfully\n";
    echo "Result type: " . gettype($result) . "\n";
    
    if (is_object($result) && method_exists($result, 'getData')) {
        $data = $result->getData();
        echo "Data type: " . get_class($data) . "\n";
        
        if (property_exists($data, 'prosesProduksis')) {
            echo "prosesProduksis count: " . $data->prosesProduksis->count() . "\n";
        }
        
        if (property_exists($data, 'bopLainnya')) {
            echo "bopLainnya count: " . $data->bopLainnya->count() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
