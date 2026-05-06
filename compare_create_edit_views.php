<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COMPARE CREATE vs EDIT VIEWS ===\n\n";

echo "1. CEK STRUKTUR CREATE VIEW:\n\n";

try {
    $createViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
    $createViewContent = file_get_contents($createViewFile);
    
    echo "Key sections in create view:\n";
    
    // Check for product selection section
    if (strpos($createViewContent, 'Pilih Produk') !== false) {
        echo "✅ Product selection section exists\n";
    } else {
        echo "❌ Product selection section missing\n";
    }
    
    // Check for biaya bahan calculation
    if (strpos($createViewContent, 'data-biaya-bahan') !== false) {
        echo "✅ Biaya bahan data attribute exists\n";
    } else {
        echo "❌ Biaya bahan data attribute missing\n";
    }
    
    // Check for BTKL processes
    if (strpos($createViewContent, 'Pilih Proses BTKL') !== false) {
        echo "✅ BTKL process selection exists\n";
    } else {
        echo "❌ BTKL process selection missing\n";
    }
    
    // Check for BOP components
    if (strpos($createViewContent, 'Detail Komponen BOP') !== false) {
        echo "✅ BOP components section exists\n";
    } else {
        echo "❌ BOP components section missing\n";
    }
    
    // Check for JavaScript
    if (strpos($createViewContent, 'calculateTotal()') !== false) {
        echo "✅ JavaScript calculation exists\n";
    } else {
        echo "❌ JavaScript calculation missing\n";
    }
    
    // Check for form submission
    if (strpos($createViewContent, 'form') !== false) {
        echo "✅ Form exists\n";
    } else {
        echo "❌ Form missing\n";
    }
    
    echo "\nCreate view size: " . strlen($createViewContent) . " characters\n";
    
} catch (\Exception $e) {
    echo "Error checking create view: " . $e->getMessage() . "\n";
}

echo "\n2. CEK STRUKTUR EDIT VIEW:\n\n";

try {
    $editViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\edit.blade.php';
    $editViewContent = file_get_contents($editViewFile);
    
    echo "Key sections in edit view:\n";
    
    // Check for product selection section
    if (strpos($editViewContent, 'Pilih Produk') !== false) {
        echo "✅ Product selection section exists\n";
    } else {
        echo "❌ Product selection section missing\n";
    }
    
    // Check for biaya bahan calculation
    if (strpos($editViewContent, 'data-biaya-bahan') !== false) {
        echo "✅ Biaya bahan data attribute exists\n";
    } else {
        echo "❌ Biaya bahan data attribute missing\n";
    }
    
    // Check for BTKL processes
    if (strpos($editViewContent, 'Pilih Proses BTKL') !== false) {
        echo "✅ BTKL process selection exists\n";
    } else {
        echo "❌ BTKL process selection missing\n";
    }
    
    // Check for BOP components
    if (strpos($editViewContent, 'Detail Komponen BOP') !== false) {
        echo "✅ BOP components section exists\n";
    } else {
        echo "❌ BOP components section missing\n";
    }
    
    // Check for JavaScript
    if (strpos($editViewContent, 'calculateTotal()') !== false) {
        echo "✅ JavaScript calculation exists\n";
    } else {
        echo "❌ JavaScript calculation missing\n";
    }
    
    // Check for form submission
    if (strpos($editViewContent, 'form') !== false) {
        echo "✅ Form exists\n";
    } else {
        echo "❌ Form missing\n";
    }
    
    echo "\nEdit view size: " . strlen($editViewContent) . " characters\n";
    
} catch (\Exception $e) {
    echo "Error checking edit view: " . $e->getMessage() . "\n";
}

echo "\n3. CEK PERBEDAAN KUNCI:\n\n";

