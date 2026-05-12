<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Inserting missing Satuan for all users...\n\n";

// Satuan yang kurang (12 unit)
$missingSatuans = [
    ['kode' => 'ONS', 'nama' => 'Ons', 'tipe' => 'weight', 'kategori' => 'berat', 'is_dasar' => false, 'nilai_konversi' => 0.025, 'faktor_ke_dasar' => 0.025],
    ['kode' => 'G', 'nama' => 'Gram', 'tipe' => 'weight', 'kategori' => 'berat', 'is_dasar' => false, 'nilai_konversi' => 0.001, 'faktor_ke_dasar' => 0.001],
    ['kode' => 'LTR', 'nama' => 'Liter', 'tipe' => 'volume', 'kategori' => 'volume', 'is_dasar' => true, 'nilai_konversi' => 1.0, 'faktor_ke_dasar' => 1.0],
    ['kode' => 'PTG', 'nama' => 'Potong', 'tipe' => 'unit', 'kategori' => 'jumlah', 'is_dasar' => false, 'nilai_konversi' => 1.0, 'faktor_ke_dasar' => 1.0],
    ['kode' => 'EKOR', 'nama' => 'Ekor', 'tipe' => 'unit', 'kategori' => 'jumlah', 'is_dasar' => false, 'nilai_konversi' => 1.0, 'faktor_ke_dasar' => 1.0],
    ['kode' => 'PCS', 'nama' => 'Pieces', 'tipe' => 'unit', 'kategori' => 'jumlah', 'is_dasar' => true, 'nilai_konversi' => 1.0, 'faktor_ke_dasar' => 1.0],
    ['kode' => 'BNGKS', 'nama' => 'Bungkus', 'tipe' => 'unit', 'kategori' => 'jumlah', 'is_dasar' => false, 'nilai_konversi' => 1.0, 'faktor_ke_dasar' => 1.0],
    ['kode' => 'CUP', 'nama' => 'Cup', 'tipe' => 'volume', 'kategori' => 'volume', 'is_dasar' => false, 'nilai_konversi' => 0.24, 'faktor_ke_dasar' => 0.24],
    ['kode' => 'GL', 'nama' => 'Galon', 'tipe' => 'volume', 'kategori' => 'volume', 'is_dasar' => false, 'nilai_konversi' => 3.785, 'faktor_ke_dasar' => 3.785],
    ['kode' => 'TBG', 'nama' => 'Tabung', 'tipe' => 'unit', 'kategori' => 'jumlah', 'is_dasar' => false, 'nilai_konversi' => 1.0, 'faktor_ke_dasar' => 1.0],
    ['kode' => 'SNG', 'nama' => 'Siung', 'tipe' => 'unit', 'kategori' => 'jumlah', 'is_dasar' => false, 'nilai_konversi' => 1.0, 'faktor_ke_dasar' => 1.0],
    ['kode' => 'KLG', 'nama' => 'Kaleng', 'tipe' => 'volume', 'kategori' => 'volume', 'is_dasar' => false, 'nilai_konversi' => 1.0, 'faktor_ke_dasar' => 1.0],
];

$users = DB::table('users')->get();

foreach ($users as $user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    
    $insertedCount = 0;
    
    foreach ($missingSatuans as $satuan) {
        // Cek apakah sudah ada
        $exists = DB::table('satuans')
            ->where('user_id', $user->id)
            ->where('kode', $satuan['kode'])
            ->exists();
        
        if ($exists) {
            continue;
        }
        
        // Insert
        DB::table('satuans')->insert([
            'user_id' => $user->id,
            'kode' => $satuan['kode'],
            'nama' => $satuan['nama'],
            'tipe' => $satuan['tipe'],
            'kategori' => $satuan['kategori'],
            'is_dasar' => $satuan['is_dasar'],
            'is_active' => true,
            'nilai_konversi' => $satuan['nilai_konversi'],
            'faktor_ke_dasar' => $satuan['faktor_ke_dasar'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $insertedCount++;
    }
    
    echo "  ✅ Inserted {$insertedCount} Satuan\n\n";
}

echo "🎉 Done!\n";
