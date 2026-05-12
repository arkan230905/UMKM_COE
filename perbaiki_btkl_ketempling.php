<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PERBAIKI BTKL KETAMPLING ===" . PHP_EOL;

try {
    // Cari BomJobCosting untuk ketempling
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
    if (!$bomJobCosting) {
        echo "BomJobCosting tidak ditemukan!" . PHP_EOL;
        exit;
    }

    echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;

    // Hapus semua data BTKL yang ada untuk ketempling
    echo "Menghapus data BTKL lama..." . PHP_EOL;
    \Illuminate\Support\Facades\DB::table('bom_job_btkl')
        ->where('bom_job_costing_id', $bomJobCosting->id)
        ->delete();

    // Tambah data BTKL yang sama dengan produk lain (Ayam Ketumbar & Opak Geulis)
    echo "Menambahkan data BTKL yang konsisten..." . PHP_EOL;
    
    $btklData = [
        'Membumbui' => ['nama_proses' => 'Membumbui', 'durasi_jam' => 1, 'tarif_per_jam' => 240, 'kapasitas_per_jam' => 1, 'subtotal' => 240],
        'Menggoreng' => ['nama_proses' => 'Menggoreng', 'durasi_jam' => 1, 'tarif_per_jam' => 900, 'kapasitas_per_jam' => 1, 'subtotal' => 900],
        'Packing' => ['nama_proses' => 'Packing', 'durasi_jam' => 1, 'tarif_per_jam' => 900, 'kapasitas_per_jam' => 1, 'subtotal' => 900]
    ];

    foreach ($btklData as $nama => $data) {
        \App\Models\BomJobBTKL::create([
            'bom_job_costing_id' => $bomJobCosting->id,
            'nama_proses' => $data['nama_proses'],
            'durasi_jam' => $data['durasi_jam'],
            'tarif_per_jam' => $data['tarif_per_jam'],
            'kapasitas_per_jam' => $data['kapasitas_per_jam'],
            'subtotal' => $data['subtotal']
        ]);
        
        echo "Ditambahkan BTKL: {$nama} - {$data['subtotal']}" . PHP_EOL;
    }

    // Update BomJobCosting total_btkl
    $totalBTKL = array_sum(array_column($btklData, 'subtotal'));
    $bomJobCosting->total_btkl = $totalBTKL;
    $bomJobCosting->save();
    
    echo "Total BTKL diupdate ke: {$totalBTKL}" . PHP_EOL;

    // Update harga pokok produk
    $totalBiayaHPP = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung + $bomJobCosting->total_btkl + $bomJobCosting->total_bop;
    
    \App\Models\Produk::where('id', 3)->update([
        'harga_pokok' => $totalBiayaHPP
    ]);

    echo "Harga pokok produk diupdate ke: {$totalBiayaHPP}" . PHP_EOL;
    echo PHP_EOL;
    echo "=== SELESAI ===" . PHP_EOL;

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
}
