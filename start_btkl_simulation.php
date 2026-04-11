<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== MULAI SIMULASI BTKL DARI DATA 0 ===\n\n";

try {
    // 1. Cek database sudah kosong
    echo "=== CEK DATABASE ===\n";
    $tables = ['proses_produksis', 'bop_proses', 'btkls', 'bom_job_btkl'];
    
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "{$table}: {$count} records\n";
        }
    }
    echo "\n";

    // 2. Siapkan data contoh untuk simulasi
    echo "=== DATA CONTOH UNTUK SIMULASI ===\n";
    
    $sampleData = [
        [
            'kode_proses' => 'PRO-001',
            'nama_proses' => 'Perbumbuan',
            'deskripsi' => 'Proses perbumbuan produk makanan',
            'tarif_btkl' => 25000,
            'satuan_btkl' => 'jam',
            'kapasitas_per_jam' => 200,
            'jabatan_id' => 1, // Asumsi jabatan ada
        ],
        [
            'kode_proses' => 'PRO-002',
            'nama_proses' => 'Penggorengan',
            'deskripsi' => 'Proses penggorengan produk',
            'tarif_btkl' => 30000,
            'satuan_btkl' => 'jam',
            'kapasitas_per_jam' => 100,
            'jabatan_id' => 2,
        ],
        [
            'kode_proses' => 'PRO-003',
            'nama_proses' => 'Pengemasan',
            'deskripsi' => 'Proses pengemasan produk jadi',
            'tarif_btkl' => 20000,
            'satuan_btkl' => 'jam',
            'kapasitas_per_jam' => 150,
            'jabatan_id' => 3,
        ]
    ];
    
    echo "Data siap untuk input:\n";
    foreach ($sampleData as $index => $data) {
        $biayaPerProduk = $data['kapasitas_per_jam'] > 0 ? $data['tarif_btkl'] / $data['kapasitas_per_jam'] : 0;
        echo ($index + 1) . ". {$data['nama_proses']}\n";
        echo "   Kode: {$data['kode_proses']}\n";
        echo "   Tarif: Rp {$data['tarif_btkl']}/jam\n";
        echo "   Kapasitas: {$data['kapasitas_per_jam']} pcs/jam\n";
        echo "   Biaya/Produk: Rp " . number_format($biayaPerProduk, 2) . "\n\n";
    }

    // 3. Input data ke database
    echo "=== INPUT DATA KE DATABASE ===\n";
    
    foreach ($sampleData as $index => $data) {
        echo "Input data " . ($index + 1) . ": {$data['nama_proses']}... ";
        
        try {
            // Insert ke proses_produksis
            $prosesId = DB::table('proses_produksis')->insertGetId([
                'kode_proses' => $data['kode_proses'],
                'nama_proses' => $data['nama_proses'],
                'deskripsi' => $data['deskripsi'],
                'tarif_btkl' => $data['tarif_btkl'],
                'satuan_btkl' => $data['satuan_btkl'],
                'kapasitas_per_jam' => $data['kapasitas_per_jam'],
                'jabatan_id' => $data['jabatan_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "✓ (ID: {$prosesId})\n";
            
            // Insert ke btkls (jika diperlukan)
            DB::table('btkls')->insert([
                'kode_proses' => $data['kode_proses'],
                'nama_btkl' => $data['nama_proses'],
                'jabatan_id' => $data['jabatan_id'],
                'tarif_per_jam' => $data['tarif_btkl'],
                'satuan' => $data['satuan_btkl'],
                'kapasitas_per_jam' => $data['kapasitas_per_jam'],
                'deskripsi_proses' => $data['deskripsi'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== VERIFIKASI INPUT ===\n";
    
    // Verifikasi data yang sudah diinput
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "{$table}: {$count} records\n";
        }
    }
    
    // Tampilkan data yang sudah diinput
    echo "\n=== DATA YANG TERSIMPAN ===\n";
    $prosesData = DB::table('proses_produksis')->get();
    
    foreach ($prosesData as $proses) {
        $biayaPerProduk = $proses->kapasitas_per_jam > 0 ? $proses->tarif_btkl / $proses->kapasitas_per_jam : 0;
        echo "ID: {$proses->id}\n";
        echo "Nama: {$proses->nama_proses}\n";
        echo "Tarif: Rp {$proses->tarif_btkl}/jam\n";
        echo "Kapasitas: {$proses->kapasitas_per_jam} pcs/jam\n";
        echo "Biaya/Produk: Rp " . number_format($biayaPerProduk, 2) . "\n\n";
    }
    
    echo "=== SIMULASI SIAP ===\n";
    echo "Database sudah terisi dengan data awal simulasi.\n";
    echo "Anda bisa mulai test di halaman master-data/BTKL dan master-data/BOP.\n";
    echo "Sinkronisasi otomatis akan berjalan saat ada perubahan data.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
