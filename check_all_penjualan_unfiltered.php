<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING ALL PENJUALAN (UNFILTERED)\n";
echo "===================================\n\n";

// Get ALL penjualan without any filters
$allPenjualans = App\Models\Penjualan::get();

echo "Total ALL penjualan records (including soft deletes): " . $allPenjualans->count() . "\n\n";

foreach ($allPenjualans as $penjualan) {
    echo "ID: {$penjualan->id}\n";
    echo "Nomor: '{$penjualan->nomor_penjualan}'\n";
    echo "Tanggal: {$penjualan->tanggal}\n";
    echo "Payment Method: '{$penjualan->payment_method}'\n";
    echo "Deleted At: " . ($penjualan->deleted_at ?? 'NULL') . "\n";
    echo "---\n";
}

echo "\nOnly active penjualans:\n";
$activePenjualans = App\Models\Penjualan::get();
echo "Total active: " . $activePenjualans->count() . "\n";

foreach ($activePenjualans as $penjualan) {
    echo "- {$penjualan->nomor_penjualan}\n";
}

echo "\nSearching for SJ-260412 patterns:\n";
$patterns = ['SJ-260412-001', 'SJ-260412-002', 'SJ-260412-003'];

foreach ($patterns as $pattern) {
    $found = App\Models\Penjualan::where('nomor_penjualan', $pattern)->first();
    if ($found) {
        echo "FOUND: {$pattern} (ID: {$found->id})\n";
    } else {
        echo "NOT FOUND: {$pattern}\n";
    }
}

echo "\nChecking if there's any data manipulation in views:\n";
echo "Looking for any hardcoded data or JavaScript modifications...\n";

?>
