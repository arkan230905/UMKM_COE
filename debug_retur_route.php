<?php

// Debug script to test retur route directly
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

echo "Checking Retur Routes\n";
echo "====================\n\n";

// Get all routes
$routes = Route::getRoutes();

echo "Looking for retur-pembelian routes:\n";
foreach ($routes as $route) {
    $uri = $route->uri();
    $name = $route->getName();
    $methods = implode('|', $route->methods());
    
    if (str_contains($uri, 'retur-pembelian') || str_contains($name ?? '', 'retur-pembelian')) {
        echo "URI: {$uri}\n";
        echo "Name: " . ($name ?? 'No name') . "\n";
        echo "Methods: {$methods}\n";
        echo "Action: " . $route->getActionName() . "\n";
        echo str_repeat('-', 50) . "\n";
    }
}

// Test specific route
echo "\nTesting update-status route:\n";
try {
    $url = route('transaksi.retur-pembelian.update-status', ['id' => 1]);
    echo "✅ Route exists: {$url}\n";
} catch (Exception $e) {
    echo "❌ Route error: " . $e->getMessage() . "\n";
}