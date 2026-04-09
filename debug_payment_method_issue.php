<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging payment method issue...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

echo "=== CHECKING CURRENT PAYMENT METHOD HANDLER ===\n";

// Look for the payment method change handler
if (strpos($content, 'payment_methodSelect.addEventListener("change"') !== false) {
    echo "Found payment method change event listener\n";
    
    // Extract the relevant section
    $startPos = strpos($content, 'payment_methodSelect.addEventListener("change"');
    $endPos = strpos($content, '});', $startPos) + 3;
    $handlerCode = substr($content, $startPos, $endPos - $startPos);
    
    echo "Current handler code:\n";
    echo $handlerCode . "\n";
    
    // Check if the logic is correct
    if (strpos($handlerCode, 'paymentMethod === "cash"') !== false) {
        echo "Found cash payment logic\n";
        
        if (strpos($handlerCode, 'option.value === "112"') !== false) {
            echo "Found Kas (112) selection logic\n";
        } else {
            echo "ERROR: Kas (112) selection logic not found\n";
        }
    }
    
    if (strpos($handlerCode, 'paymentMethod === "transfer"') !== false) {
        echo "Found transfer payment logic\n";
        
        if (strpos($handlerCode, 'option.value === "111"') !== false) {
            echo "Found Kas Bank (111) selection logic\n";
        } else {
            echo "ERROR: Kas Bank (111) selection logic not found\n";
        }
    }
    
    if (strpos($handlerCode, 'paymentMethod === "credit"') !== false) {
        echo "Found credit payment logic\n";
        
        if (strpos($handlerCode, 'sumberDanaWrapper.style.display = "none"') !== false) {
            echo "Found credit hide logic\n";
        } else {
            echo "ERROR: Credit hide logic not found\n";
        }
    }
} else {
    echo "ERROR: Payment method change event listener not found\n";
}

echo "\n=== CHECKING FOR MULTIPLE HANDLERS ===\n";

// Count how many times the payment method handler appears
$handlerCount = substr_count($content, 'paymentMethodSelect.addEventListener("change"');
echo "Number of payment method change handlers: {$handlerCount}\n";

if ($handlerCount > 1) {
    echo "WARNING: Multiple handlers may cause conflicts\n";
}

echo "\n=== CHECKING FOR TRIGGER ON PAGE LOAD ===\n";

if (strpos($content, 'paymentMethodSelect.dispatchEvent(new Event("change"))') !== false) {
    echo "Found page load trigger\n";
} else {
    echo "ERROR: Page load trigger not found\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Check if there are multiple handlers conflicting\n";
echo "2. Verify the payment method values match the logic\n";
echo "3. Ensure the page load trigger is working\n";
echo "4. Check browser console for JavaScript errors\n";

echo "\nDone.\n";
