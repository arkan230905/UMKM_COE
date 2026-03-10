<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX BTKL & BOP SEMUA PRODUK ===" . PHP_EOL;

try {
    $targetBTKL = 2040;
    $targetBOP = 3190;
    
    echo "Target:" . PHP_EOL;
    echo "- BTKL: Rp " . number_format($targetBTKL, 2, ',', '.') . PHP_EOL;
    echo "- BOP: Rp " . number_format($targetBOP, 2, ',', '.') . PHP_EOL . PHP_EOL;
    
    // 1. Hapus semua data BTKL dan BOP yang ada
    echo "Menghapus data BTKL dan BOP yang lama..." . PHP_EOL;
    
    $bomJobCostings = \App\Models\BomJobCosting::all();
    foreach ($bomJobCostings as $bom) {
        // Hapus BTKL lama
        \App\Models\BomJobBTKL::where('bom_job_costing_id', $bom->id)->delete();
        // Hapus BOP lama
        \App\Models\BomJobBOP::where('bom_job_costing_id', $bom->id)->delete();
        
        echo "- Dihapus untuk {$bom->produk->nama_produk}" . PHP_EOL;
    }
    
    // 2. Cari master BTKL dan BOP yang akan digunakan
    echo PHP_EOL . "Mencari master data..." . PHP_EOL;
    
    $btkls = \App\Models\Btkl::all();
    $bops = \App\Models\Bop::all();
    
    if ($btkls->isEmpty()) {
        echo "❌ Tidak ada master BTKL! Membuat dummy..." . PHP_EOL;
        // Buat dummy BTKL
        $btkl = \App\Models\Btkl::create([
            'nama' => 'BTKL Standard',
            'tarif_per_jam' => $targetBTKL
        ]);
        $btkls = collect([$btkl]);
    }
    
    if ($bops->isEmpty()) {
        echo "❌ Tidak ada master BOP! Membuat dummy..." . PHP_EOL;
        // Buat dummy BOP
        $bop = \App\Models\Bop::create([
            'nama' => 'BOP Standard',
            'tarif_per_unit' => $targetBOP
        ]);
        $bops = collect([$bop]);
    }
    
    $selectedBTKL = $btkls->first();
    $selectedBOP = $bops->first();
    
    echo "BTKL yang digunakan: {$selectedBTKL->nama}" . PHP_EOL;
    echo "BOP yang digunakan: {$selectedBOP->nama}" . PHP_EOL;
    
    // 3. Tambahkan BTKL dan BOP ke semua produk
    echo PHP_EOL . "Menambahkan BTKL dan BOP ke semua produk..." . PHP_EOL;
    
    foreach ($bomJobCostings as $bom) {
        // Tambah BTKL
        \App\Models\BomJobBTKL::create([
            'bom_job_costing_id' => $bom->id,
            'btkl_id' => $selectedBTKL->id,
            'nama_proses' => $selectedBTKL->nama ?? 'Proses Standard',
            'durasi_jam' => 1,
            'tarif_per_jam' => $targetBTKL,
            'subtotal' => $targetBTKL
        ]);
        
        // Tambah BOP
        \App\Models\BomJobBOP::create([
            'bom_job_costing_id' => $bom->id,
            'bop_id' => $selectedBOP->id,
            'nama_bop' => $selectedBOP->nama ?? 'BOP Standard',
            'jumlah' => 1,
            'tarif' => $targetBOP,
            'subtotal' => $targetBOP
        ]);
        
        echo "- Ditambahkan untuk {$bom->produk->nama_produk}" . PHP_EOL;
    }
    
    // 4. Recalculate semua BomJobCosting
    echo PHP_EOL . "Menghitung ulang semua BomJobCosting..." . PHP_EOL;
    
    foreach ($bomJobCostings as $bom) {
        $bom->recalculate();
        
        echo "{$bom->produk->nama_produk}:" . PHP_EOL;
        echo "  - Total BBB: Rp " . number_format($bom->total_bbb, 2, ',', '.') . PHP_EOL;
        echo "  - Total BTKL: Rp " . number_format($bom->total_btkl, 2, ',', '.') . PHP_EOL;
        echo "  - Total BOP: Rp " . number_format($bom->total_bop, 2, ',', '.') . PHP_EOL;
        echo "  - Total HPP: Rp " . number_format($bom->total_hpp, 2, ',', '.') . PHP_EOL;
        echo PHP_EOL;
    }
    
    // 5. Update produk
    echo "Mengupdate data produk..." . PHP_EOL;
    
    foreach ($bomJobCostings as $bom) {
        $produk = $bom->produk;
        $produk->refresh();
        
        echo "{$produk->nama_produk}:" . PHP_EOL;
        echo "  - Harga BOM: Rp " . number_format($produk->harga_bom ?? 0, 2, ',', '.') . PHP_EOL;
        echo "  - Harga Pokok: Rp " . number_format($produk->harga_pokok ?? 0, 2, ',', '.') . PHP_EOL;
        echo PHP_EOL;
    }
    
    echo "✅ Semua produk telah diupdate dengan BTKL Rp 2.040 dan BOP Rp 3.190!" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
