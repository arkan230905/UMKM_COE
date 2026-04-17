<?php
/**
 * Script untuk cek pegawai dengan jabatan Penggorengan
 * 
 * Jalankan: php check_pegawai_penggorengan.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Jabatan;
use App\Models\Pegawai;

echo "=== CEK PEGAWAI PENGGORENGAN ===\n\n";

// 1. Cek semua jabatan BTKL
echo "1. JABATAN BTKL:\n";
$jabatanBtkl = Jabatan::where('kategori', 'btkl')
    ->with('pegawais')
    ->orderBy('nama')
    ->get();

foreach ($jabatanBtkl as $jabatan) {
    $jumlahPegawai = $jabatan->pegawais->count();
    echo "   - {$jabatan->nama} (ID: {$jabatan->id}): {$jumlahPegawai} pegawai\n";
    
    if ($jumlahPegawai > 0) {
        foreach ($jabatan->pegawais as $pegawai) {
            echo "     * {$pegawai->nama}\n";
        }
    }
}

// 2. Cek pegawai yang jabatannya mengandung "goreng"
echo "\n2. PEGAWAI DENGAN JABATAN MENGANDUNG 'GORENG':\n";
$pegawaiGoreng = Pegawai::whereHas('jabatanRelasi', function($q) {
    $q->where('nama', 'like', '%goreng%');
})->with('jabatanRelasi')->get();

if ($pegawaiGoreng->count() > 0) {
    foreach ($pegawaiGoreng as $pegawai) {
        echo "   - {$pegawai->nama} (Jabatan: {$pegawai->jabatanRelasi->nama}, ID: {$pegawai->jabatan_id})\n";
    }
} else {
    echo "   Tidak ada pegawai dengan jabatan mengandung 'goreng'\n";
}

// 3. Cek semua pegawai dan jabatannya
echo "\n3. SEMUA PEGAWAI:\n";
$allPegawai = Pegawai::with('jabatanRelasi')->get();

foreach ($allPegawai as $pegawai) {
    $jabatanNama = $pegawai->jabatanRelasi ? $pegawai->jabatanRelasi->nama : 'TIDAK ADA JABATAN';
    $jabatanId = $pegawai->jabatan_id ?? 'NULL';
    echo "   - {$pegawai->nama} → Jabatan: {$jabatanNama} (ID: {$jabatanId})\n";
}

// 4. Cek jabatan dengan nama mengandung "goreng"
echo "\n4. JABATAN MENGANDUNG 'GORENG':\n";
$jabatanGoreng = Jabatan::where('nama', 'like', '%goreng%')->get();

if ($jabatanGoreng->count() > 0) {
    foreach ($jabatanGoreng as $jabatan) {
        $jumlahPegawai = Pegawai::where('jabatan_id', $jabatan->id)->count();
        echo "   - {$jabatan->nama} (ID: {$jabatan->id}, Kategori: {$jabatan->kategori}): {$jumlahPegawai} pegawai\n";
    }
} else {
    echo "   Tidak ada jabatan mengandung 'goreng'\n";
}

echo "\n=== SELESAI ===\n";
