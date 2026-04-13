<?php

// Test the JavaScript logic in PHP to verify it works
function testVendorCategoryLogic($vendorKategori, $purchaseKategori) {
    $vendorKategori = strtolower($vendorKategori);
    
    echo "Testing: Vendor='$vendorKategori', Purchase='$purchaseKategori'\n";
    
    // Simulate the JavaScript logic
    if ((strpos($vendorKategori, 'bahan') !== false && strpos($vendorKategori, 'baku') !== false) || $purchaseKategori === 'bahan_baku') {
        echo "Result: Show only Bahan Baku section\n";
        echo "- Bahan Baku: visible\n";
        echo "- Bahan Pendukung: hidden\n";
    } elseif ((strpos($vendorKategori, 'bahan') !== false && strpos($vendorKategori, 'pendukung') !== false) || $purchaseKategori === 'bahan_pendukung') {
        echo "Result: Show only Bahan Pendukung section\n";
        echo "- Bahan Baku: hidden\n";
        echo "- Bahan Pendukung: visible\n";
    } else {
        echo "Result: Show both sections\n";
        echo "- Bahan Baku: visible\n";
        echo "- Bahan Pendukung: visible\n";
    }
    echo "---\n";
}

echo "=== TESTING VENDOR CATEGORY LOGIC ===\n\n";

// Test cases
testVendorCategoryLogic('Bahan Baku', 'mixed');
testVendorCategoryLogic('Bahan Pendukung', 'mixed');
testVendorCategoryLogic('Mixed', 'bahan_baku');
testVendorCategoryLogic('Mixed', 'bahan_pendukung');
testVendorCategoryLogic('Mixed', 'mixed');

echo "The current purchase 4 has:\n";
echo "- Vendor: 'Bahan Baku' (Tel Mart)\n";
echo "- Purchase category: 'mixed' (no details)\n";
echo "Expected result: Show only Bahan Baku section\n";