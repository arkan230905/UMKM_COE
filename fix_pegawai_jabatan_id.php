<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIX PEGAWAI JABATAN_ID ===\n\n";

// Cari pegawai yang jabatan_id nya kosong tapi punya field jabatan
$pegawaisWithoutJabatanId = \App\Models\Pegawai::whereNull('jabatan_id')
    ->orWhere('jabatan_id', '')
    ->get();

echo "Pegawai dengan jabatan_id kosong: " . $pegawaisWithoutJabatanId->count() . "\n\n";

foreach ($pegawaisWithoutJabatanId as $pegawai) {
    echo "Pegawai: {$pegawai->nama}\n";
    echo "  jabatan_id saat ini: " . ($pegawai->jabatan_id ?? 'NULL') . "\n";
    echo "  Jabatan (text): {$pegawai->jabatan}\n";
    
    // Cari jabatan berdasarkan nama
    $jabatan = \App\Models\Jabatan::where('nama', $pegawai->jabatan)->first();
    
    if ($jabatan) {
        echo "  ✓ Ditemukan Jabatan ID: {$jabatan->id} ({$jabatan->nama})\n";
        echo "  Kategori: {$jabatan->kategori}\n";
        
        // Update jabatan_id
        $pegawai->jabatan_id = $jabatan->id;
        $pegawai->save();
        
        echo "  ✓ UPDATED: jabatan_id = {$jabatan->id}\n";
    } else {
        echo "  ✗ Jabatan '{$pegawai->jabatan}' tidak ditemukan di tabel jabatans\n";
        
        // Tampilkan jabatan yang tersedia
        echo "  Jabatan yang tersedia:\n";
        $availableJabatans = \App\Models\Jabatan::all();
        foreach ($availableJabatans as $j) {
            echo "    - {$j->nama} (ID: {$j->id}, Kategori: {$j->kategori})\n";
        }
    }
    
    echo "\n";
}

echo "\n=== VERIFIKASI SETELAH FIX ===\n\n";

// Cek lagi jabatan BTKL
$jabatans = \App\Models\Jabatan::where('kategori', 'btkl')
    ->with('pegawais')
    ->get();

foreach ($jabatans as $jabatan) {
    $jumlahPegawai = $jabatan->pegawais->count();
    echo "Jabatan: {$jabatan->nama} (ID: {$jabatan->id})\n";
    echo "Jumlah Pegawai: {$jumlahPegawai}\n";
    
    if ($jumlahPegawai > 0) {
        foreach ($jabatan->pegawais as $pegawai) {
            echo "  - {$pegawai->nama}\n";
        }
    }
    echo "\n";
}

echo "=== SELESAI ===\n";
