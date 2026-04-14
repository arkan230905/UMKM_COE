<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging dropdown field closing issue...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources\views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

echo "=== CHECKING DROPDOWN IMPLEMENTATION ===\n";

// Check for dropdown/select elements
if (strpos($content, '<select') !== false) {
    echo "Found select elements in the file\n";
    
    // Count select elements
    $selectCount = substr_count($content, '<select');
    echo "Number of select elements: {$selectCount}\n";
}

// Check for any JavaScript that might be interfering with dropdowns
echo "\n=== CHECKING JAVASCRIPT INTERFERENCE ===\n";

// Look for potential issues
$issues = [
    'addEventListener' => 'Event listeners might be interfering',
    'focus' => 'Focus events might be closing dropdowns',
    'blur' => 'Blur events might be closing dropdowns',
    'click' => 'Click events might be interfering',
    'maintainFocus' => 'Focus management might be interfering',
    'setTimeout' => 'Timers might be interfering',
    'setInterval' => 'Intervals might be interfering'
];

foreach ($issues as $pattern => $description) {
    if (strpos($content, $pattern) !== false) {
        echo "Found {$pattern}: {$description}\n";
    }
}

// Check for specific dropdown-related code
echo "\n=== CHECKING SPECIFIC DROPDOWN CODE ===\n";

// Look for produk-select class
if (strpos($content, 'produk-select') !== false) {
    echo "Found produk-select class\n";
}

// Look for payment_method dropdown
if (strpos($content, 'payment_method') !== false) {
    echo "Found payment_method dropdown\n";
}

// Look for sumber_dana dropdown
if (strpos($content, 'sumber_dana') !== false) {
    echo "Found sumber_dana dropdown\n";
}

echo "\n=== POTENTIAL CAUSES ===\n";
echo "1. Focus management system might be interfering\n";
echo "2. Event listeners might be closing dropdowns\n";
echo "3. Barcode scanner focus might be stealing focus\n";
echo "4. Timer-based focus management might be interfering\n";
echo "5. Click event handlers might be preventing dropdown interaction\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Add specific event handlers for dropdown elements\n";
echo "2. Prevent focus management from interfering with dropdowns\n";
echo "3. Add click prevention for dropdown interactions\n";
echo "4. Ensure dropdowns stay open when user is selecting\n";

echo "\nDone.\n";
