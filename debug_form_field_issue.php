<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging form field click issue on penjualan create page...\n\n";

// Check if there are any JavaScript conflicts or issues
echo "=== CHECKING FORM STRUCTURE ===\n";

// Read the create.blade.php file to identify potential issues
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
if (file_exists($bladeFile)) {
    $content = file_get_contents($bladeFile);
    
    // Check for potential issues
    echo "Checking for potential issues:\n";
    
    // 1. Check for autofocus on barcode scanner
    if (strpos($content, 'autofocus') !== false) {
        echo "✓ Found autofocus on barcode scanner\n";
    }
    
    // 2. Check for readonly fields that might prevent input
    if (strpos($content, 'readonly') !== false) {
        echo "⚠️ Found readonly fields - this might prevent manual input\n";
        $readonlyCount = substr_count($content, 'readonly');
        echo "  Number of readonly attributes: {$readonlyCount}\n";
    }
    
    // 3. Check for JavaScript event handlers that might interfere
    if (strpos($content, 'onclick') !== false) {
        echo "✓ Found onclick handlers\n";
    }
    
    // 4. Check for form validation that might prevent focus
    if (strpos($content, 'required') !== false) {
        echo "✓ Found required fields\n";
    }
    
    // 5. Check for any JavaScript that might redirect focus
    if (strpos($content, 'maintainFocus()') !== false) {
        echo "✓ Found maintainFocus() function\n";
    }
    
    // 6. Check for barcode scanner auto-focus
    if (strpos($content, 'maintainFocus()') !== false) {
        echo "⚠️ Auto-focus system might interfere with manual input\n";
    }
    
    // 7. Check for any CSS that might interfere
    if (strpos($content, 'form-control') !== false) {
        echo "✓ Found form-control classes\n";
    }
    
    echo "\n=== POTENTIAL ISSUES ===\n";
    echo "1. Barcode scanner has autofocus - might steal focus from other fields\n";
    echo "2. Some fields are readonly - prevents manual input\n";
    echo "3. Auto-focus system keeps returning focus to barcode scanner\n";
    echo "4. Event handlers might interfere with normal field interaction\n";
    
    echo "\n=== RECOMMENDATIONS ===\n";
    echo "1. Remove autofocus from barcode scanner to allow manual field interaction\n";
    echo "2. Make sure only necessary fields are readonly\n";
    echo "3. Modify maintainFocus() to not interfere when user is manually typing\n";
    echo "4. Add proper event handling for field clicks\n";
    
} else {
    echo "Create.blade.php file not found!\n";
}

echo "\nDone.\n";
