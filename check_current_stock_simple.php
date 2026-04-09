<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== STOK BAHAN BAKU SAAT INI ===\n\n";

$bahanBakus = \App\Models\BahanBaku::all();

foreach ($bahanBakus as $bahan) {
    echo "📦 {$bahan->nama_bahan} (ID: {$bahan->id})\n";
    echo "   Stok saat ini: {$bahan->stok} KG\n";
    
    // Cek apakah ada pembelian
    $totalPembelian = \DB::table('pembelian_details')
        ->where('bahan_baku_id', $bahan->id)
        ->count();
    
    echo "   Jumlah transaksi pembelian: {$totalPembelian}\n";
    
    // Cek stock movements
    $totalMovements = \DB::table('stock_movements')
        ->where('item_type', 'material')
        ->where('item_id', $bahan->id)
        ->count();
    
    echo "   Jumlah stock movements: {$totalMovements}\n";
    echo "\n";
}

echo "=== KONFIRMASI STOK AWAL ===\n\n";
echo "Tolong beritahu stok awal yang benar untuk masing-masing bahan:\n\n";

foreach ($bahanBakus as $bahan) {
    echo "• {$bahan->nama_bahan}: _____ KG\n";
}

echo "\nContoh jawaban:\n";
echo "• Ayam Potong: 50 KG\n";
echo "• Ayam Kampung: 40 KG\n";
echo "• Bebek: 30 KG\n";
echo "\nSetelah Anda berikan informasi ini, saya akan update stok yang benar.\n";