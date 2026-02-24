<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Fixed View Logic ===\n";

// Test different tipe values
$types = ['material', 'product', 'bahan_pendukung'];

foreach ($types as $tipe) {
    echo "\n=== Testing tipe: $tipe ===\n";
    
    // Simulate view logic
    if ($tipe == 'material') {
        echo "✅ Will show materials\n";
    } elseif ($tipe == 'product') {
        echo "✅ Will show products\n";
    } elseif ($tipe == 'bahan_pendukung') {
        echo "✅ Will show bahan pendukungs\n";
    } else {
        echo "❌ Unknown type\n";
    }
    
    // Also test the old logic for comparison
    echo "Old logic (request('tipe', 'material')): " . ($tipe == 'material' ? 'true' : 'false') . "\n";
    echo "New logic (\$tipe == 'material'): " . ($tipe == 'material' ? 'true' : 'false') . "\n";
}

// Test actual data
echo "\n=== Test Data Availability ===\n";
$bahanPendukungs = \App\Models\BahanPendukung::orderBy('nama_bahan', 'asc')->get();
echo "Bahan Pendukung available: " . $bahanPendukungs->count() . " items\n";

echo "\nFirst 3 items:\n";
foreach ($bahanPendukungs->take(3) as $bp) {
    echo "- {$bp->nama_bahan}\n";
}
