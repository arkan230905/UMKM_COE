<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ISI DATA SIMPLE ===" . PHP_EOL;

try {
    // Cari BomJobCosting untuk ketempling
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
    if (!$bomJobCosting) {
        echo "BomJobCosting tidak ditemukan!" . PHP_EOL;
        exit;
    }

    echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;

    // Tambah data BTKL
    echo "Menambahkan data BTKL..." . PHP_EOL;
    
    $btklData = [
        'Penggorengan' => ['nama_proses' => 'Penggorengan', 'durasi_jam' => 8, 'tarif_per_jam' => 50000, 'kapasitas_per_jam' => 1, 'subtotal' => 2000000],
        'Perbumbuan' => ['nama_proses' => 'Perbumbuan', 'durasi_jam' => 6, 'tarif_per_jam' => 40000, 'kapasitas_per_jam' => 1, 'subtotal' => 720000],
        'Pengemasan' => ['nama_proses' => 'Pengemasan', 'durasi_jam' => 4, 'tarif_per_jam' => 35000, 'kapasitas_per_jam' => 1, 'subtotal' => 280000]
    ];

    foreach ($btklData as $nama => $data) {
        // Cek apakah sudah ada
        $existing = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
            ->where('bom_job_costing_id', $bomJobCosting->id)
            ->where('nama_proses', $data['nama_proses'])
            ->first();
        
        if (!$existing) {
            \App\Models\BomJobBTKL::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'nama_proses' => $data['nama_proses'],
                'durasi_jam' => $data['durasi_jam'],
                'tarif_per_jam' => $data['tarif_per_jam'],
                'kapasitas_per_jam' => $data['kapasitas_per_jam'],
                'subtotal' => $data['subtotal']
            ]);
            
            echo "Ditambahkan BTKL: {$nama}" . PHP_EOL;
        }
    }

    // Tambah data BOP
    echo "Menambahkan data BOP..." . PHP_EOL;
    
    $bopData = [
        'Penggorengan' => ['nama_bop' => 'Penggorengan', 'jumlah' => 1740],
        'Perbumbuan' => ['nama_bop' => 'Perbumbuan', 'jumlah' => 290],
        'Pengemasan' => ['nama_bop' => 'Pengemasan', 'jumlah' => 1160]
    ];

    foreach ($bopData as $nama => $data) {
        // Cek apakah sudah ada
        $existing = \Illuminate\Support\Facades\DB::table('bom_job_bop')
            ->where('bom_job_costing_id', $bomJobCosting->id)
            ->where('nama_bop', $data['nama_bop'])
            ->first();
        
        if (!$existing) {
            \App\Models\BomJobBOP::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'nama_bop' => $data['nama_bop'],
                'jumlah' => $data['jumlah'],
                'subtotal' => $data['jumlah']
            ]);
            
            echo "Ditambahkan BOP: {$nama}" . PHP_EOL;
        }
    }

    // Update BomJobCosting
    $bomJobCosting->fresh();
    $totalBiayaHPP = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung + $bomJobCosting->total_btkl + $bomJobCosting->total_bop;
    
    echo "Total biaya HPP: {$totalBiayaHPP}" . PHP_EOL;
    
    // Update harga pokok produk
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
