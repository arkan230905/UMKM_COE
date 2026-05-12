<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TAMBAH BBB DEFAULT KETAMPLING ===" . PHP_EOL;

try {
    // 1. Cari BomJobCosting ketempling
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
    if (!$bomJobCosting) {
        echo "BomJobCosting ketempling tidak ditemukan!" . PHP_EOL;
        exit;
    }
    
    echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
    
    // 2. Cari bahan baku yang tersedia
    $bahanBakus = \App\Models\BahanBaku::where('harga_satuan', '>', 0)->get();
    echo "Bahan baku tersedia: " . $bahanBakus->count() . PHP_EOL;
    
    // 3. Tambah bahan baku default untuk ketempling
    // Ambil 3 bahan baku pertama sebagai contoh
    $bahanUntukDitambahkan = $bahanBakus->take(3);
    
    foreach ($bahanUntukDitambahkan as $bahan) {
        // Cek apakah sudah ada
        $existingBBB = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
            ->where('bahan_baku_id', $bahan->id)
            ->first();
            
        if (!$existingBBB) {
            // Tambah dengan jumlah default
            $jumlahDefault = 0.5; // 0.5 kg
            $subtotal = $jumlahDefault * $bahan->harga_satuan;
            
            $bbb = \App\Models\BomJobBBB::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'bahan_baku_id' => $bahan->id,
                'jumlah' => $jumlahDefault,
                'satuan' => $bahan->satuan->nama ?? 'KG',
                'harga_satuan' => $bahan->harga_satuan,
                'subtotal' => $subtotal
            ]);
            
            echo "BBB ditambahkan: {$bahan->nama_bahan} - {$jumlahDefault} {$bbb->satuan} @ Rp " . 
                 number_format($bahan->harga_satuan, 2, ',', '.') . 
                 " = Rp " . number_format($bbb->subtotal, 2, ',', '.') . PHP_EOL;
        } else {
            echo "BBB sudah ada: {$bahan->nama_bahan}" . PHP_EOL;
        }
    }
    
    // 4. Recalculate BomJobCosting
    echo PHP_EOL . "Menghitung ulang BomJobCosting..." . PHP_EOL;
    $bomJobCosting->recalculate();
    
    echo "Hasil recalculate:" . PHP_EOL;
    echo "- Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
    echo "- Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    echo "- Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
    echo "- Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
    
    // 5. Update produk
    $produk = $bomJobCosting->produk;
    $produk->refresh();
    
    echo PHP_EOL . "Data produk setelah update:" . PHP_EOL;
    echo "- Harga BOM: Rp " . number_format($produk->harga_bom ?? 0, 2, ',', '.') . PHP_EOL;
    echo "- Biaya Bahan: Rp " . number_format($produk->biaya_bahan ?? 0, 2, ',', '.') . PHP_EOL;
    echo "- Harga Pokok: Rp " . number_format($produk->harga_pokok ?? 0, 2, ',', '.') . PHP_EOL;
    
    echo PHP_EOL . "✅ BBB default berhasil ditambahkan!" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
