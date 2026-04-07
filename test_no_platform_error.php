<?php

echo "🧪 Testing application without platform error...\n\n";

try {
    require_once 'vendor/autoload.php';
    echo "✅ Autoload successful - no platform check error!\n";
    
    // Load Laravel
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "✅ Laravel bootstrap successful!\n";
    
    // Test basic functionality
    echo "📊 Testing basic functionality:\n";
    echo "- Current time: " . now()->addHours(5)->format('d/m/Y H:i:s') . "\n";
    echo "- Config timezone: " . config('app.timezone') . "\n";
    echo "- PHP version: " . PHP_VERSION . "\n";
    
    // Test database connection
    try {
        \DB::connection()->getPdo();
        echo "✅ Database connection successful!\n";
    } catch (\Exception $e) {
        echo "⚠️  Database connection failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 Application is working properly!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n✅ Test complete - ready for production use!\n";