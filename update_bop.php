<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Update all BOP values to 3190
$bomJobCostings = \App\Models\BomJobCosting::all();

foreach ($bomJobCostings as $bom) {
    echo "Updating Product ID: {$bom->produk_id}, Old BOP: {$bom->total_bop}, New BOP: 3190\n";
    
    $bom->total_bop = 3190;
    $bom->save();
    
    // Recalculate totals
    $bom->recalculate();
    
    echo "Updated Product ID: {$bom->produk_id}, New Total BOP: {$bom->total_bop}, New HPP: {$bom->total_hpp}\n";
}

echo "\nAll BOP values updated to 3190!\n";
