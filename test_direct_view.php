<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Direct View Rendering ===\n";

// Simulate request data
$request = new \Illuminate\Http\Request([
    'tipe' => 'bahan_pendukung',
    'item_id' => null,
    'satuan_id' => null
]);

// Get data like controller does
$tipe = $request->get('tipe', 'material');
$materials = \App\Models\BahanBaku::with('satuan')->orderBy('nama_bahan', 'asc')->get();
$products = \App\Models\Produk::with('satuan')->orderBy('nama_produk', 'asc')->get();
$bahanPendukungs = \App\Models\BahanPendukung::with('satuanRelation')->orderBy('nama_bahan', 'asc')->get();

echo "Tipe: $tipe\n";
echo "Materials count: " . $materials->count() . "\n";
echo "Products count: " . $products->count() . "\n";
echo "Bahan Pendukungs count: " . $bahanPendukungs->count() . "\n";

// Check the condition in view
echo "\n=== View Condition Check ===\n";
echo "request('tipe', 'material'): " . request('tipe', 'material') . "\n";
echo "request('tipe'): " . request('tipe') . "\n";

if (request('tipe', 'material') == 'material') {
    echo "✅ Will show materials\n";
} elseif (request('tipe') == 'product') {
    echo "✅ Will show products\n";
} elseif (request('tipe') == 'bahan_pendukung') {
    echo "✅ Will show bahan pendukungs\n";
} else {
    echo "❌ Unknown type\n";
}

// Test the actual condition
echo "\n=== Actual Condition Test ===\n";
$testTipe = 'bahan_pendukung';
echo "Test tipe: $testTipe\n";

if ($testTipe == 'material') {
    echo "❌ Would show materials (WRONG)\n";
} elseif ($testTipe == 'product') {
    echo "❌ Would show products (WRONG)\n";
} elseif ($testTipe == 'bahan_pendukung') {
    echo "✅ Would show bahan pendukungs (CORRECT)\n";
} else {
    echo "❌ Unknown type\n";
}

// Show first few bahan pendukung names
echo "\n=== First 5 Bahan Pendukung ===\n";
foreach ($bahanPendukungs->take(5) as $bp) {
    echo "- {$bp->id}: {$bp->nama_bahan}\n";
}
