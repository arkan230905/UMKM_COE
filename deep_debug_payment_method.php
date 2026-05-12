<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Deep debugging payment method issue...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

echo "=== CHECKING PAYMENT METHOD DROPDOWN OPTIONS ===\n";

// Look for the payment method dropdown in HTML
if (preg_match('/<select[^>]*name="payment_method"[^>]*>(.*?)<\/select>/s', $content, $matches)) {
    $dropdownContent = $matches[1];
    echo "Payment method dropdown found\n";
    
    // Extract all options
    if (preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>(.*?)<\/option>/', $dropdownContent, $optionMatches)) {
        echo "Payment method options:\n";
        foreach ($optionMatches[1] as $index => $value) {
            $text = trim($optionMatches[2][$index]);
            echo "  Value: '{$value}' -> Text: '{$text}'\n";
        }
    }
} else {
    echo "Payment method dropdown not found\n";
}

echo "\n=== CHECKING PAYMENT METHOD HANDLER ===\n";

// Look for the payment method handler
if (strpos($content, 'paymentMethodSelect.addEventListener("change"') !== false) {
    echo "Payment method handler found\n";
    
    // Extract the handler code
    $startPos = strpos($content, 'paymentMethodSelect.addEventListener("change"');
    $endPos = strpos($content, '});', $startPos) + 3;
    $handlerCode = substr($content, $startPos, $endPos - $startPos);
    
    echo "Handler code:\n";
    echo $handlerCode . "\n";
    
    // Check the logic
    if (strpos($handlerCode, 'paymentMethod === "cash"') !== false) {
        echo "Found cash logic\n";
        
        if (strpos($handlerCode, 'option.value === "112"') !== false) {
            echo "Found Kas (112) selection\n";
        } else {
            echo "ERROR: Kas (112) selection not found\n";
        }
    } else {
        echo "ERROR: Cash logic not found\n";
    }
    
    if (strpos($handlerCode, 'paymentMethod === "transfer"') !== false) {
        echo "Found transfer logic\n";
        
        if (strpos($handlerCode, 'option.value === "111"') !== false) {
            echo "Found Kas Bank (111) selection\n";
        } else {
            echo "ERROR: Kas Bank (111) selection not found\n";
        }
    } else {
        echo "ERROR: Transfer logic not found\n";
    }
} else {
    echo "ERROR: Payment method handler not found\n";
}

echo "\n=== CHECKING FOR CONFLICTING HANDLERS ===\n";

// Count how many times payment method handlers appear
$handlerCount = substr_count($content, 'paymentMethodSelect.addEventListener("change"');
echo "Number of payment method handlers: {$handlerCount}\n";

// Check for any other payment method related code
if (strpos($content, 'payment_method_jual') !== false) {
    echo "Found payment_method_jual references\n";
}

echo "\n=== CHECKING FOR SCRIPT CONFLICTS ===\n";

// Count script tags
$scriptCount = substr_count($content, '<script>');
echo "Number of script tags: {$scriptCount}\n";

// Count DOMContentLoaded listeners
$domContentLoadedCount = substr_count($content, 'document.addEventListener("DOMContentLoaded"');
echo "Number of DOMContentLoaded listeners: {$domContentLoadedCount}\n";

echo "\n=== POTENTIAL ISSUES ===\n";
echo "1. Payment method value might not match the dropdown option value\n";
echo "2. Multiple handlers might be conflicting\n";
echo "3. Handler might not be executing due to JavaScript errors\n";
echo "4. Element IDs might not match\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Check browser console for JavaScript errors\n";
echo "2. Verify payment method dropdown values\n";
echo "3. Check if handler is actually being called\n";
echo "4. Simplify the handler for testing\n";

echo "\nDone.\n";
