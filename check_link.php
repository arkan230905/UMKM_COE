<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING STORAGE LINK ===\n\n";

$storageLink = public_path('storage');
echo "🔍 Storage link path: $storageLink\n";

if (is_link($storageLink)) {
    echo "✅ Storage link exists\n";
    $target = readlink($storageLink);
    echo "🎯 Link target: $target\n";
    echo "📁 Target exists: " . (is_dir($target) ? 'Yes' : 'No') . "\n";
    
    // Check if it's the correct target
    $expectedTarget = storage_path('app/public');
    echo "🎯 Expected target: $expectedTarget\n";
    echo "✅ Target correct: " . ($target === $expectedTarget ? 'Yes' : 'No') . "\n";
    
} else {
    echo "❌ Storage link does not exist\n";
}

echo "\n=== CHECK COMPLETE ===\n";
