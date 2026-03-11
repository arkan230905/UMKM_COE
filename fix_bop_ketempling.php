<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIX BOP KETAMPLING ===" . PHP_EOL;

try {
    // Cari BomJobCosting untuk ketempling
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
    if (!$bomJobCosting) {
        echo "BomJobCosting tidak ditemukan!" . PHP_EOL;
        exit;
    }

    echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
    echo "Total BOP saat ini: {$bomJobCosting->total_bop}" . PHP_EOL;

    // Hapus semua data BOP yang ada
    echo "Menghapus data BOP lama..." . PHP_EOL;
    \Illuminate\Support\Facades\DB::table('bom_job_bop')
        ->where('bom_job_costing_id', $bomJobCosting->id)
        ->delete();

    // Tambah data BOP yang sama dengan produk lain
    echo "Menambahkan data BOP yang konsisten..." . PHP_EOL;
    
    $bopData = [
        'BOP Membumbui' => ['nama_bop' => 'BOP Membumbui', 'jumlah' => 0.005, 'tarif' => 0.005, 'subtotal' => 0.005],
        'BOP Menggoreng' => ['nama_bop' => 'BOP Menggoreng', 'jumlah' => 0.02, 'tarif' => 0.02, 'subtotal' => 0.02],
        'BOP Packing' => ['nama_bop' => 'BOP Packing', 'jumlah' => 0.02, 'tarif' => 0.02, 'subtotal' => 0.02]
    ];

    foreach ($bopData as $nama => $data) {
        \App\Models\BomJobBOP::create([
            'bom_job_costing_id' => $bomJobCosting->id,
            'nama_bop' => $data['nama_bop'],
            'jumlah' => $data['jumlah'],
            'tarif' => $data['tarif'],
            'subtotal' => $data['subtotal']
        ]);
        
        echo "Ditambahkan BOP: {$nama} - {$data['subtotal']}" . PHP_EOL;
    }

    // Update BomJobCosting total_bop
    $totalBOP = array_sum(array_column($bopData, 'subtotal'));
    $bomJobCosting->total_bop = $totalBOP;
    $bomJobCosting->save();
    
    echo "Total BOP diupdate ke: {$totalBOP}" . PHP_EOL;

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
}
