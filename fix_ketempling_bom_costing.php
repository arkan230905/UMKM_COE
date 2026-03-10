<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX KETAMPLING BOM COSTING ===" . PHP_EOL;

try {
    // 1. Cari produk ketempling
    $produk = \App\Models\Produk::find(3);
    if (!$produk) {
        echo "Produk ketempling tidak ditemukan!" . PHP_EOL;
        exit;
    }
    
    echo "Produk: {$produk->nama_produk}" . PHP_EOL;
    echo "Biaya Bahan saat ini: Rp " . number_format($produk->biaya_bahan ?? 0, 2, ',', '.') . PHP_EOL;
    
    // 2. Buat BomJobCosting untuk ketempling
    $bomJobCosting = \App\Models\BomJobCosting::firstOrCreate([
        'produk_id' => 3
    ], [
        'jumlah_produk' => 100, // Default 100 unit
        'total_bbb' => $produk->biaya_bahan ?? 0,
        'total_btkl' => 0,
        'total_bahan_pendukung' => 0,
        'total_bop' => 0,
        'total_hpp' => $produk->biaya_bahan ?? 0,
        'hpp_per_unit' => ($produk->biaya_bahan ?? 0) / 100
    ]);
    
    echo "BomJobCosting dibuat/ditemukan: ID {$bomJobCosting->id}" . PHP_EOL;
    
    // 3. Tambah data BTKL default untuk ketempling
    echo PHP_EOL . "Menambahkan data BTKL..." . PHP_EOL;
    
    // Cari BTKL yang tersedia
    $btkls = \App\Models\Btkl::all();
    echo "Jumlah BTKL tersedia: " . $btkls->count() . PHP_EOL;
    
    // Ambil BTKL pertama sebagai default
    $defaultBtkl = $btkls->first();
    if ($defaultBtkl) {
        // Cek apakah sudah ada
        $existingBtkl = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)
            ->where('btkl_id', $defaultBtkl->id)
            ->first();
            
        if (!$existingBtkl) {
            $bomJobBtkl = \App\Models\BomJobBTKL::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'btkl_id' => $defaultBtkl->id,
                'nama_proses' => $defaultBtkl->nama ?? 'Proses Default',
                'durasi_jam' => 1,
                'tarif_per_jam' => $defaultBtkl->tarif_per_jam ?? 5000,
                'subtotal' => $defaultBtkl->tarif_per_jam ?? 5000
            ]);
            
            echo "BTKL ditambahkan: {$defaultBtkl->nama} - Rp " . 
                 number_format($bomJobBtkl->subtotal, 2, ',', '.') . PHP_EOL;
        } else {
            echo "BTKL sudah ada: {$defaultBtkl->nama}" . PHP_EOL;
        }
    }
    
    // 4. Tambah data BOP default untuk ketempling
    echo PHP_EOL . "Menambahkan data BOP..." . PHP_EOL;
    
    // Cari BOP yang tersedia
    $bops = \App\Models\Bop::all();
    echo "Jumlah BOP tersedia: " . $bops->count() . PHP_EOL;
    
    // Ambil BOP pertama sebagai default
    $defaultBop = $bops->first();
    if ($defaultBop) {
        // Cek apakah sudah ada
        $existingBop = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)
            ->where('bop_id', $defaultBop->id)
            ->first();
            
        if (!$existingBop) {
            $bomJobBop = \App\Models\BomJobBOP::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'bop_id' => $defaultBop->id,
                'nama_bop' => $defaultBop->nama ?? 'BOP Default',
                'jumlah' => 1,
                'tarif' => $defaultBop->tarif_per_unit ?? 3000,
                'subtotal' => $defaultBop->tarif_per_unit ?? 3000
            ]);
            
            echo "BOP ditambahkan: {$defaultBop->nama} - Rp " . 
                 number_format($bomJobBop->subtotal, 2, ',', '.') . PHP_EOL;
        } else {
            echo "BOP sudah ada: {$defaultBop->nama}" . PHP_EOL;
        }
    }
    
    // 5. Recalculate semua
    echo PHP_EOL . "Menghitung ulang total..." . PHP_EOL;
    $bomJobCosting->recalculate();
    
    echo "Hasil recalculate:" . PHP_EOL;
    echo "- Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
    echo "- Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    echo "- Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
    echo "- Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
    echo "- HPP per unit: Rp " . number_format($bomJobCosting->hpp_per_unit, 2, ',', '.') . PHP_EOL;
    
    // 6. Update produk
    $produk->refresh();
    echo PHP_EOL . "Data produk setelah update:" . PHP_EOL;
    echo "- Harga BOM: Rp " . number_format($produk->harga_bom ?? 0, 2, ',', '.') . PHP_EOL;
    echo "- Biaya Bahan: Rp " . number_format($produk->biaya_bahan ?? 0, 2, ',', '.') . PHP_EOL;
    echo "- Harga Pokok: Rp " . number_format($produk->harga_pokok ?? 0, 2, ',', '.') . PHP_EOL;
    
    echo PHP_EOL . "✅ Fix completed!" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
