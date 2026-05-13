<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Insert COA 57 untuk user_id 2
DB::table('coas')->insert([
    'user_id' => 2,
    'kode_akun' => '57',
    'nama_akun' => 'Biaya Air & Kebersihan',
    'tipe_akun' => 'Biaya',
    'kategori_akun' => 'Biaya',
    'saldo_normal' => 'debit',
    'saldo_awal' => 0,
    'tanggal_saldo_awal' => now(),
    'posted_saldo_awal' => 0,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "✅ COA 57 - Biaya Air & Kebersihan berhasil ditambahkan untuk user_id 2\n";
