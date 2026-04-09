<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging dropdown data issue...\n\n";

// Check if products exist in database
echo "=== CHECKING PRODUCT DATA ===\n";
$products = \App\Models\Produk::all();

echo "Total products in database: " . $products->count() . "\n";

if ($products->count() > 0) {
    echo "Sample products:\n";
    foreach ($products->take(3) as $product) {
        echo "  ID: {$product->id}, Name: {$product->nama_produk}, Price: " . number_format($product->harga_jual ?? 0, 0, ',', '.') . "\n";
    }
} else {
    echo "No products found in database!\n";
}

// Check if kas/bank accounts exist
echo "\n=== CHECKING KAS/BANK DATA ===\n";
try {
    $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
    echo "Kas/Bank accounts found: " . count($kasbank) . "\n";
    
    if (count($kasbank) > 0) {
        echo "Sample kas/bank accounts:\n";
        foreach (array_slice($kasbank, 0, 3) as $kb) {
            echo "  Code: {$kb->kode_akun}, Name: {$kb->nama_akun}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error getting kas/bank accounts: " . $e->getMessage() . "\n";
}

// Check the PenjualanController create method
echo "\n=== CHECKING CONTROLLER ===\n";
$controllerFile = 'app/Http/Controllers/PenjualanController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    // Look for the create method
    if (strpos($content, 'public function create()') !== false) {
        echo "Found create method in PenjualanController\n";
        
        // Check if it's passing products data
        if (strpos($content, 'Produk::all()') !== false) {
            echo "Controller is calling Produk::all()\n";
        }
        
        // Check if it's passing kasbank data
        if (strpos($content, 'AccountHelper::getKasBankAccounts()') !== false) {
            echo "Controller is calling AccountHelper::getKasBankAccounts()\n";
        }
        
        // Check if it's returning the create view
        if (strpos($content, 'return view(\'transaksi.penjualan.create\'') !== false) {
            echo "Controller is returning create view\n";
        }
        
        // Check if it's passing the data to the view
        if (strpos($content, 'compact(\'produks\', \'kasbank\')') !== false) {
            echo "Controller is passing data to view\n";
        }
    } else {
        echo "Create method not found in PenjualanController\n";
    }
} else {
    echo "PenjualanController.php file not found!\n";
}

// Check the create.blade.php file for data usage
echo "\n=== CHECKING VIEW DATA USAGE ===\n";
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
if (file_exists($bladeFile)) {
    $content = file_get_contents($bladeFile);
    
    // Check if view is expecting products data
    if (strpos($content, '@foreach($produks as $p)') !== false) {
        echo "View is expecting \$produks data\n";
    }
    
    // Check if view is expecting kasbank data
    if (strpos($content, '@foreach($kasbank as $kb)') !== false) {
        echo "View is expecting \$kasbank data\n";
    }
    
    // Check if there are any errors in the view
    if (strpos($content, 'isset($produks)') !== false) {
        echo "View has isset check for \$produks\n";
    }
    
    if (strpos($content, 'isset($kasbank)') !== false) {
        echo "View has isset check for \$kasbank\n";
    }
} else {
    echo "Create.blade.php file not found!\n";
}

echo "\n=== POTENTIAL ISSUES ===\n";
echo "1. Controller might not be passing data correctly\n";
echo "2. View might have syntax errors\n";
echo "3. Database might have connection issues\n";
echo "4. Models might have relationship issues\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Check if controller create method is working\n";
echo "2. Verify data is being passed to view\n";
echo "3. Check for any PHP errors in the view\n";
echo "4. Test the create route directly\n";

echo "\nDone.\n";
