<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging biaya ongkir field focus and value retention issue...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

echo "=== CURRENT BIAYA ONGKIR FIELD ===\n";

// Find the biaya ongkir field definition
$pattern = '/biaya_ongkir.*?id="biaya_ongkir"[^>]*>/';
if (preg_match($pattern, $content, $matches)) {
    echo "Found biaya_ongkir field: " . $matches[0] . "\n";
} else {
    echo "Biaya ongkir field pattern not found\n";
}

// Check for any JavaScript that might be affecting this field
echo "\n=== CHECKING JAVASCRIPT THAT MIGHT AFFECT BIAYA ONGKIR ===\n";

// Look for any JavaScript that might reset or interfere with biaya_ongkir
$jsPatterns = [
    '/biaya_ongkir/i',
    '/biayaOngkir/i',
    '/getElementById.*biaya_ongkir/i',
    '/querySelector.*biaya_ongkir/i'
];

foreach ($jsPatterns as $pattern) {
    if (preg_match_all($pattern, $content, $matches)) {
        echo "Found JavaScript references to biaya_ongkir:\n";
        foreach ($matches as $match) {
            echo "  " . trim($match) . "\n";
        }
    }
}

// Check for any event listeners that might be causing issues
echo "\n=== CHECKING EVENT LISTENERS ===\n";
if (strpos($content, 'addEventListener') !== false) {
    echo "Found addEventListener calls - checking for biaya_ongkir interference...\n";
    
    // Extract relevant JavaScript sections
    $jsStart = strpos($content, '<script>');
    $jsContent = substr($content, $jsStart);
    
    if (strpos($jsContent, 'biaya_ongkir') !== false) {
        echo "JavaScript contains biaya_ongkir references\n";
        
        // Look for any reset or value changing code
        if (strpos($jsContent, 'value') !== false) {
            echo "Found value assignments that might affect biaya_ongkir\n";
        }
        
        if (strpos($jsContent, 'focus') !== false) {
            echo "Found focus handlers that might affect biaya_ongkir\n";
        }
        
        if (strpos($jsContent, 'blur') !== false) {
            echo "Found blur handlers that might affect biaya_ongkir\n";
        }
    }
}

echo "\n=== POTENTIAL ISSUES ===\n";
echo "1. Field might have conflicting event handlers\n";
echo "2. JavaScript might be resetting the value\n";
echo "3. Focus might be jumping away from the field\n";
echo "4. There might be validation that clears the field\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Add specific event listeners for biaya_ongkir field\n";
echo "2. Prevent value reset when field is clicked\n";
echo "3. Ensure focus stays on biaya_ongkir when user clicks it\n";
echo "4. Check for any validation that might clear the field\n";

echo "\nDone.\n";
