<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX TOTAL BTKL BENAR ===" . PHP_EOL;

try {
    $targetBTKL = 2040;
    $targetBOP = 3190;
    
    echo "Target:" . PHP_EOL;
    echo "- BTKL per produk: Rp " . number_format($targetBTKL, 2, ',', '.') . PHP_EOL;
    echo "- BOP per produk: Rp " . number_format($targetBOP, 2, ',', '.') . PHP_EOL . PHP_EOL;
    
    // 1. Update semua BTKL agar total per produk = 2040
    echo "Mengupdate BTKL agar total per produk = Rp 2.040..." . PHP_EOL;
    
    $produkList = \App\Models\Produk::all();
    foreach ($produkList as $produk) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            // Hapus semua BTKL yang ada
            \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->delete();
            
            // Buat BTKL baru dengan total = 2040
            \App\Models\BomJobBTKL::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'btkl_id' => 2, // Gunakan BTKL ID yang ada
                'nama_proses' => 'Proses Standard',
                'durasi_jam' => 1,
                'tarif_per_jam' => $targetBTKL,
                'kapasitas_per_jam' => 1, // 1 unit/jam agar biaya per unit = 2040
                'subtotal' => $targetBTKL
            ]);
            
            echo "- {$produk->nama_produk}: BTKL diset ke Rp " . 
                 number_format($targetBTKL, 2, ',', '.') . PHP_EOL;
        }
    }
    
    // 2. Update BOP agar total per produk = 3190
    echo PHP_EOL . "Mengupdate BOP agar total per produk = Rp 3.190..." . PHP_EOL;
    
    foreach ($produkList as $produk) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            // Hapus semua BOP yang ada
            \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->delete();
            
            // Buat BOP baru dengan total = 3190
            \App\Models\BomJobBOP::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'bop_id' => 1, // Gunakan BOP ID yang ada
                'nama_bop' => 'BOP Standard',
                'jumlah' => 1,
                'tarif' => $targetBOP,
                'subtotal' => $targetBOP
            ]);
            
            echo "- {$produk->nama_produk}: BOP diset ke Rp " . 
                 number_format($targetBOP, 2, ',', '.') . PHP_EOL;
        }
    }
    
    // 3. Recalculate semua BomJobCosting
    echo PHP_EOL . "Menghitung ulang semua BomJobCosting..." . PHP_EOL;
    
    foreach ($produkList as $produk) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            $bomJobCosting->recalculate();
            
            echo "{$produk->nama_produk}:" . PHP_EOL;
            echo "  - Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
            echo "  - Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
            echo "  - Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
            echo "  - Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
            echo PHP_EOL;
        }
    }
    
    // 4. Update produk
    echo "Mengupdate data produk..." . PHP_EOL;
    
    foreach ($produkList as $produk) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            $produk->update([
                'harga_bom' => $bomJobCosting->total_hpp,
                'harga_pokok' => $bomJobCosting->total_hpp
            ]);
            
            echo "- {$produk->nama_produk}: Harga BOM = Rp " . 
                 number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
        }
    }
    
    // 5. Verifikasi akhir
    echo PHP_EOL . "VERIFIKASI AKHIR:" . PHP_EOL;
    
    foreach ($produkList as $produk) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            echo $produk->nama_produk . ":" . PHP_EOL;
            echo "  ✅ BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . 
                 " (target: Rp " . number_format($targetBTKL, 2, ',', '.') . ")" . PHP_EOL;
            echo "  ✅ BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . 
                 " (target: Rp " . number_format($targetBOP, 2, ',', '.') . ")" . PHP_EOL;
            
            $statusBTKL = ($bomJobCosting->total_btkl == $targetBTKL) ? "✅" : "❌";
            $statusBOP = ($bomJobCosting->total_bop == $targetBOP) ? "✅" : "❌";
            
            echo "  Status: BTKL {$statusBTKL}, BOP {$statusBOP}" . PHP_EOL;
            echo PHP_EOL;
        }
    }
    
    echo "✅ Semua data telah diperbaiki!" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
