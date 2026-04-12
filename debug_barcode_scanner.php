<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging barcode scanner functionality...\n\n";

// Check if products have barcode data
echo "=== CHECKING PRODUCT BARCODE DATA ===\n";
$products = \App\Models\Produk::all(['id', 'nama_produk', 'barcode', 'harga_jual', 'stok']);

$productsWithBarcode = 0;
$productsWithoutBarcode = 0;

foreach ($products as $product) {
    echo "Product ID: {$product->id}\n";
    echo "  Name: {$product->nama_produk}\n";
    echo "  Barcode: " . ($product->barcode ?? 'NULL') . "\n";
    echo "  Harga: " . number_format($product->harga_jual ?? 0, 0, ',', '.') . "\n";
    echo "  Stok: " . number_format($product->stok ?? 0, 0, ',', '.') . "\n";
    
    if ($product->barcode) {
        $productsWithBarcode++;
    } else {
        $productsWithoutBarcode++;
    }
    echo "\n";
}

echo "Summary:\n";
echo "Products with barcode: {$productsWithBarcode}\n";
echo "Products without barcode: {$productsWithoutBarcode}\n";

// Check the current create.blade.php file for barcode scanner issues
echo "\n=== CHECKING BARCODE SCANNER IMPLEMENTATION ===\n";
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
if (file_exists($bladeFile)) {
    $content = file_get_contents($bladeFile);
    
    // Check for barcode scanner elements
    if (strpos($content, 'barcode-scanner') !== false) {
        echo "Found barcode scanner element\n";
    }
    
    // Check for productData JavaScript object
    if (strpos($content, 'productData') !== false) {
        echo "Found productData JavaScript object\n";
    }
    
    // Check for handleBarcodeInput function
    if (strpos($content, 'handleBarcodeInput') !== false) {
        echo "Found handleBarcodeInput function\n";
    }
    
    // Check for processAutomaticBarcode function
    if (strpos($content, 'processAutomaticBarcode') !== false) {
        echo "Found processAutomaticBarcode function\n";
    }
    
    // Check for any recent modifications that might have broken the scanner
    if (strpos($content, 'maintainFocus()') !== false) {
        echo "Found maintainFocus function - might be interfering\n";
    }
    
    // Check for any focus-related issues
    if (strpos($content, 'document.activeElement') !== false) {
        echo "Found document.activeElement usage - might be interfering\n";
    }
    
} else {
    echo "Create.blade.php file not found!\n";
}

echo "\n=== POTENTIAL ISSUES ===\n";
echo "1. Products might not have barcode data\n";
echo "2. Recent focus management changes might interfere with scanner\n";
echo "3. JavaScript event handlers might be conflicting\n";
echo "4. Barcode scanner input might not be receiving focus properly\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Check if products have barcode data\n";
echo "2. Test barcode scanner focus management\n";
echo "3. Verify JavaScript event handlers are not conflicting\n";
echo "4. Check browser console for JavaScript errors\n";

echo "\nDone.\n";
