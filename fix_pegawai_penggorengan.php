<?php
/**
 * Script untuk fix jabatan_id pegawai Penggorengan
 * 
 * Jalankan: php fix_pegawai_penggorengan.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pegawai;
use App\Models\Jabatan;

echo "=== FIX JABATAN_ID PEGAWAI PENGGORENGAN ===\n\n";

// 1. Cari jabatan Penggorengan
$jabatanPenggorengan = Jabatan::where('nama', 'Penggorengan')->first();

if (!$jabatanPenggorengan) {
    echo "❌ Jabatan 'Penggorengan' tidak ditemukan!\n";
    exit(1);
}

echo "✅ Jabatan 'Penggorengan' ditemukan (ID: {$jabatanPenggorengan->id})\n\n";

// 2. Cari pegawai dengan jabatan (text) = "Penggorengan" tapi jabatan_id NULL
$pegawaiPenggorengan = Pegawai::where('jabatan', 'Penggorengan')
    ->whereNull('jabatan_id')
    ->get();

if ($pegawaiPenggorengan->isEmpty()) {
    echo "ℹ️  Tidak ada pegawai yang perlu di-fix\n";
    exit(0);
}

echo "Pegawai yang akan di-update:\n";
foreach ($pegawaiPenggorengan as $pegawai) {
    echo "   - {$pegawai->nama} (ID: {$pegawai->id})\n";
}

echo "\nMengupdate jabatan_id...\n";

foreach ($pegawaiPenggorengan as $pegawai) {
    $pegawai->jabatan_id = $jabatanPenggorengan->id;
    $pegawai->save();
    echo "   ✅ {$pegawai->nama} → jabatan_id = {$jabatanPenggorengan->id}\n";
}

// 3. Verifikasi
echo "\n=== VERIFIKASI ===\n";
$jabatanPenggorengan = Jabatan::with('pegawais')->find($jabatanPenggorengan->id);
$jumlahPegawai = $jabatanPenggorengan->pegawais->count();

echo "Jabatan: {$jabatanPenggorengan->nama}\n";
echo "Jumlah Pegawai: {$jumlahPegawai}\n";

if ($jumlahPegawai > 0) {
    echo "Daftar Pegawai:\n";
    foreach ($jabatanPenggorengan->pegawais as $pegawai) {
        echo "   - {$pegawai->nama}\n";
    }
}

echo "\n✅ SELESAI!\n";
