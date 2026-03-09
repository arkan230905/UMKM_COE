<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DATA BBB KETAMPLING ===" . PHP_EOL;

// Cek BomJobCosting ketempling
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
if ($bomJobCosting) {
    echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
    
    // Cek semua detail BBB
    echo PHP_EOL . "Detail BBB:" . PHP_EOL;
    $bbbDetails = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
    
    if ($bbbDetails->isEmpty()) {
        echo "Tidak ada data BBB sama sekali!" . PHP_EOL;
        
        // Cek apakah ada bahan baku yang bisa ditambahkan
        echo PHP_EOL . "Mencari bahan baku yang tersedia..." . PHP_EOL;
        $bahanBakus = \App\Models\BahanBaku::limit(5)->get();
        foreach ($bahanBakus as $bahan) {
            echo "- {$bahan->nama_bahan}: Rp " . number_format($bahan->harga_satuan, 2, ',', '.') . "/{$bahan->satuan->nama}" . PHP_EOL;
        }
    } else {
        foreach ($bbbDetails as $bbb) {
            $status = $bbb->harga_satuan > 0 ? 'AKTIF' : 'TERHAPUS';
            $namaBahan = $bbb->nama_bahan_terhapus ?? ($bbb->bahanBaku->nama_bahan ?? 'Unknown');
            echo "- {$namaBahan}: {$bbb->jumlah} {$bbb->satuan} @ Rp " . 
                 number_format($bbb->harga_satuan, 2, ',', '.') . 
                 " = Rp " . number_format($bbb->subtotal, 2, ',', '.') . " ({$status})" . PHP_EOL;
        }
    }
} else {
    echo "BomJobCosting tidak ditemukan!" . PHP_EOL;
}
