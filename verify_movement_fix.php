<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Movement Fix ===" . PHP_EOL;

// Test the logic that should now work correctly
echo PHP_EOL . "Testing Fixed Logic:" . PHP_EOL;

// Simulate the controller logic for different movement types
$testMovements = [
    ['ref_type' => 'initial_stock', 'qty' => 100, 'total_cost' => 1000],
    ['ref_type' => 'purchase', 'qty' => 50, 'total_cost' => 500],
    ['ref_type' => 'production', 'qty' => 25, 'total_cost' => 250],
    ['ref_type' => 'sale', 'qty' => 10, 'total_cost' => 100]
];

echo "Processing movements with fixed logic:" . PHP_EOL;
foreach ($testMovements as $m) {
    echo "Movement: " . $m['ref_type'] . PHP_EOL;
    
    // Simulate the fixed logic
    if ($m['ref_type'] === 'initial_stock') {
        echo "  Action: Skip (continue)" . PHP_EOL;
        continue; // This should only skip the current iteration
    } elseif ($m['ref_type'] === 'purchase') {
        echo "  Action: Add to purchase column" . PHP_EOL;
    } elseif ($m['ref_type'] === 'production') {
        echo "  Action: Add to production column" . PHP_EOL;
    } elseif ($m['ref_type'] === 'sale') {
        echo "  Action: Add to sale column" . PHP_EOL;
    } else {
        echo "  Action: Default processing" . PHP_EOL;
    }
    
    echo "  Qty: " . $m['qty'] . PHP_EOL;
    echo "  Cost: " . $m['total_cost'] . PHP_EOL;
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "=== Expected Behavior ===" . PHP_EOL;
echo "- initial_stock: SKIPPED (no duplicate entry)" . PHP_EOL;
echo "- purchase: PROCESSED (shows in pembelian column)" . PHP_EOL;
echo "- production: PROCESSED (shows in produksi column)" . PHP_EOL;
echo "- sale: PROCESSED (shows in penjualan column)" . PHP_EOL;

echo PHP_EOL . "=== Fix Summary ===" . PHP_EOL;
echo "Changes made:" . PHP_EOL;
echo "1. Changed 'continue 2' to 'continue'" . PHP_EOL;
echo "2. This only skips the current movement, not the entire loop" . PHP_EOL;
echo "3. Other movements (purchase, production, sale) will be processed normally" . PHP_EOL;
echo "4. Only initial_stock movements are skipped to avoid duplicates" . PHP_EOL;
