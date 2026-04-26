<?php
/**
 * Test BOP route accessibility
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING BOP ROUTE ===\n\n";

// Test 1: Check if route exists
try {
    $route = Route::getRoutes()->getByName('master-data.bop.index');
    if ($route) {
        echo "✓ Route 'master-data.bop.index' exists\n";
        echo "  URI: " . $route->uri() . "\n";
        echo "  Action: " . $route->getActionName() . "\n";
        echo "  Middleware: " . implode(', ', $route->middleware()) . "\n";
    } else {
        echo "✗ Route 'master-data.bop.index' NOT found\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking route: " . $e->getMessage() . "\n";
}

// Test 2: Check if controller exists
echo "\n";
if (class_exists('App\Http\Controllers\MasterData\BopController')) {
    echo "✓ BopController class exists\n";
    
    if (method_exists('App\Http\Controllers\MasterData\BopController', 'index')) {
        echo "✓ BopController::index() method exists\n";
    } else {
        echo "✗ BopController::index() method NOT found\n";
    }
} else {
    echo "✗ BopController class NOT found\n";
}

// Test 3: Check if BebanOperasional model can be loaded
echo "\n";
try {
    if (class_exists('App\Models\BebanOperasional')) {
        echo "✓ BebanOperasional model exists\n";
        
        // Try to instantiate
        $model = new App\Models\BebanOperasional();
        echo "✓ BebanOperasional can be instantiated\n";
    } else {
        echo "✗ BebanOperasional model NOT found\n";
    }
} catch (Exception $e) {
    echo "✗ Error with BebanOperasional model: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
