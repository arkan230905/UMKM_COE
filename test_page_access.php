<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Blade Compilation ===\n";

try {
    // Try to compile the problematic view
    $view = view('laporan.stok.index', [
        'tipe' => 'material',
        'item_id' => 1,
        'satuan_id' => 1,
        'materials' => \App\Models\BahanBaku::all(),
        'products' => \App\Models\Produk::all(),
        'bahanPendukungs' => \App\Models\BahanPendukung::all(),
        'dailyStock' => [],
        'conversionData' => [],
        'item' => null
    ]);
    
    echo "✅ Blade compilation successful!\n";
    echo "✅ No syntax errors found\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
