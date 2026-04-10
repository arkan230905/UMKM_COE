<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Checking penjualan transactions and stock movements...\n\n";

// Get all penjualan transactions
$penjualans = \App\Models\Penjualan::orderBy('id')->get(['id', 'nomor_transaksi', 'tanggal', 'total_harga']);

echo "Total penjualan transactions: " . $penjualans->count() . "\n\n";

foreach ($penjualans as $penjualan) {
    echo "=== Penjualan ID: {$penjualan->id} ===\n";
    echo "Nomor: {$penjualan->nomor_transaksi}\n";
    echo "Tanggal: {$penjualan->tanggal}\n";
    echo "Total: " . number_format($penjualan->total_harga, 0, ',', '.') . "\n";
    
    // Check penjualan details
    $details = \App\Models\PenjualanDetail::where('penjualan_id', $penjualan->id)
        ->with('produk')
        ->get();
    
    echo "Details count: " . $details->count() . "\n";
    
    foreach ($details as $detail) {
        echo "  Produk: " . ($detail->produk->nama_produk ?? 'Unknown') . "\n";
        echo "  Jumlah: {$detail->jumlah}\n";
        echo "  Harga: " . number_format($detail->harga_satuan, 0, ',', '.') . "\n";
        echo "  Subtotal: " . number_format($detail->subtotal, 0, ',', '.') . "\n";
    }
    
    // Check stock movements for this penjualan
    $stockMovements = \App\Models\StockMovement::where('ref_type', 'sale')
        ->where('ref_id', $penjualan->id)
        ->get();
    
    echo "Stock movements: " . $stockMovements->count() . "\n";
    
    foreach ($stockMovements as $movement) {
        echo "  Movement ID: {$movement->id}\n";
        echo "  Item Type: {$movement->item_type}\n";
        echo "  Item ID: {$movement->item_id}\n";
        echo "  Qty: {$movement->qty}\n";
        echo "  Direction: {$movement->direction}\n";
        echo "  Total Cost: " . number_format($movement->total_cost, 0, ',', '.') . "\n";
    }
    
    echo "\n";
}

echo "=== CHECKING PRODUCT STOCK STATUS ===\n";

// Get all products
$products = \App\Models\Produk::all(['id', 'nama_produk', 'stok']);

foreach ($products as $product) {
    echo "Produk: {$product->nama_produk}\n";
    echo "  Current Stock: {$product->stok}\n";
    
    // Check stock movements for this product
    $movements = \App\Models\StockMovement::where('item_type', 'product')
        ->where('item_id', $product->id)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    echo "  Recent movements (last 5):\n";
    foreach ($movements as $movement) {
        echo "    " . $movement->created_at . " - {$movement->direction} {$movement->qty} (ref: {$movement->ref_type}:{$movement->ref_id})\n";
    }
    echo "\n";
}

echo "Done.\n";
