<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CREATING DIRECT SYMBOLIC LINK ===\n\n";

$source = storage_path('app/public');
$target = public_path('storage');

echo "📁 Source: $source\n";
echo "🎯 Target: $target\n";

// Remove existing if it exists
if (is_link($target)) {
    unlink($target);
    echo "✅ Removed existing link\n";
}

// Create the link using PHP's symlink function
if (symlink($source, $target)) {
    echo "✅ Symbolic link created successfully\n";
} else {
    echo "❌ Failed to create symbolic link\n";
    echo "🔍 Error: " . error_get_last()['message'] . "\n";
}

// Verify the link
if (is_link($target)) {
    echo "✅ Link verification: SUCCESS\n";
    echo "🎯 Link points to: " . readlink($target) . "\n";
} else {
    echo "❌ Link verification: FAILED\n";
}

echo "\n=== LINK CREATION COMPLETE ===\n";
