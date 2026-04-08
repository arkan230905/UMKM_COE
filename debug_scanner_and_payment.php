<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging scanner and payment method issues...\n\n";

// Check the current state of the create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

echo "=== CHECKING CURRENT IMPLEMENTATION ===\n";

// Check if the payment method change handler exists
if (strpos($content, 'payment_method_jual') !== false) {
    echo "Found payment_method_jual element\n";
} else {
    echo "WARNING: payment_method_jual element not found\n";
}

// Check if the payment method change event exists
if (strpos($content, 'addEventListener("change"') !== false && strpos($content, 'payment_method') !== false) {
    echo "Found payment method change event\n";
} else {
    echo "WARNING: Payment method change event not found\n";
}

// Check if barcode scanner functions exist
if (strpos($content, 'handleBarcodeInput') !== false) {
    echo "Found handleBarcodeInput function\n";
} else {
    echo "WARNING: handleBarcodeInput function not found\n";
}

if (strpos($content, 'processAutomaticBarcode') !== false) {
    echo "Found processAutomaticBarcode function\n";
} else {
    echo "WARNING: processAutomaticBarcode function not found\n";
}

if (strpos($content, 'addProductByBarcode') !== false) {
    echo "Found addProductByBarcode function\n";
} else {
    echo "WARNING: addProductByBarcode function not found\n";
}

// Check if the global keydown listener exists
if (strpos($content, 'document.addEventListener(\'keydown\'') !== false) {
    echo "Found global keydown listener\n";
} else {
    echo "WARNING: Global keydown listener not found\n";
}

echo "\n=== CHECKING PRODUCT DATA ===\n";
$products = \App\Models\Produk::all();
echo "Products in database: " . $products->count() . "\n";

foreach ($products as $product) {
    echo "  ID: {$product->id}, Name: {$product->nama_produk}, Barcode: " . ($product->barcode ?? 'NULL') . "\n";
}

echo "\n=== CHECKING KAS/BANK ACCOUNTS ===\n";
try {
    $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
    echo "Kas/Bank accounts: " . count($kasbank) . "\n";
    
    foreach ($kasbank as $kb) {
        echo "  Code: {$kb->kode_akun}, Name: {$kb->nama_akun}\n";
    }
} catch (\Exception $e) {
    echo "Error getting kas/bank accounts: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING FOR CONFLICTS ===\n";

// Count DOMContentLoaded listeners
$domContentLoadedCount = substr_count($content, 'document.addEventListener("DOMContentLoaded"');
echo "DOM Content Loaded listeners: {$domContentLoadedCount}\n";

// Check if there are multiple event listeners that might conflict
if ($domContentLoadedCount > 1) {
    echo "WARNING: Multiple DOMContentLoaded listeners may cause conflicts\n";
}

// Check if the consolidated script was properly added
if (strpos($content, 'Consolidated form management system') !== false) {
    echo "Found consolidated form management system\n";
} else {
    echo "WARNING: Consolidated form management system not found\n";
}

echo "\n=== POTENTIAL ISSUES ===\n";
echo "1. Payment method change event may not be properly attached\n";
echo "2. Barcode scanner may not be receiving key events\n";
echo "3. Multiple event listeners may be conflicting\n";
echo "4. Functions may not be in the right scope\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Ensure payment method change event is properly attached\n";
echo "2. Verify barcode scanner keydown listener is working\n";
echo "3. Check browser console for JavaScript errors\n";
echo "4. Test individual components separately\n";

echo "\nDone.\n";
