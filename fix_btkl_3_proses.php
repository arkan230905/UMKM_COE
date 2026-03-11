<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX BTKL 3 PROSES ===" . PHP_EOL;

try {
    // 1. Cek master data BTKL yang tersedia
    echo "Master data BTKL:" . PHP_EOL;
    $masterBTKL = \App\Models\Btkl::all();
    
    if ($masterBTKL->count() < 3) {
        echo "Master BTKL kurang dari 3, membuat dummy data..." . PHP_EOL;
        
        // Buat 3 master BTKL
        $prosesBTKL = [
            ['kode_proses' => 'PRO-001', 'nama_btkl' => 'Membumbui', 'jabatan_id' => 11, 'tarif' => 48000, 'kapasitas' => 200, 'deskripsi' => 'Proses membumbui'],
            ['kode_proses' => 'PRO-002', 'nama_btkl' => 'Menggoreng', 'jabatan_id' => 12, 'tarif' => 45000, 'kapasitas' => 50, 'deskripsi' => 'Proses menggoreng'],
            ['kode_proses' => 'PRO-003', 'nama_btkl' => 'Packing', 'jabatan_id' => 13, 'tarif' => 45000, 'kapasitas' => 50, 'deskripsi' => 'Proses packing']
        ];
        
        foreach ($prosesBTKL as $index => $proses) {
            $btkl = \App\Models\Btkl::updateOrCreate(
                ['kode_proses' => $proses['kode_proses']],
                [
                    'nama_btkl' => $proses['nama_btkl'],
                    'jabatan_id' => $proses['jabatan_id'],
                    'tarif_per_jam' => $proses['tarif'],
                    'kapasitas_per_jam' => $proses['kapasitas'],
                    'satuan' => 'Jam',
                    'deskripsi_proses' => $proses['deskripsi'],
                    'is_active' => 1
                ]
            );
            
            echo "- {$proses['kode_proses']}: {$proses['nama_btkl']} @ Rp " . 
                 number_format($proses['tarif'], 2, ',', '.') . "/jam" . PHP_EOL;
        }
    }
    
    // 2. Ambil master BTKL
    $masterBTKL = \App\Models\Btkl::all();
    echo PHP_EOL . "Master BTKL tersedia: " . $masterBTKL->count() . PHP_EOL;
    
    // 3. Update semua produk agar memiliki 3 proses BTKL
    echo PHP_EOL . "Mengupdate semua produk dengan 3 proses BTKL..." . PHP_EOL;
    
    $produkList = \App\Models\Produk::all();
    foreach ($produkList as $produk) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            // Hapus BTKL yang lama
            \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->delete();
            
            echo "Produk: {$produk->nama_produk}" . PHP_EOL;
            $totalBTKL = 0;
            
            // Tambah 3 proses BTKL
            foreach ($masterBTKL->take(3) as $index => $btkl) {
                $biayaPerUnit = $btkl->kapasitas_per_jam > 0 ? 
                    $btkl->tarif_per_jam / $btkl->kapasitas_per_jam : 0;
                
                \App\Models\BomJobBTKL::create([
                    'bom_job_costing_id' => $bomJobCosting->id,
                    'btkl_id' => $btkl->id,
                    'nama_proses' => $btkl->nama_btkl,
                    'durasi_jam' => 1,
                    'tarif_per_jam' => $btkl->tarif_per_jam,
                    'kapasitas_per_jam' => $btkl->kapasitas_per_jam,
                    'subtotal' => $biayaPerUnit
                ]);
                
                $totalBTKL += $biayaPerUnit;
                
                echo "  " . ($index + 1) . ". {$btkl->nama_btkl}: Rp " . 
                     number_format($biayaPerUnit, 2, ',', '.') . "/unit" . PHP_EOL;
            }
            
            echo "  Total BTKL: Rp " . number_format($totalBTKL, 2, ',', '.') . PHP_EOL;
            
            // Recalculate
            $bomJobCosting->recalculate();
            echo "  Setelah recalculate - Total BTKL: Rp " . 
                 number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
            echo PHP_EOL;
        }
    }
    
    // 4. Verifikasi hasil
    echo "VERIFIKASI HASIL:" . PHP_EOL;
    
    foreach ($produkList as $produk) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
        
        if ($bomJobCosting) {
            $btklDetails = \App\Models\BomJobBTKL::with('btkl')
                ->where('bom_job_costing_id', $bomJobCosting->id)
                ->get();
            
            echo $produk->nama_produk . " (" . $btklDetails->count() . " proses):" . PHP_EOL;
            
            $totalBiayaPerUnit = 0;
            foreach ($btklDetails as $btkl) {
                $biayaPerUnit = $btkl->kapasitas_per_jam > 0 ? 
                    $btkl->tarif_per_jam / $btkl->kapasitas_per_jam : 0;
                $totalBiayaPerUnit += $biayaPerUnit;
                
                echo "  - {$btkl->nama_proses}: Rp " . 
                     number_format($biayaPerUnit, 2, ',', '.') . "/unit" . PHP_EOL;
            }
            
            echo "  Total: Rp " . number_format($totalBiayaPerUnit, 2, ',', '.') . PHP_EOL;
            echo "  Di BomJobCosting: Rp " . 
                 number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
            echo PHP_EOL;
        }
    }
    
    echo "✅ Semua produk telah diupdate dengan 3 proses BTKL!" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
