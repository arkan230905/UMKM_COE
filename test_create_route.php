<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Testing create route and controller...\n\n";

// Test the controller create method directly
echo "=== TESTING CONTROLLER CREATE METHOD ===\n";
try {
    $controller = new \App\Http\Controllers\PenjualanController();
    
    // Call the create method
    $response = $controller->create();
    
    echo "Controller create method executed successfully\n";
    
    // Check if response is a view
    if (method_exists($response, 'getData')) {
        $viewData = $response->getData();
        
        echo "View data found:\n";
        if (isset($viewData['produks'])) {
            echo "  Products: " . count($viewData['produks']) . " items\n";
        }
        
        if (isset($viewData['kasbank'])) {
            echo "  Kas/Bank: " . count($viewData['kasbank']) . " items\n";
        }
        
        // Check if data is actually populated
        if (isset($viewData['produks']) && count($viewData['produks']) > 0) {
            $firstProduct = $viewData['produks']->first();
            echo "  First product: " . ($firstProduct->nama_produk ?? 'Unknown') . "\n";
        }
        
        if (isset($viewData['kasbank']) && count($viewData['kasbank']) > 0) {
            $firstKasbank = $viewData['kasbank']->first();
            echo "  First kas/bank: " . ($firstKasbank->nama_akun ?? 'Unknown') . "\n";
        }
    } else {
        echo "Response is not a view or doesn't have getData method\n";
    }
    
} catch (\Exception $e) {
    echo "Error calling controller create method: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test the route
echo "\n=== TESTING ROUTE ===\n";
try {
    $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('transaksi.penjualan.create');
    
    if ($route) {
        echo "Route found: transaksi.penjualan.create\n";
        echo "URI: " . $route->uri() . "\n";
        echo "Action: " . $route->getActionName() . "\n";
    } else {
        echo "Route transaksi.penjualan.create not found\n";
    }
} catch (\Exception $e) {
    echo "Error checking route: " . $e->getMessage() . "\n";
}

// Check if there are any recent modifications that might have broken the view
echo "\n=== CHECKING FOR RECENT MODIFICATIONS ===\n";
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
if (file_exists($bladeFile)) {
    $content = file_get_contents($bladeFile);
    
    // Check for any PHP syntax errors
    $lines = file($bladeFile);
    $phpLines = [];
    $inPhp = false;
    
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, '<?php') !== false) {
            $inPhp = true;
        }
        
        if ($inPhp) {
            $phpLines[] = "Line " . ($lineNum + 1) . ": " . trim($line);
        }
        
        if (strpos($line, '?>') !== false) {
            $inPhp = false;
        }
    }
    
    if (!empty($phpLines)) {
        echo "Found PHP code in blade file:\n";
        foreach (array_slice($phpLines, 0, 5) as $phpLine) {
            echo "  " . $phpLine . "\n";
        }
    }
    
    // Check for any obvious syntax issues
    if (strpos($content, '@foreach($produks as $p)') !== false) {
        echo "Found @foreach loop for products\n";
    }
    
    if (strpos($content, '@endforeach') !== false) {
        echo "Found @endforeach\n";
    }
    
    // Check for any missing closing tags
    $foreachCount = substr_count($content, '@foreach');
    $endforeachCount = substr_count($content, '@endforeach');
    
    if ($foreachCount !== $endforeachCount) {
        echo "Mismatched foreach directives: {$foreachCount} @foreach vs {$endforeachCount} @endforeach\n";
    }
} else {
    echo "Blade file not found\n";
}

echo "\nDone.\n";
