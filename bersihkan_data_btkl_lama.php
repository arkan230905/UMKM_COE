<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== BERSIHKAN DATA BTKL LAMA ===" . PHP_EOL;

try {
    // 1. Hapus data BTKL yang tidak terhubung ke BomJobCosting
    echo "Menghapus data BTKL yang tidak terhubung..." . PHP_EOL;
    
    $allBTKL = \App\Models\BomJobBTKL::all();
    $deletedCount = 0;
    
    foreach ($allBTKL as $btkl) {
        // Cek apakah terhubung ke BomJobCosting yang valid
        $bomJobCosting = \App\Models\BomJobCosting::find($btkl->bom_job_costing_id);
        
        if (!$bomJobCosting) {
            echo "Menghapus BTKL ID {$btkl->id} (tidak terhubung ke BomJobCosting)" . PHP_EOL;
            $btkl->delete();
            $deletedCount++;
        }
    }
    
    echo "Total yang dihapus: {$deletedCount}" . PHP_EOL . PHP_EOL;
    
    // 2. Hapus data BTKL duplikat untuk setiap produk
    echo "Menghapus data BTKL duplikat..." . PHP_EOL;
    
    $produkList = \App\Models\Produk::all();
    foreach ($produkList as $produk) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            $btklDetails = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
            
            if ($btklDetails->count() > 1) {
                echo "Produk {$produk->nama_produk} memiliki {$btklDetails->count()} BTKL, menyisakan 1..." . PHP_EOL;
                
                // Keep the latest one, delete the rest
                $latestBTKL = $btklDetails->last();
                $toDelete = $btklDetails->except($latestBTKL->id);
                
                foreach ($toDelete as $deleteBTKL) {
                    echo "  Menghapus BTKL ID {$deleteBTKL->id}: {$deleteBTKL->nama_proses}" . PHP_EOL;
                    $deleteBTKL->delete();
                }
            }
        }
    }
    
    // 3. Update data BTKL yang tersisa agar sesuai target
    echo PHP_EOL . "Mengupdate data BTKL yang tersisa..." . PHP_EOL;
    
    $targetBTKL = 2040;
    $targetBOP = 3190;
    
    foreach ($produkList as $produk) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            $btklDetails = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->get();
            
            foreach ($btklDetails as $btkl) {
                // Update tarif agar total per unit menjadi 2040
                $btkl->update([
                    'tarif_per_jam' => $targetBTKL,
                    'subtotal' => $targetBTKL,
                    'kapasitas_per_jam' => 200 // Agar biaya per unit = 10.20
                ]);
                
                echo "Updated BTKL untuk {$produk->nama_produk}: Rp " . 
                     number_format($targetBTKL, 2, ',', '.') . "/jam" . PHP_EOL;
            }
            
            // Recalculate
            $bomJobCosting->recalculate();
            
            echo "  Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
            echo "  Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
        }
    }
    
    // 4. Verifikasi hasil
    echo PHP_EOL . "VERIFIKASI HASIL:" . PHP_EOL;
    
    $finalBTKL = \App\Models\BomJobBTKL::with(['bomJobCosting.produk'])->get();
    echo "Total BTKL di sistem: " . $finalBTKL->count() . PHP_EOL . PHP_EOL;
    
    foreach ($finalBTKL as $btkl) {
        echo "Produk: " . ($btkl->bomJobCosting->produk->nama_produk ?? 'Unknown') . PHP_EOL;
        echo "  Nama Proses: {$btkl->nama_proses}" . PHP_EOL;
        echo "  Tarif: Rp " . number_format($btkl->tarif_per_jam, 2, ',', '.') . "/jam" . PHP_EOL;
        echo "  Subtotal: Rp " . number_format($btkl->subtotal, 2, ',', '.') . PHP_EOL;
        echo PHP_EOL;
    }
    
    echo "✅ Data BTKL telah dibersihkan dan disesuaikan!" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
