<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST HAPUS BAHAN KETAMPLING ===" . PHP_EOL;

try {
    // 1. Cek data sebelum hapus
    echo "DATA SEBELUM HAPUS:" . PHP_EOL;
    $produk = \App\Models\Produk::find(3);
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
    
    if ($produk && $bomJobCosting) {
        echo "- Harga BOM: Rp " . number_format($produk->harga_bom ?? 0, 2, ',', '.') . PHP_EOL;
        echo "- Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
        echo "- Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
        echo "- Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
        echo "- Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
        
        // 2. Cari bahan baku yang akan dihapus (Ayam Potong)
        $ayamPotong = \App\Models\BahanBaku::where('nama_bahan', 'Ayam Potong')->first();
        if ($ayamPotong) {
            echo PHP_EOL . "Menghapus bahan: {$ayamPotong->nama_bahan}" . PHP_EOL;
            
            // 3. Hapus bahan baku (ini akan trigger observer)
            $ayamPotong->delete();
            
            echo PHP_EOL . "DATA SETELAH HAPUS:" . PHP_EOL;
            
            // Refresh data
            $produk->refresh();
            $bomJobCosting->refresh();
            
            echo "- Harga BOM: Rp " . number_format($produk->harga_bom ?? 0, 2, ',', '.') . PHP_EOL;
            echo "- Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
            echo "- Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
            echo "- Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
            echo "- Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
            
            // 4. Cek detail BBB
            echo PHP_EOL . "Detail BBB setelah hapus:" . PHP_EOL;
            $bbbDetails = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
            foreach ($bbbDetails as $bbb) {
                $status = $bbb->harga_satuan > 0 ? 'AKTIF' : 'TERHAPUS';
                $namaBahan = $bbb->nama_bahan_terhapus ?? ($bbb->bahanBaku->nama_bahan ?? 'Unknown');
                echo "- {$namaBahan}: Rp " . 
                     number_format($bbb->subtotal, 2, ',', '.') . " ({$status})" . PHP_EOL;
            }
            
            // 5. Cek detail BTKL
            echo PHP_EOL . "Detail BTKL:" . PHP_EOL;
            $btklDetails = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
            foreach ($btklDetails as $btkl) {
                echo "- " . ($btkl->btkl->nama ?? 'Unknown') . ": Rp " . 
                     number_format($btkl->subtotal, 2, ',', '.') . PHP_EOL;
            }
            
            // 6. Cek detail BOP
            echo PHP_EOL . "Detail BOP:" . PHP_EOL;
            $bopDetails = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->get();
            foreach ($bopDetails as $bop) {
                echo "- " . ($bop->bop->nama ?? 'Unknown') . ": Rp " . 
                     number_format($bop->subtotal, 2, ',', '.') . PHP_EOL;
            }
            
            echo PHP_EOL . "✅ Test selesai!" . PHP_EOL;
            
            // 7. Analisis hasil
            echo PHP_EOL . "ANALISIS:" . PHP_EOL;
            if ($bomJobCosting->total_btkl > 0 && $bomJobCosting->total_bop > 0) {
                echo "✅ BTKL dan BOP TIDAK terhapus - Masalah SOLVED!" . PHP_EOL;
            } else {
                echo "❌ BTKL dan/atau BOP terhapus - Masalah masih ada!" . PHP_EOL;
            }
            
        } else {
            echo "Bahan Ayam Potong tidak ditemukan!" . PHP_EOL;
        }
    } else {
        echo "Produk atau BomJobCosting tidak ditemukan!" . PHP_EOL;
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
