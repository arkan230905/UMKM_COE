<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Creating missing stock movements for penjualan transactions...\n\n";

// Get penjualan ID 5
$penjualan = \App\Models\Penjualan::with(['details.produk'])->find(5);

if (!$penjualan) {
    echo "Penjualan ID 5 not found!\n";
    exit;
}

echo "=== Penjualan ID: {$penjualan->id} ===\n";
echo "Nomor: {$penjualan->nomor_penjualan}\n";
echo "Tanggal: {$penjualan->tanggal}\n";
echo "Total: " . number_format($penjualan->total, 0, ',', '.') . "\n";

// Check if stock movements already exist
$existingMovements = \App\Models\StockMovement::where('ref_type', 'sale')
    ->where('ref_id', $penjualan->id)
    ->count();

echo "Existing stock movements: {$existingMovements}\n";

if ($existingMovements > 0) {
    echo "Stock movements already exist for this penjualan.\n";
    exit;
}

// Process each detail
foreach ($penjualan->details as $detail) {
    echo "\n--- Processing Detail ID: {$detail->id} ---\n";
    echo "Produk: " . ($detail->produk->nama_produk ?? 'Unknown') . "\n";
    echo "Jumlah: {$detail->jumlah}\n";
    echo "Harga: " . number_format($detail->harga_satuan, 0, ',', '.') . "\n";
    echo "Subtotal: " . number_format($detail->subtotal, 0, ',', '.') . "\n";
    
    if (!$detail->produk) {
        echo "ERROR: Produk not found for detail ID {$detail->id}\n";
        continue;
    }
    
    $produk = $detail->produk;
    $qty = (float) $detail->jumlah;
    
    echo "Current stock before sale: " . ($produk->stok ?? 0) . "\n";
    
    // Create stock movement OUT
    try {
        $stockService = new \App\Services\StockService();
        
        // Get HPP for cost calculation
        $hpp = $produk->getHPPForSaleDate($penjualan->tanggal);
        if ($hpp <= 0) {
            // Fallback to BOM calculation
            $sumBom = (float) \App\Models\Bom::where('produk_id', $produk->id)->sum('total_biaya');
            $btkl = (float) ($produk->btkl_default ?? 0);
            $bop = (float) ($produk->bop_default ?? 0);
            $hpp = ($sumBom + $btkl + $bop);
        }
        
        $totalCost = $hpp * $qty;
        
        echo "HPP per unit: " . number_format($hpp, 0, ',', '.') . "\n";
        echo "Total cost: " . number_format($totalCost, 0, ',', '.') . "\n";
        
        // Create stock movement
        $movement = \App\Models\StockMovement::create([
            'tanggal' => $penjualan->tanggal,
            'item_type' => 'product',
            'item_id' => $produk->id,
            'ref_type' => 'sale',
            'ref_id' => $penjualan->id,
            'qty' => $qty,
            'direction' => 'out',
            'unit_cost' => $hpp,
            'total_cost' => $totalCost,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "Stock movement created: ID {$movement->id}\n";
        
        // Update product stock
        $newStock = (float)($produk->stok ?? 0) - $qty;
        $produk->stok = $newStock;
        $produk->save();
        
        echo "Updated stock: {$newStock}\n";
        
    } catch (\Exception $e) {
        echo "ERROR creating stock movement: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Verification ===\n";

// Check final stock
$produk = \App\Models\Produk::find($penjualan->details->first()->produk_id);
echo "Final product stock: " . ($produk->stok ?? 0) . "\n";

// Check stock movements
$movementCount = \App\Models\StockMovement::where('ref_type', 'sale')
    ->where('ref_id', $penjualan->id)
    ->count();

echo "Total stock movements created: {$movementCount}\n";

echo "\nDone.\n";
