<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;

// Data bahan pendukung yang diinginkan
$dataBahanPendukung = [
    [
        'nama' => 'Air',
        'jenis' => 'Air',
        'satuan' => 'L',
        'harga' => 1000,
        'stok' => 50,
        'stok_minimum' => 5
    ],
    [
        'nama' => 'Minyak Goreng',
        'jenis' => 'Minyak',
        'satuan' => 'L',
        'harga' => 14000,
        'stok' => 50,
        'stok_minimum' => 1
    ],
    [
        'nama' => 'Gas 30 Kg',
        'jenis' => 'Gas',
        'satuan' => 'Tabung',
        'harga' => 240000,
        'stok' => 50,
        'stok_minimum' => 1
    ],
    [
        'nama' => 'Ketumbar Bubuk',
        'jenis' => 'Bumbu',
        'satuan' => 'Bungkus',
        'harga' => 15000,
        'stok' => 50,
        'stok_minimum' => 0
    ],
    [
        'nama' => 'Cabe Merah',
        'jenis' => 'Bumbu',
        'satuan' => 'Kg',
        'harga' => 100000,
        'stok' => 50,
        'stok_minimum' => 0
    ],
    [
        'nama' => 'Cabe Hijau',
        'jenis' => 'Bumbu',
        'satuan' => 'Kg',
        'harga' => 120000,
        'stok' => 50,
        'stok_minimum' => 0
    ],
    [
        'nama' => 'Lada Hitam',
        'jenis' => 'Bumbu',
        'satuan' => 'Bungkus',
        'harga' => 15000,
        'stok' => 50,
        'stok_minimum' => 0
    ],
    [
        'nama' => 'Bawang Putih',
        'jenis' => 'Bumbu',
        'satuan' => 'Kg',
        'harga' => 28000,
        'stok' => 50,
        'stok_minimum' => 0
    ],
    [
        'nama' => 'Tepung Maizena',
        'jenis' => 'Bumbu',
        'satuan' => 'Bungkus',
        'harga' => 9000,
        'stok' => 50,
        'stok_minimum' => 0
    ],
    [
        'nama' => 'Merica Bubuk',
        'jenis' => 'Bumbu',
        'satuan' => 'Bungkus',
        'harga' => 2000,
        'stok' => 50,
        'stok_minimum' => 0
    ],
    [
        'nama' => 'Listrik',
        'jenis' => 'Listrik',
        'satuan' => 'Watt',
        'harga' => 3000,
        'stok' => 50,
        'stok_minimum' => 0
    ],
    [
        'nama' => 'Bawang Merah',
        'jenis' => 'Bumbu',
        'satuan' => 'KG',
        'harga' => 25000,
        'stok' => 50,
        'stok_minimum' => 0
    ],
    [
        'nama' => 'Kemasan',
        'jenis' => 'Kemasan',
        'satuan' => 'PCS',
        'harga' => 2000,
        'stok' => 50,
        'stok_minimum' => 0
    ]
];

echo "Membuat data bahan pendukung...\n\n";

try {
    // Cek apakah tabel bahan_pendukung ada
    if (!\Schema::hasTable('bahan_pendukung')) {
        echo "Tabel 'bahan_pendukung' tidak ditemukan. Membuat tabel terlebih dahulu...\n";
        
        // Buat tabel bahan_pendukung
        \Schema::create('bahan_pendukung', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis');
            $table->string('satuan');
            $table->decimal('harga', 15, 2);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->timestamps();
            
            $table->index('nama');
            $table->index('jenis');
        });
        
        echo "Tabel 'bahan_pendukung' berhasil dibuat.\n\n";
    }

    // Kosongkan data yang ada (opsional)
    \DB::table('bahan_pendukung')->delete();
    echo "Data lama berhasil dikosongkan.\n\n";

    // Insert data baru
    $successCount = 0;
    foreach ($dataBahanPendukung as $index => $bahan) {
        try {
            \DB::table('bahan_pendukung')->insert([
                'nama' => $bahan['nama'],
                'jenis' => $bahan['jenis'],
                'satuan' => $bahan['satuan'],
                'harga' => $bahan['harga'],
                'stok' => $bahan['stok'],
                'stok_minimum' => $bahan['stok_minimum'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $successCount++;
            echo ($index + 1) . ". {$bahan['nama']} - {$bahan['jenis']} - {$bahan['satuan']} - RP" . number_format($bahan['harga'], 0, ',', '.') . " - Stok: {$bahan['stok']} - Min: {$bahan['stok_minimum']} ✓\n";
            
        } catch (\Exception $e) {
            echo "Gagal menyimpan {$bahan['nama']}: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== SELESAI ===\n";
    echo "Berhasil membuat {$successCount} dari " . count($dataBahanPendukung) . " data bahan pendukung.\n\n";

    // Tampilkan data yang tersimpan
    echo "Data yang tersimpan di database:\n";
    echo str_repeat("=", 100) . "\n";
    echo sprintf("%-20s %-10s %-10s %-15s %-10s %-10s\n", "Nama", "Jenis", "Satuan", "Harga", "Stok", "Stok Min");
    echo str_repeat("-", 100) . "\n";
    
    $savedData = \DB::table('bahan_pendukung')->orderBy('jenis')->orderBy('nama')->get();
    foreach ($savedData as $item) {
        echo sprintf("%-20s %-10s %-10s %-15s %-10s %-10s\n", 
            $item->nama, 
            $item->jenis, 
            $item->satuan, 
            "RP" . number_format($item->harga, 0, ',', '.'), 
            $item->stok, 
            $item->stok_minimum
        );
    }
    echo str_repeat("=", 100) . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Pastikan konfigurasi database sudah benar.\n";
}
