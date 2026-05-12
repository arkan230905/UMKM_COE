<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DATA KETAMPLING SAAT INI ===" . PHP_EOL;

// 1. Cek produk ketempling
$produk = \App\Models\Produk::find(3);
if ($produk) {
    echo "Produk: {$produk->nama_produk}" . PHP_EOL;
    echo "Harga BOM: Rp " . number_format($produk->harga_bom ?? 0, 2, ',', '.') . PHP_EOL;
    echo "Biaya Bahan: Rp " . number_format($produk->biaya_bahan ?? 0, 2, ',', '.') . PHP_EOL;
    echo "Harga Pokok: Rp " . number_format($produk->harga_pokok ?? 0, 2, ',', '.') . PHP_EOL;
} else {
    echo "Produk ketempling tidak ditemukan!" . PHP_EOL;
}

// 2. Cek BomJobCosting
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
if ($bomJobCosting) {
    echo PHP_EOL . "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
    echo "Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
    echo "Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    echo "Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
    echo "Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
    
    // 3. Cek detail BBB
    echo PHP_EOL . "Detail BBB:" . PHP_EOL;
    $bbbDetails = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($bbbDetails as $bbb) {
        $status = $bbb->harga_satuan > 0 ? 'AKTIF' : 'TERHAPUS';
        $namaBahan = $bbb->nama_bahan_terhapus ?? ($bbb->bahanBaku->nama_bahan ?? 'Unknown');
        echo "- {$namaBahan}: Rp " . 
             number_format($bbb->subtotal, 2, ',', '.') . " ({$status})" . PHP_EOL;
    }
    
    // 4. Cek detail BTKL
    echo PHP_EOL . "Detail BTKL:" . PHP_EOL;
    $btklDetails = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($btklDetails as $btkl) {
        echo "- " . ($btkl->btkl->nama ?? 'Unknown') . ": Rp " . 
             number_format($btkl->subtotal, 2, ',', '.') . PHP_EOL;
    }
    
    // 5. Cek detail BOP
    echo PHP_EOL . "Detail BOP:" . PHP_EOL;
    $bopDetails = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
    foreach ($bopDetails as $bop) {
        echo "- " . ($bop->bop->nama ?? 'Unknown') . ": Rp " . 
             number_format($bop->subtotal, 2, ',', '.') . PHP_EOL;
    }
} else {
    echo "BomJobCosting tidak ditemukan!" . PHP_EOL;
}
