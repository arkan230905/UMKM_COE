<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TAMBAH BAHAN BAKU DEFAULT ===" . PHP_EOL;

try {
    // 1. Cek apakah ada bahan baku
    $bahanBakus = \App\Models\BahanBaku::all();
    echo "Total bahan baku di sistem: " . $bahanBakus->count() . PHP_EOL;
    
    if ($bahanBakus->isEmpty()) {
        echo "Tidak ada bahan baku sama sekali, membuat dummy..." . PHP_EOL;
        
        // Buat bahan baku dummy
        $satuan = \App\Models\Satuan::first();
        $satuanId = $satuan ? $satuan->id : 1;
        
        $newBahan = \App\Models\BahanBaku::create([
            'nama_bahan' => 'Bahan Baku Standard',
            'kode_bahan' => 'BB-001',
            'satuan_id' => $satuanId,
            'harga_satuan' => 20000,
            'stok' => 100,
            'stok_minimum' => 10
        ]);
        
        echo "Bahan baku dummy dibuat: {$newBahan->nama_bahan}" . PHP_EOL;
        $bahanBakus = collect([$newBahan]);
    }
    
    // 2. Tambahkan BBB ke ketempling
    echo PHP_EOL . "Menambahkan BBB ke ketempling..." . PHP_EOL;
    
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
    if (!$bomJobCosting) {
        echo "BomJobCosting ketempling tidak ditemukan!" . PHP_EOL;
        exit;
    }
    
    // Ambil bahan baku pertama yang tersedia
    $bahanBaku = $bahanBakus->first();
    
    // Cek apakah sudah ada BBB untuk ketempling
    $existingBBB = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
        ->where('bahan_baku_id', $bahanBaku->id)
        ->first();
    
    if (!$existingBBB) {
        $bbb = \App\Models\BomJobBBB::create([
            'bom_job_costing_id' => $bomJobCosting->id,
            'bahan_baku_id' => $bahanBaku->id,
            'jumlah' => 0.5,
            'satuan' => $bahanBaku->satuan->nama ?? 'KG',
            'harga_satuan' => $bahanBaku->harga_satuan,
            'subtotal' => 0.5 * $bahanBaku->harga_satuan
        ]);
        
        echo "BBB ditambahkan:" . PHP_EOL;
        echo "- Bahan: {$bahanBaku->nama_bahan}" . PHP_EOL;
        echo "- Jumlah: 0.5 {$bbb->satuan}" . PHP_EOL;
        echo "- Harga: Rp " . number_format($bahanBaku->harga_satuan, 2, ',', '.') . "/{$bbb->satuan}" . PHP_EOL;
        echo "- Subtotal: Rp " . number_format($bbb->subtotal, 2, ',', '.') . PHP_EOL;
    } else {
        echo "BBB sudah ada untuk bahan ini" . PHP_EOL;
    }
    
    // 3. Recalculate
    echo PHP_EOL . "Menghitung ulang..." . PHP_EOL;
    $bomJobCosting->recalculate();
    
    echo "Hasil:" . PHP_EOL;
    echo "- Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
    echo "- Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
    echo "- Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
    echo "- Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
    
    // 4. Update produk
    $produk = $bomJobCosting->produk;
    $produk->update([
        'harga_bom' => $bomJobCosting->total_hpp,
        'harga_pokok' => $bomJobCosting->total_hpp
    ]);
    
    echo PHP_EOL . "Data produk:" . PHP_EOL;
    echo "- Harga BOM: Rp " . number_format($produk->harga_bom, 2, ',', '.') . PHP_EOL;
    echo "- Harga Pokok: Rp " . number_format($produk->harga_pokok, 2, ',', '.') . PHP_EOL;
    
    // 5. Verifikasi akhir
    echo PHP_EOL . "VERIFIKASI:" . PHP_EOL;
    
    $finalBBB = \App\Models\BomJobBBB::with('bahanBaku')
        ->where('bom_job_costing_id', $bomJobCosting->id)
        ->get();
    
    foreach ($finalBBB as $bbb) {
        $status = $bbb->bahanBaku ? '✅ OK' : '❌ MASALAH';
        $nama = $bbb->bahanBaku ? $bbb->bahanBaku->nama_bahan : 'NULL';
        echo "- {$nama}: Rp " . number_format($bbb->subtotal, 2, ',', '.') . " ({$status})" . PHP_EOL;
    }
    
    echo PHP_EOL . "✅ Selesai! Sekarang cek halaman detail ketempling." . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
}
