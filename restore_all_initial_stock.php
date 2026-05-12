<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== MENGEMBALIKAN SEMUA STOK AWAL BAHAN BAKU ===\n\n";

// Data stok awal yang benar (sesuai permintaan user)
$stokAwalBenar = [
    1 => 50,  // Ayam Potong = 50 KG (sudah benar)
    2 => 40,  // Ayam Kampung = 40 ekor
    3 => 50,  // Bebek = 50 ekor
];

echo "📦 MENGUPDATE STOK AWAL:\n";

foreach ($stokAwalBenar as $bahanBakuId => $stokAwal) {
    $bahanBaku = \App\Models\BahanBaku::find($bahanBakuId);
    
    if ($bahanBaku) {
        $stokSekarang = $bahanBaku->stok;
        
        echo "• {$bahanBaku->nama_bahan} (ID: {$bahanBakuId})\n";
        echo "  Stok sekarang: {$stokSekarang}\n";
        echo "  Stok awal yang benar: {$stokAwal}\n";
        
        if (abs($stokSekarang - $stokAwal) > 0.0001) {
            echo "  ❌ PERLU DIPERBAIKI - Mengupdate dari {$stokSekarang} ke {$stokAwal}\n";
            
            $bahanBaku->stok = $stokAwal;
            $bahanBaku->save();
            
            echo "  ✅ BERHASIL DIUPDATE\n";
        } else {
            echo "  ✅ SUDAH BENAR\n";
        }
        echo "\n";
    }
}

echo "=== HASIL AKHIR ===\n";
$bahanBakus = \App\Models\BahanBaku::select('id', 'nama_bahan', 'stok')->get();
foreach ($bahanBakus as $bahan) {
    $satuan = '';
    if ($bahan->id == 1) $satuan = 'KG';      // Ayam Potong
    if ($bahan->id == 2) $satuan = 'ekor';    // Ayam Kampung  
    if ($bahan->id == 3) $satuan = 'ekor';    // Bebek
    
    echo "✅ {$bahan->nama_bahan}: {$bahan->stok} {$satuan}\n";
}

echo "\n🎉 SEMUA STOK AWAL SUDAH DIKEMBALIKAN KE NILAI YANG BENAR!\n";
echo "\nStok awal sekarang:\n";
echo "• Ayam Potong: 50 KG\n";
echo "• Ayam Kampung: 40 ekor\n";  
echo "• Bebek: 50 ekor\n";
echo "\n✅ Siap untuk digunakan!\n";