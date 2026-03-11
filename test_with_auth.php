<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING WITH AUTHENTICATION ===" . PHP_EOL;

// Get admin user
$admin = \App\Models\User::where('role', 'owner')->first();
if (!$admin) {
    echo "❌ No admin user found!" . PHP_EOL;
    exit;
}

echo "Using admin user: {$admin->email} ({$admin->role})" . PHP_EOL;

// Simulate authentication
use Illuminate\Support\Facades\Auth;
Auth::login($admin);

echo "✅ User authenticated" . PHP_EOL;
echo "User ID: " . Auth::id() . PHP_EOL;
echo "User role: " . Auth::user()->role . PHP_EOL;

// Now test the BOP Proses create route
use Illuminate\Http\Request;
$request = Request::create('/master-data/bop-proses/create', 'GET');

try {
    $kernel = $app->make('Illuminate\Contracts\Http\Kernel');
    $response = $kernel->handle($request);
    
    echo "Response Status: " . $response->getStatusCode() . PHP_EOL;
    
    if ($response->getStatusCode() === 200) {
        echo "✅ Successfully accessed BOP Proses create page!" . PHP_EOL;
        
        $content = $response->getContent();
        
        // Check for dropdown options
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
        
        if (strpos($content, 'PRO-002') !== false) {
            echo "✅ Found PRO-002 in response" . PHP_EOL;
        } else {
            echo "❌ PRO-002 NOT found in response" . PHP_EOL;
        }
        
        if (strpos($content, 'Perbumbuan') !== false) {
            echo "✅ Found Perbumbuan in response" . PHP_EOL;
        } else {
            echo "❌ Perbumbuan NOT found in response" . PHP_EOL;
        }
        
        // Save response for inspection
        file_put_contents('debug_auth_response.html', $content);
        echo "Response saved to debug_auth_response.html" . PHP_EOL;
        
    } else {
        echo "❌ Failed to access page. Status: " . $response->getStatusCode() . PHP_EOL;
        echo "Content: " . $response->getContent() . PHP_EOL;
    }
    
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    echo "❌ Request failed: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . PHP_EOL;
}
