<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK DATA PEGAWAI BTKL ===\n\n";

// Cek Jabatan BTKL
echo "1. JABATAN DENGAN KATEGORI BTKL:\n";
echo str_repeat("-", 60) . "\n";

$jabatans = \App\Models\Jabatan::where('kategori', 'btkl')
    ->with('pegawais')
    ->get();

foreach ($jabatans as $jabatan) {
    $jumlahPegawai = $jabatan->pegawais->count();
    echo "Jabatan: {$jabatan->nama} (ID: {$jabatan->id})\n";
    echo "Kategori: {$jabatan->kategori}\n";
    echo "Tarif: Rp " . number_format($jabatan->tarif ?? 0, 0, ',', '.') . "\n";
    echo "Jumlah Pegawai: {$jumlahPegawai}\n";
    
    if ($jumlahPegawai > 0) {
        echo "Daftar Pegawai:\n";
        foreach ($jabatan->pegawais as $pegawai) {
            echo "  - {$pegawai->nama} (jabatan_id: {$pegawai->jabatan_id})\n";
        }
    } else {
        echo "  (Tidak ada pegawai)\n";
    }
    echo "\n";
}

// Cek semua pegawai
echo "\n2. SEMUA PEGAWAI:\n";
echo str_repeat("-", 60) . "\n";

$allPegawais = \App\Models\Pegawai::with('jabatanRelasi')->get();

echo "Total Pegawai: " . $allPegawais->count() . "\n\n";

foreach ($allPegawais as $pegawai) {
    $jabatanNama = $pegawai->jabatanRelasi->nama ?? $pegawai->jabatan ?? 'N/A';
    $jabatanKategori = $pegawai->jabatanRelasi->kategori ?? 'N/A';
    
    echo "Pegawai: {$pegawai->nama}\n";
    echo "  jabatan_id: {$pegawai->jabatan_id}\n";
    echo "  Jabatan: {$jabatanNama}\n";
    echo "  Kategori: {$jabatanKategori}\n";
    echo "\n";
}

// Cek pegawai dengan jabatan_id yang ada di jabatan BTKL
echo "\n3. PEGAWAI DENGAN JABATAN_ID BTKL:\n";
echo str_repeat("-", 60) . "\n";

$btklJabatanIds = $jabatans->pluck('id')->toArray();
echo "Jabatan BTKL IDs: " . implode(', ', $btklJabatanIds) . "\n\n";

$pegawaisBtkl = \App\Models\Pegawai::whereIn('jabatan_id', $btklJabatanIds)
    ->with('jabatanRelasi')
    ->get();

echo "Jumlah Pegawai dengan jabatan_id BTKL: " . $pegawaisBtkl->count() . "\n\n";

foreach ($pegawaisBtkl as $pegawai) {
    echo "- {$pegawai->nama} (jabatan_id: {$pegawai->jabatan_id}, Jabatan: {$pegawai->jabatanRelasi->nama})\n";
}

echo "\n=== SELESAI ===\n";
