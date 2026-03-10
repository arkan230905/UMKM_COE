<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== HAPUS BAHAN BAKU KETAMPLING ===" . PHP_EOL;

try {
    // 1. Cari bahan baku "Bahan Baku Standard"
    $bahanBaku = \App\Models\BahanBaku::where('nama_bahan', 'Bahan Baku Standard')->first();
    if (!$bahanBaku) {
        echo "Bahan Baku Standard tidak ditemukan!" . PHP_EOL;
        exit;
    }
    
    echo "Bahan baku yang akan dihapus: {$bahanBaku->nama_bahan}" . PHP_EOL;
    echo "ID: {$bahanBaku->id}" . PHP_EOL;
    echo "Harga: Rp " . number_format($bahanBaku->harga_satuan, 2, ',', '.') . PHP_EOL;
    echo PHP_EOL;
    
    // 2. Cek data sebelum hapus
    echo "DATA SEBELUM HAPUS:" . PHP_EOL;
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
    if ($bomJobCosting) {
        $bbbCount = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
            ->where('harga_satuan', '>', 0)
            ->count();
        $bbbTotal = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
            ->sum('subtotal');
        
        echo "- BBB: {$bbbCount} item, Total: Rp " . number_format($bbbTotal, 2, ',', '.') . PHP_EOL;
        echo "- Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
        echo "- Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
        echo "- Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
    }
    
    echo PHP_EOL;
    
    // 3. Hapus bahan baku (ini akan trigger observer)
    echo "Menghapus bahan baku..." . PHP_EOL;
    $bahanBaku->delete();
    echo "✅ Bahan baku dihapus" . PHP_EOL;
    
    echo PHP_EOL . "DATA SETELAH HAPUS:" . PHP_EOL;
    
    // 4. Cek data setelah hapus
    if ($bomJobCosting) {
        $bomJobCosting->refresh();
        
        $bbbCount = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
            ->where('harga_satuan', '>', 0)
            ->count();
        $bbbTotal = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
            ->sum('subtotal');
        
        $btklCount = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->count();
        $bopCount = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->count();
        
        echo "- BBB: {$bbbCount} item, Total: Rp " . number_format($bbbTotal, 2, ',', '.') . PHP_EOL;
        echo "- BTKL: {$btklCount} item, Total: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
        echo "- BOP: {$bopCount} item, Total: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
        echo "- Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
        
        echo PHP_EOL;
        
        // 5. Logic pengecekan status
        echo "LOGIC PENGECEKAN STATUS:" . PHP_EOL;
        
        $missingComponents = [];
        $isComplete = true;
        
        if ($bbbCount == 0 || $bbbTotal == 0) {
            $missingComponents[] = 'Biaya Bahan';
            $isComplete = false;
        }
        
        if ($btklCount == 0 || $bomJobCosting->total_btkl == 0) {
            $missingComponents[] = 'Biaya Tenaga Kerja Langsung';
            $isComplete = false;
        }
        
        if ($bopCount == 0 || $bomJobCosting->total_bop == 0) {
            $missingComponents[] = 'Biaya Overhead Pabrik';
            $isComplete = false;
        }
        
        if ($isComplete) {
            echo "✅ Status: Produk Sudah Memiliki Harga Pokok Produksi" . PHP_EOL;
            echo "   Semua komponen lengkap" . PHP_EOL;
        } else {
            echo "❌ Status: Harga Pokok Produksi Belum Lengkap" . PHP_EOL;
            echo "   Kolom kosong: " . implode(', ', $missingComponents) . PHP_EOL;
        }
        
        // 6. Cek detail BBB yang tersisa
        echo PHP_EOL . "DETAIL BBB SETELAH HAPUS:" . PHP_EOL;
        $allBBB = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)->get();
        foreach ($allBBB as $bbb) {
            $status = $bbb->harga_satuan > 0 ? 'AKTIF' : 'TERHAPUS';
            $nama = $bbb->nama_bahan_terhapus ?? ($bbb->bahanBaku->nama_bahan ?? 'Unknown');
            echo "- {$nama}: Rp " . number_format($bbb->subtotal, 2, ',', '.') . " ({$status})" . PHP_EOL;
        }
        
        // 7. Update produk
        $produk = $bomJobCosting->produk;
        $produk->refresh();
        
        echo PHP_EOL . "DATA PRODUK:" . PHP_EOL;
        echo "- Harga BOM: Rp " . number_format($produk->harga_bom, 2, ',', '.') . PHP_EOL;
        echo "- Harga Pokok: Rp " . number_format($produk->harga_pokok, 2, ',', '.') . PHP_EOL;
        echo "- Biaya Bahan: Rp " . number_format($produk->biaya_bahan, 2, ',', '.') . PHP_EOL;
    }
    
    echo PHP_EOL . "✅ Selesai! Sekarang cek status di halaman web." . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
