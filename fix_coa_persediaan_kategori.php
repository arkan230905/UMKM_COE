<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$userId = 2;

echo "=== Update Kategori COA Persediaan untuk User ID: {$userId} ===\n\n";

$coaKodes = ['114', '115', '116'];

foreach ($coaKodes as $kode) {
    $coa = \App\Models\Coa::withoutGlobalScopes()
        ->where('user_id', $userId)
        ->where('kode_akun', $kode)
        ->first();
    
    if ($coa) {
        $oldKategori = $coa->kategori_akun;
        $coa->kategori_akun = 'Persediaan';
        $coa->save();
        echo "✓ {$kode} - {$coa->nama_akun}: '{$oldKategori}' → 'Persediaan'\n";
    } else {
        echo "❌ COA {$kode} tidak ditemukan\n";
    }
}

echo "\n=== Verifikasi ===\n";
$coas = \App\Models\Coa::withoutGlobalScopes()
    ->where('user_id', $userId)
    ->where('tipe_akun', 'Aset')
    ->where('kategori_akun', 'Persediaan')
    ->orderBy('kode_akun')
    ->get();

echo "Total COA Persediaan: " . $coas->count() . "\n";
foreach($coas as $coa) {
    echo "- {$coa->kode_akun} - {$coa->nama_akun}\n";
}
