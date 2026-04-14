<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging dropdown display issue...\n\n";

// Check if there are any JavaScript errors that might be affecting dropdown rendering
echo "=== CHECKING FOR JAVASCRIPT ISSUES ===\n";
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Look for any JavaScript that might be interfering with dropdown rendering
if (strpos($content, 'DOMContentLoaded') !== false) {
    echo "Found DOMContentLoaded event listener\n";
}

// Count how many DOMContentLoaded listeners there are
$domContentLoadedCount = substr_count($content, 'DOMContentLoaded');
echo "Number of DOMContentLoaded listeners: {$domContentLoadedCount}\n";

// Check for any potential conflicts in the JavaScript
if (strpos($content, 'document.addEventListener') !== false) {
    echo "Found document.addEventListener calls\n";
}

$addEventListenerCount = substr_count($content, 'document.addEventListener');
echo "Number of document.addEventListener calls: {$addEventListenerCount}\n";

// Check for any potential issues with the dropdown script
if (strpos($content, 'dropdowns.forEach') !== false) {
    echo "Found dropdowns.forEach loop\n";
}

// Look for any potential issues with the productData object
if (strpos($content, 'productData') !== false) {
    echo "Found productData object\n";
    
    // Check if productData is properly formatted
    if (strpos($content, 'const productData = {') !== false) {
        echo "productData is properly declared\n";
    }
}

echo "\n=== CHECKING FOR RENDERING ISSUES ===\n";

// Check if the @foreach loop is properly closed
$foreachCount = substr_count($content, '@foreach');
$endforeachCount = substr_count($content, '@endforeach');

echo "@foreach count: {$foreachCount}\n";
echo "@endforeach count: {$endforeachCount}\n";

if ($foreachCount !== $endforeachCount) {
    echo "WARNING: Mismatched foreach directives!\n";
} else {
    echo "foreach directives are balanced\n";
}

// Check for any potential issues with the option tags
$optionStartCount = substr_count($content, '<option');
$optionEndCount = substr_count($content, '</option>');

echo "<option> count: {$optionStartCount}\n";
echo "</option> count: {$optionEndCount}\n";

if ($optionStartCount !== $optionEndCount) {
    echo "WARNING: Mismatched option tags!\n";
} else {
    echo "option tags are balanced\n";
}

echo "\n=== TESTING VIEW RENDERING ===\n";

// Test if the view can be rendered with the data
try {
    $controller = new \App\Http\Controllers\PenjualanController();
    $response = $controller->create();
    
    // Get the rendered content
    $renderedContent = $response->render();
    
    // Check if the product options are in the rendered content
    if (strpos($renderedContent, 'Ayam Crispy Macdi') !== false) {
        echo "Product name found in rendered content\n";
    } else {
        echo "Product name NOT found in rendered content\n";
    }
    
    // Check if the option tags are in the rendered content
    if (strpos($renderedContent, '<option value="2"') !== false) {
        echo "Product option tag found in rendered content\n";
    } else {
        echo "Product option tag NOT found in rendered content\n";
    }
    
    // Check for any error messages in the rendered content
    if (strpos($renderedContent, 'Error') !== false || strpos($renderedContent, 'Exception') !== false) {
        echo "Found error messages in rendered content\n";
    } else {
        echo "No error messages found in rendered content\n";
    }
    
} catch (\Exception $e) {
    echo "Error rendering view: " . $e->getMessage() . "\n";
}

echo "\n=== POTENTIAL CAUSES ===\n";
echo "1. JavaScript might be interfering with dropdown rendering\n";
echo "2. Multiple DOMContentLoaded listeners might be conflicting\n";
echo "3. View might have rendering issues\n";
echo "4. Data might not be properly passed to the view\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Check browser console for JavaScript errors\n";
echo "2. Test the view in browser to see if options are rendered\n";
echo "3. Check if JavaScript is manipulating the dropdowns\n";
echo "4. Verify the data is actually being rendered in HTML\n";

echo "\nDone.\n";