try {
    $createViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\create.blade.php';
    $editViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\edit.blade.php';
    
    $createViewContent = file_get_contents($createViewFile);
    $editViewContent = file_get_contents($editViewFile);
    
    echo "Key differences:\n";
    
    // Check for product selection logic
    if (strpos($createViewContent, '@php') !== false && strpos($editViewContent, '@php') !== false) {
        echo "✅ Both views have PHP logic for biaya bahan\n";
    } else {
        echo "❌ Different biaya bahan calculation logic\n";
    }
    
    // Check for checkbox selection
    if (strpos($createViewContent, 'proses-checkbox') !== false && strpos($editViewContent, 'proses-checkbox') !== false) {
        echo "✅ Both views have process checkboxes\n";
    } else {
        echo "❌ Different process selection approach\n";
    }
    
    // Check for BOP JavaScript
    if (strpos($createViewContent, 'Use rate_per_produk directly from controller') !== false) {
        echo "✅ Create view has fixed BOP JavaScript\n";
    } else {
        echo "❌ Create view BOP JavaScript not fixed\n";
    }
    
    if (strpos($editViewContent, 'Use rate_per_produk directly from controller') !== false) {
        echo "✅ Edit view has fixed BOP JavaScript\n";
    } else {
        echo "❌ Edit view BOP JavaScript not fixed\n";
    }
    
    // Check for form method
    if (strpos($createViewContent, 'method="POST"') !== false) {
        echo "✅ Create view uses POST method\n";
    } else {
        echo "❌ Create view method issue\n";
    }
    
    if (strpos($editViewContent, 'method="POST"') !== false) {
        echo "✅ Edit view uses POST method\n";
    } else {
        echo "❌ Edit view method issue\n";
    }
    
} catch (\Exception $e) {
    echo "Error comparing views: " . $e->getMessage() . "\n";
}

echo "\n4. CEK DATA YANG DITERIMA:\n\n";

try {
    echo "Data passed to create view:\n";
    echo "- \$produks (products with BBB data)\n";
    echo "- \$prosesBtkl (processes with BTKL/BOP)\n";
    
    echo "\nData passed to edit view:\n";
    echo "- \$produk (single product)\n";
    echo "- \$bomJobCosting (existing cost data)\n";
    echo "- \$prosesBtkl (processes with BTKL/BOP)\n";
    echo "- \$selectedProses (pre-selected processes)\n";
    
    echo "\nKey difference: Edit view has pre-selected data\n";
    
} catch (\Exception $e) {
    echo "Error checking data: " . $e->getMessage() . "\n";
}

echo "\n5. IDENTIFIKASI AREA YANG PERLU DIPERBAIKI:\n\n";

try {
    echo "Areas that need to be synchronized:\n";
    
    $editViewFile = 'c:\UMKM_COE\resources\views\master-data\bom\edit.blade.php';
    $editViewContent = file_get_contents($editViewFile);
    
    // Check if edit view has the same biaya bahan logic
    if (strpos($editViewContent, 'Calculate from bom_job_bbb if BomJobCosting doesn\'t exist') === false) {
        echo "❌ Edit view needs biaya bahan fallback logic\n";
    } else {
        echo "✅ Edit view has biaya bahan fallback logic\n";
    }
    
    // Check if edit view has the same BOP JavaScript
    if (strpos($editViewContent, 'Use rate_per_produk directly from controller') === false) {
        echo "❌ Edit view needs BOP JavaScript fix\n";
    } else {
        echo "✅ Edit view has BOP JavaScript fix\n";
    }
    
    // Check if edit view has the same form structure
    if (strpos($editViewContent, '@method(\'PUT\')') !== false) {
        echo "✅ Edit view uses PUT method (correct for edit)\n";
    } else {
        echo "❌ Edit view form method issue\n";
    }
    
    echo "\nRecommendations:\n";
    echo "1. Add biaya bahan fallback logic to edit view\n";
    echo "2. Ensure BOP JavaScript is consistent\n";
    echo "3. Verify form structure is correct for edit\n";
    echo "4. Test pre-selected processes functionality\n";
    
} catch (\Exception $e) {
    echo "Error identifying fixes: " . $e->getMessage() . "\n";
}

echo "\n=== COMPARISON COMPLETE ===\n";
