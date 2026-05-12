<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Analisis Klasifikasi Jabatan vs COA ===" . PHP_EOL;

// Analyze the real issue: Bagian Gudang should be BTKL not BTKTL
echo PHP_EOL . "Masalah Sebenarnya:" . PHP_EOL;
echo "- Jabatan: Bagian Gudang" . PHP_EOL;
echo "- Data pegawai: btktl (SUDAH BENAR)" . PHP_EOL;
echo "- Tapi jurnal: COA 52 (BIAYA TENAGA KERJA LANGSUNG)" . PHP_EOL;
echo "- User bilang: Bagian Gudang masuk BTKL, bukan BTKTL" . PHP_EOL;

echo PHP_EOL . "=== Analisis Logika Klasifikasi ===" . PHP_EOL;

// Check current classification logic in penggajian
echo "Menganalisis logika klasifikasi penggajian..." . PHP_EOL;

// Get all jabatan data
$jabatanData = DB::table('jabatans')->get();
echo "Data Jabatan di Database:" . PHP_EOL;
foreach ($jabatanData as $jabatan) {
    echo "- " . $jabatan->nama_jabatan . " (Kategori: " . ($jabatan->kategori ?? 'N/A') . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Cek Klasifikasi Saat Ini ===" . PHP_EOL;

// Check how penggajian system classifies departments
$gudangJabatan = DB::table('jabatans')
    ->where('nama_jabatan', 'like', '%gudang%')
    ->first();

echo "Jabatan Gudang:" . PHP_EOL;
if ($gudangJabatan) {
    echo "- Nama: " . $gudangJabatan->nama_jabatan . PHP_EOL;
    echo "- Kategori: " . ($gudangJabatan->kategori ?? 'N/A') . PHP_EOL;
    echo "- ID: " . $gudangJabatan->id . PHP_EOL;
} else {
    echo "❌ Jabatan Gudang tidak ditemukan" . PHP_EOL;
}

echo PHP_EOL . "=== Analisis BTKL vs BTKTL ===" . PHP_EOL;

// Check BTKL data
$btklData = DB::table('btkls')->get();
echo "Data BTKL di Database:" . PHP_EOL;
foreach ($btklData as $btkl) {
    echo "- " . $btkl->nama_btkl . " (Jenis: " . ($btkl->jenis_btkl ?? 'N/A') . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Cek Klasifikasi Pegawai Gudang ===" . PHP_EOL;

// Check all pegawai with jabatan gudang
$gudangPegawais = DB::table('pegawais')
    ->join('jabatans', 'pegawais.jabatan_id', '=', 'jabatans.id')
    ->where('jabatans.nama_jabatan', 'like', '%gudang%')
    ->select('pegawais.nama', 'pegawais.jenis_pegawai', 'jabatans.nama_jabatan', 'jabatans.kategori')
    ->get();

echo "Pegawai dengan Jabatan Gudang:" . PHP_EOL;
foreach ($gudangPegawais as $pegawai) {
    echo "- " . $pegawai->nama . " | Jabatan: " . $pegawai->nama_jabatan . " | Jenis: " . $pegawai->jenis_pegawai . " | Kategori Jabatan: " . ($pegawai->kategori ?? 'N/A') . PHP_EOL;
}

echo PHP_EOL . "=== Root Cause Analysis ===" . PHP_EOL;
echo "Masalah Utama:" . PHP_EOL;
echo "1. Bagian Gudang (produksi langsung) seharusnya BTKL" . PHP_EOL;
echo "2. Tapi sistem mungkin mengklasifikasikan semua gudang ke BTKTL" . PHP_EOL;
echo "3. Atau ada logic lain yang menyebabkan kesalahan" . PHP_EOL;

echo PHP_EOL . "=== Possible Solutions ===" . PHP_EOL;

echo "Solusi 1: Perbaiki Logika Klasifikasi" . PHP_EOL;
echo "- Tambah logic: Jika jabatan mengandung 'gudang' → BTKL" . PHP_EOL;
echo "- Jika jabatan mengandung 'produksi' → BTKL" . PHP_EOL;
echo "- Jika jabatan mengandung 'supervisor' → BTKTL" . PHP_EOL;

echo PHP_EOL . "Solusi 2: Perbaiki Data Jabatan" . PHP_EOL;
echo "- Update kategori jabatan gudang ke 'BTKL'" . PHP_EOL;
echo "- Sesuaikan dengan kebutuhan klasifikasi" . PHP_EOL;

echo PHP_EOL . "Solusi 3: Tambah COA Khusus" . PHP_EOL;
echo "- Buat COA 52A untuk BTKL Gudang" . PHP_EOL;
echo "- Buat COA 52B untuk BTKL Produksi" . PHP_EOL;
echo "- Tetap gunakan COA 52 untuk BTKTL" . PHP_EOL;

echo PHP_EOL . "=== Rekomendasi Terbaik ===" . PHP_EOL;
echo "💡 Solusi 1 (Perbaiki Logika):" . PHP_EOL;
echo "- Lebih fleksibel dan adaptif" . PHP_EOL;
echo "- Tidak perlu ubah struktur COA" . PHP_EOL;
echo "- Bisa handle berbagai macam jabatan" . PHP_EOL;

echo PHP_EOL . "=== Action Required ===" . PHP_EOL;
echo "1. Cek file penggajian controller" . PHP_EOL;
echo "2. Analisis logic klasifikasi jabatan" . PHP_EOL;
echo "3. Perbaiki sesuai kebutuhan" . PHP_EOL;
echo "4. Test dengan data Dedi Gunawan" . PHP_EOL;
