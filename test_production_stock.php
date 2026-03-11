<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check latest production
$produksi = \App\Models\Produksi::with(['produk', 'details.bahanBaku'])->latest()->first();

if ($produksi) {
    echo "=== PRODUKSI TERAKHIR ===\n";
    echo "ID: {$produksi->id}\n";
    echo "Produk: {$produksi->produk->nama_produk}\n";
    echo "Qty Produksi: {$produksi->qty_produksi}\n";
    echo "Tanggal: {$produksi->tanggal}\n\n";
    
    echo "=== BAHAN YANG TERPAKAI ===\n";
    foreach ($produksi->details as $detail) {
        $bahan = $detail->bahanBaku;
        echo "Bahan: {$bahan->nama_bahan}\n";
        echo "  - Qty Resep: {$detail->qty_resep} {$detail->satuan_resep}\n";
        echo "  - Qty Konversi (terpakai): {$detail->qty_konversi} {$detail->satuan}\n";
        echo "  - Subtotal: Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n";
        
        // Check stock movement
        $movement = \App\Models\StockMovement::where('item_type', 'material')
            ->where('item_id', $bahan->id)
            ->where('ref_type', 'production')
            ->where('ref_id', $produksi->id)
            ->first();
        
        if ($movement) {
            echo "  - Stock Movement: {$movement->qty} {$movement->unit} (direction: {$movement->direction})\n";
        } else {
            echo "  - Stock Movement: TIDAK ADA!\n";
        }
        echo "\n";
    }
    
    // Check if there are stock layers consumed
    echo "=== STOCK LAYERS CONSUMED ===\n";
    $ayam = \App\Models\BahanBaku::find(2);
    if ($ayam) {
        echo "Ayam Kampung - Stok saat ini: {$ayam->stok} Ekor\n";
        $layers = \App\Models\StockLayer::where('item_type', 'material')
            ->where('item_id', 2)
            ->get();
        
        $totalRemaining = 0;
        foreach ($layers as $layer) {
            echo "  Layer: Qty Remaining = {$layer->remaining_qty}, Satuan = {$layer->satuan}\n";
            $totalRemaining += $layer->remaining_qty;
        }
        echo "Total dari layers: {$totalRemaining}\n";
    }
} else {
    echo "Tidak ada data produksi\n";
}
