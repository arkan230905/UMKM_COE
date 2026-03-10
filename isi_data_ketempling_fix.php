<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ISI DATA KETAMPLING FIX ===" . PHP_EOL;

try {
    // Cari produk ketempling
    $produk = \App\Models\Produk::find(3);
    if (!$produk) {
        echo "Produk ketempling tidak ditemukan!" . PHP_EOL;
        exit;
    }

    echo "Produk: {$produk->nama_produk}" . PHP_EOL;

    // Buat BomJobCosting jika belum ada
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
    if (!$bomJobCosting) {
        echo "Membuat BomJobCosting baru..." . PHP_EOL;
        
        $bomJobCosting = \App\Models\BomJobCosting::create([
            'produk_id' => 3,
            'jumlah_produk' => 1,
            'total_bbb' => 0,
            'total_btkl' => 0,
            'total_bahan_pendukung' => 0,
            'total_bop' => 0,
            'total_hpp' => 0,
            'hpp_per_unit' => 0
        ]);
        
        echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
    } else {
        echo "BomJobCosting sudah ada: {$bomJobCosting->id}" . PHP_EOL;
    }

    // Tambah data BTKL default untuk ketempling
    echo "Menambahkan data BTKL..." . PHP_EOL;
    
    // Data BTKL yang diperlukan
    $btklData = [
        ['nama_btkl' => 'Penggorengan', 'kode_proses' => 'TKL-001', 'tarif_per_jam' => 50000, 'kapasitas_per_jam' => 1, 'waktu_pengerjaan' => 8, 'jumlah_pegawai' => 5, 'subtotal' => 2000000],
        ['nama_btkl' => 'Perbumbuan', 'kode_proses' => 'TKL-002', 'tarif_per_jam' => 40000, 'kapasitas_per_jam' => 1, 'waktu_pengerjaan' => 6, 'jumlah_pegawai' => 3, 'subtotal' => 720000],
        ['nama_btkl' => 'Pengemasan', 'kode_proses' => 'TKL-003', 'tarif_per_jam' => 35000, 'kapasitas_per_jam' => 1, 'waktu_pengerjaan' => 4, 'jumlah_pegawai' => 2, 'subtotal' => 280000]
    ];

    foreach ($btklData as $btkl) {
        // Cek apakah sudah ada berdasarkan kode_proses
        $existing = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
            ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
            ->where('bom_job_btkl.kode_proses', $btkl['kode_proses'])
            ->first();
        
        if (!$existing) {
            // Cari data master
            $btkl = \Illuminate\Support\Facades\DB::table('btkls')
                ->where('kode_proses', $btkl['kode_proses'])
                ->first();
            
            $jabatan = \Illuminate\Support\Facades\DB::table('jabatans')
                ->where('nama_jabatan', $btkl['nama_btkl'])
                ->first();
            
            if ($btkl && $jabatan) {
                // Buat bom_job_btkl dengan field yang benar
                \App\Models\BomJobBTKL::create([
                    'bom_job_costing_id' => $bomJobCosting->id,
                    'btkl_id' => $btkl->id,
                    'nama_proses' => $btkl['nama_btkl'],
                    'durasi_jam' => $btkl['waktu_pengerjaan'],
                    'tarif_per_jam' => $btkl['tarif_per_jam'],
                    'kapasitas_per_jam' => $btkl['kapasitas_per_jam'],
                    'subtotal' => $btkl['subtotal']
                ]);
                
                echo "Ditambahkan BTKL: {$btkl['nama_btkl']}" . PHP_EOL;
            }
        } else {
            echo "BTKL {$btkl['nama_btkl']} sudah ada" . PHP_EOL;
        }
    }

    // Tambah data BOP default untuk ketempling
    echo "Menambahkan data BOP..." . PHP_EOL;
    
    $bopData = [
        ['nama_bop' => 'Penggorengan', 'jumlah' => 1740],
        ['nama_bop' => 'Perbumbuan', 'jumlah' => 290],
        ['nama_bop' => 'Pengemasan', 'jumlah' => 1160]
    ];

    foreach ($bopData as $bop) {
        // Cek apakah sudah ada
        $existing = \Illuminate\Support\Facades\DB::table('bom_job_bop')
            ->where('bom_job_bop.bom_job_costing_id', $bomJobCosting->id)
            ->where('bom_job_bop.nama_bop', $bop['nama_bop'])
            ->first();
        
        if (!$existing) {
            // Buat bom_job_bop
            \App\Models\BomJobBOP::create([
                'bom_job_costing_id' => $bomJobCosting->id,
                'nama_bop' => $bop['nama_bop'],
                'jumlah' => $bop['jumlah'],
                'subtotal' => $bop['jumlah']
            ]);
            
            echo "Ditambahkan BOP: {$bop['nama_bop']}" . PHP_EOL;
        } else {
            echo "BOP {$bop['nama_bop']} sudah ada" . PHP_EOL;
        }
    }

    // Update BomJobCosting dengan total yang benar
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
