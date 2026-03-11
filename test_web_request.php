<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING WEB REQUEST SIMULATION ===" . PHP_EOL;

// Create a mock request
use Illuminate\Http\Request;

$request = Request::create('/master-data/bop-proses/create', 'GET');

// Simulate Laravel request handling
try {
    $kernel = $app->make('Illuminate\Contracts\Http\Kernel');
    
    $response = $kernel->handle($request);
    
    echo "Response Status: " . $response->getStatusCode() . PHP_EOL;
    echo "Response Content Length: " . strlen($response->getContent()) . PHP_EOL;
    
    // Check for dropdown options in response
    $content = $response->getContent();
    if (strpos($content, 'PRO-001') !== false) {
        echo "✅ Found PRO-001 in response" . PHP_EOL;
    } else {
        echo "❌ PRO-001 NOT found in response" . PHP_EOL;
    }
    
    if (strpos($content, 'Penggorengan') !== false) {
        echo "✅ Found Penggorengan in response" . PHP_EOL;
    } else {
        echo "❌ Penggorengan NOT found in response" . PHP_EOL;
    }
    
    // Look for error indicators
    if (strpos($content, 'error') !== false || strpos($content, 'Error') !== false) {
        echo "⚠️  Found error indicators in response" . PHP_EOL;
    }
    
    // Save response to file for inspection
    file_put_contents('debug_response.html', $content);
    echo "Response saved to debug_response.html for inspection" . PHP_EOL;
    
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    echo "❌ Request failed: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . PHP_EOL;
}
