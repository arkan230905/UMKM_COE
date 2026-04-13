<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK RIWAYAT STOK SEMUA BAHAN BAKU ===\n\n";

$bahanBakus = \App\Models\BahanBaku::all();

foreach ($bahanBakus as $bahan) {
    echo "📦 {$bahan->nama_bahan} (ID: {$bahan->id})\n";
    echo "   Stok saat ini: {$bahan->stok} KG\n";
    
    // Cek apakah ada stock movements untuk bahan ini
    $stockMovements = \DB::table('stock_movements')
        ->where('item_type', 'material')
        ->where('item_id', $bahan->id)
        ->orderBy('created_at', 'asc')
        ->get();
    
    if ($stockMovements->count() > 0) {
        echo "   📊 Riwayat Stock Movements:\n";
        foreach ($stockMovements as $movement) {
            echo "     - {$movement->created_at}: {$movement->direction} {$movement->qty} {$movement->satuan} (ref: {$movement->ref_type})\n";
        }
    } else {
        echo "   📊 Tidak ada riwayat stock movements\n";
    }
    
    // Cek apakah ada stock layers
    $stockLayers = \DB::table('stock_layers')
        ->where('item_type', 'material')
        ->where('item_id', $bahan->id)
        ->orderBy('created_at', 'asc')
        ->get();
    
    if ($stockLayers->count() > 0) {
        echo "   📋 Riwayat Stock Layers:\n";
        foreach ($stockLayers as $layer) {
            echo "     - {$layer->created_at}: +{$layer->qty} {$layer->satuan} @ Rp{$layer->unit_cost} (remaining: {$layer->remaining_qty})\n";
        }
    } else {
        echo "   📋 Tidak ada riwayat stock layers\n";
    }
    
    // Cek pembelian untuk bahan ini
    $pembelianDetails = \DB::table('pembelian_details')
        ->where('bahan_baku_id', $bahan->id)
        ->get();
    
    if ($pembelianDetails->count() > 0) {
        echo "   🛒 Riwayat Pembelian:\n";
        foreach ($pembelianDetails as $detail) {
            $pembelian = \DB::table('pembelians')->where('id', $detail->pembelian_id)->first();
            $tanggal = $pembelian ? $pembelian->tanggal : 'unknown';
            echo "     - {$tanggal}: {$detail->jumlah} {$detail->satuan} (konversi: " . ($detail->jumlah_satuan_utama ?? 'null') . " KG)\n";
        }
    } else {
        echo "   🛒 Tidak ada riwayat pembelian\n";
    }
    
    echo "\n";
}

echo "=== PERTANYAAN UNTUK USER ===\n";
echo "Berdasarkan data di atas, tolong konfirmasi stok awal yang benar untuk:\n\n";

foreach ($bahanBakus as $bahan) {
    echo "• {$bahan->nama_bahan}: _____ KG\n";
}

echo "\nSetelah Anda berikan informasi stok awal yang benar,\n";
echo "saya akan membuat script untuk mengembalikan semua stok ke nilai yang tepat.\n";