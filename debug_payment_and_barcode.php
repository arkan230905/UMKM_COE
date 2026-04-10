<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging payment method and barcode scanner issues...\n\n";

// Check payment method and receiving account setup
echo "=== CHECKING PAYMENT METHOD SETUP ===\n";
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
if (file_exists($bladeFile)) {
    $content = file_get_contents($bladeFile);
    
    // Check if payment method dropdown exists
    if (strpos($content, 'payment_method') !== false) {
        echo "Found payment method dropdown\n";
    }
    
    // Check if receiving account dropdown exists
    if (strpos($content, 'sumber_dana') !== false) {
        echo "Found receiving account dropdown\n";
    }
    
    // Check if there's any JavaScript for payment method change
    if (strpos($content, 'payment_method_jual') !== false) {
        echo "Found payment_method_jual element\n";
    }
    
    // Check if there's any change event handler for payment method
    if (strpos($content, 'change') !== false) {
        echo "Found change event handlers\n";
    }
    
    // Look for any existing payment method JavaScript
    if (strpos($content, 'payment_method') !== false && strpos($content, 'addEventListener') !== false) {
        echo "Found payment method related event listeners\n";
    }
}

// Check barcode scanner functionality
echo "\n=== CHECKING BARCODE SCANNER FUNCTIONALITY ===\n";

// Check if barcode scanner input exists
if (strpos($content, 'barcode-scanner') !== false) {
    echo "Found barcode scanner input\n";
}

// Check if productData exists
if (strpos($content, 'productData') !== false) {
    echo "Found productData object\n";
}

// Check if addProductByBarcode function exists
if (strpos($content, 'addProductByBarcode') !== false) {
    echo "Found addProductByBarcode function\n";
}

// Check if processAutomaticBarcode function exists
if (strpos($content, 'processAutomaticBarcode') !== false) {
    echo "Found processAutomaticBarcode function\n";
}

// Check if handleBarcodeInput function exists
if (strpos($content, 'handleBarcodeInput') !== false) {
    echo "Found handleBarcodeInput function\n";
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

echo "\n=== POTENTIAL ISSUES ===\n";
echo "1. Payment method change event might not be implemented\n";
echo "2. Barcode scanner might not be properly connected to product addition\n";
echo "3. JavaScript consolidation might have broken some functionality\n";
echo "4. Event listeners might not be properly attached\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Add payment method change event handler\n";
echo "2. Fix barcode scanner to add products to table\n";
echo "3. Ensure productData is properly populated\n";
echo "4. Test both functionalities independently\n";

echo "\nDone.\n";
