<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Data Bahan Pendukung ===\n";

// Get all bahan pendukung
$bahanPendukungs = \App\Models\BahanPendukung::with('satuanRelation')->orderBy('nama_bahan', 'asc')->get();

echo "Total Bahan Pendukung: " . $bahanPendukungs->count() . "\n\n";

echo "Daftar Bahan Pendukung:\n";
foreach ($bahanPendukungs as $bp) {
    echo "ID: {$bp->id}\n";
    echo "Nama: {$bp->nama_bahan}\n";
    echo "Satuan: " . ($bp->satuanRelation->nama_satuan ?? 'N/A') . "\n";
    echo "Stok: " . $bp->stok . "\n";
    echo "Harga: Rp " . number_format($bp->harga_satuan, 2) . "\n";
    echo "---\n";
}

// Test view compilation with bahan pendukung data
echo "\n=== Test View Compilation ===\n";

try {
    $view = view('laporan.stok.index', [
        'tipe' => 'bahan_pendukung',
        'item_id' => null,
        'satuan_id' => null,
        'from' => null,
        'to' => null,
        'materials' => \App\Models\BahanBaku::all(),
        'products' => \App\Models\Produk::all(),
        'bahanPendukungs' => $bahanPendukungs,
        'dailyStock' => [],
        'conversionData' => [],
        'item' => null,
        'saldoAwalQty' => 0,
        'saldoAwalNilai' => 0,
        'running' => []
    ]);
    
    echo "✅ View compilation successful with bahan pendukung data\n";
    
    // Check if view contains bahan pendukung options
    $viewContent = $view->render();
    if (strpos($viewContent, 'Air') !== false) {
        echo "✅ View contains 'Air' option\n";
    } else {
        echo "❌ View does not contain 'Air' option\n";
    }
    
    if (strpos($viewContent, 'Minyak Goreng') !== false) {
        echo "✅ View contains 'Minyak Goreng' option\n";
    } else {
        echo "❌ View does not contain 'Minyak Goreng' option\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
