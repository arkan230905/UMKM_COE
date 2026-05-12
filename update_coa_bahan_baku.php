<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== UPDATE COA BAHAN BAKU ===" . PHP_EOL;

try {
    // 1. Cek COA yang tersedia
    echo "CEK COA YANG TERSEDIA:" . PHP_EOL;
    $coas = \App\Models\Coa::where('is_akun_header', '!=', 1)
        ->orderBy('kode_akun')
        ->get();
    
    $coaMap = [];
    foreach ($coas as $coa) {
        $coaMap[$coa->kode_akun] = $coa;
        echo "- {$coa->kode_akun}: {$coa->nama_akun} ({$coa->tipe_akun})" . PHP_EOL;
    }
    
    // 2. Cari COA yang relevan untuk pembelian dan persediaan
    echo PHP_EOL . "MENCARI COA YANG RELEVAN:" . PHP_EOL;
    
    // COA untuk pembelian (biasanya akun pembelian/utang usaha)
    $coaPembelian = \App\Models\Coa::where('is_akun_header', '!=', 1)
        ->where(function($query) {
            $query->where('nama_akun', 'like', '%pembelian%')
                  ->orWhere('nama_akun', 'like', '%utang%')
                  ->orWhere('nama_akun', 'like', '%persediaan%')
                  ->orWhere('kode_akun', 'like', '15%'); // Biasanya akun persediaan mulai 15xx
        })
        ->first();
    
    if (!$coaPembelian) {
        // Fallback ke akun persediaan umum
        $coaPembelian = \App\Models\Coa::where('kode_akun', '1510')->first(); // Persediaan Barang Dagang
    }
    
    // COA untuk persediaan
    $coaPersediaan = $coaPembelian; // Sementara gunakan yang sama
    
    // COA untuk HPP (Harga Pokok Penjualan)
    $coaHpp = \App\Models\Coa::where('is_akun_header', '!=', 1)
        ->where(function($query) {
            $query->where('nama_akun', 'like', '%hpp%')
                  ->orWhere('nama_akun', 'like', '%harga pokok%')
                  ->orWhere('kode_akun', 'like', '16%'); // Biasanya akun HPP mulai 16xx
        })
        ->first();
    
    if (!$coaHpp) {
        // Fallback ke akun HPP umum
        $coaHpp = \App\Models\Coa::where('kode_akun', '1610')->first(); // HPP
    }
    
    echo "COA Pembelian: " . ($coaPembelian ? "{$coaPembelian->kode_akun} - {$coaPembelian->nama_akun}" : "NULL") . PHP_EOL;
    echo "COA Persediaan: " . ($coaPersediaan ? "{$coaPersediaan->kode_akun} - {$coaPersediaan->nama_akun}" : "NULL") . PHP_EOL;
    echo "COA HPP: " . ($coaHpp ? "{$coaHpp->kode_akun} - {$coaHpp->nama_akun}" : "NULL") . PHP_EOL;
    
    // 3. Update semua bahan baku yang belum memiliki COA
    echo PHP_EOL . "UPDATE BAHAN BAKU:" . PHP_EOL;
    
    $bahanBakus = \App\Models\BahanBaku::all();
    $updatedCount = 0;
    
    foreach ($bahanBakus as $bahan) {
        $needsUpdate = false;
        $updateData = [];
        
        if (!$bahan->coa_pembelian_id && $coaPembelian) {
            $updateData['coa_pembelian_id'] = $coaPembelian->id;
            $needsUpdate = true;
        }
        
        if (!$bahan->coa_persediaan_id && $coaPersediaan) {
            $updateData['coa_persediaan_id'] = $coaPersediaan->id;
            $needsUpdate = true;
        }
        
        if (!$bahan->coa_hpp_id && $coaHpp) {
            $updateData['coa_hpp_id'] = $coaHpp->id;
            $needsUpdate = true;
        }
        
        if ($needsUpdate) {
            $bahan->update($updateData);
            $updatedCount++;
            
            echo "- {$bahan->nama_bahan}:" . PHP_EOL;
            if (isset($updateData['coa_pembelian_id'])) {
                echo "  COA Pembelian: {$coaPembelian->kode_akun}" . PHP_EOL;
            }
            if (isset($updateData['coa_persediaan_id'])) {
                echo "  COA Persediaan: {$coaPersediaan->kode_akun}" . PHP_EOL;
            }
            if (isset($updateData['coa_hpp_id'])) {
                echo "  COA HPP: {$coaHpp->kode_akun}" . PHP_EOL;
            }
        }
    }
    
    echo PHP_EOL . "Total bahan baku diupdate: {$updatedCount}" . PHP_EOL;
    
    // 4. Verifikasi
    echo PHP_EOL . "VERIFIKASI:" . PHP_EOL;
    
    $bahanBakus = \App\Models\BahanBaku::with(['coaPembelian', 'coaPersediaan', 'coaHpp'])->get();
    
    foreach ($bahanBakus as $bahan) {
        echo "- {$bahan->nama_bahan}:" . PHP_EOL;
        echo "  Pembelian: " . ($bahan->coaPembelian ? $bahan->coaPembelian->kode_akun : 'NULL') . PHP_EOL;
        echo "  Persediaan: " . ($bahan->coaPersediaan ? $bahan->coaPersediaan->kode_akun : 'NULL') . PHP_EOL;
        echo "  HPP: " . ($bahan->coaHpp ? $bahan->coaHpp->kode_akun : 'NULL') . PHP_EOL;
    }
    
    echo PHP_EOL . "✅ Update COA selesai!" . PHP_EOL;
    echo "📝 Sekarang coba lakukan pembelian lagi." . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
