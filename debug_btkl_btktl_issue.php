<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug BTKL vs BTKTL Issue ===" . PHP_EOL;

// Analyze inconsistency between employee data and journal entry
echo PHP_EOL . "Masalah:" . PHP_EOL;
echo "- Pegawai: Dedi Gunawan" . PHP_EOL;
echo "- Jabatan: Bagian Gudang" . PHP_EOL;
echo "- Jenis Pegawai: BTKL" . PHP_EOL;
echo "- Tapi di data pegawai: BTKL" . PHP_EOL;
echo "- Di jurnal: COA 52 (BIAYA TENAGA KERJA LANGSUNG)" . PHP_EOL;
echo "- User bilang: Seharusnya BTKTL, bukan BTKL" . PHP_EOL;

echo PHP_EOL . "=== Analisis Data Pegawai ===" . PHP_EOL;

// Check employee data for Dedi Gunawan
$pegawai = DB::table('pegawais')
    ->where('nama_pegawai', 'like', '%Dedi Gunawan%')
    ->first();

if ($pegawai) {
    echo "Data Pegawai Dedi Gunawan:" . PHP_EOL;
    echo "ID: " . $pegawai->id . PHP_EOL;
    echo "Nama: " . $pegawai->nama_pegawai . PHP_EOL;
    echo "Jabatan: " . $pegawai->jabatan . PHP_EOL;
    echo "Jenis Pegawai: " . $pegawai->jenis_pegawai . PHP_EOL;
    echo "Jenis Pegawai (asli): " . $pegawai->jenis_pegawai . PHP_EOL;
} else {
    echo "❌ Data pegawai Dedi Gunawan tidak ditemukan" . PHP_EOL;
}

echo PHP_EOL . "=== Analisis BTKL vs BTKTL ===" . PHP_EOL;

// Check BTKL data
$btklData = DB::table('btkls')->get();
echo "Data BTKL di Database:" . PHP_EOL;
foreach ($btklData as $btkl) {
    echo "- ID: " . $btkl->id . ", Nama: " . $btkl->nama_btkl . ", Jenis: " . ($btkl->jenis_btkl ?? 'NULL') . PHP_EOL;
}

echo PHP_EOL . "=== Analisis Penggajian ===" . PHP_EOL;

// Check how penggajian creates journal entries
echo "Menganalisis logika penggajian:" . PHP_EOL;

// Check if there's specific logic for BTKTL
$btktlData = DB::table('btkls')->where('nama_btkl', 'like', '%TIDAK LANGSUNG%')->first();
echo "BTKTL Data: " . ($btktlData ? "Ditemukan: " . $btktlData->nama_btkl : "Tidak ditemukan") . PHP_EOL;

echo PHP_EOL . "=== Cek Penggajian Controller ===" . PHP_EOL;

// Check penggajian controller logic
echo "Mengidentifikasi source penggajian journal entries..." . PHP_EOL;

// Look for penggajian related files
$files = [
    'app/Http/Controllers/PenggajianController.php',
    'app/Http/Controllers/ExpensePaymentController.php',
    'app/Services/PenggajianService.php'
];

foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ Found: " . $file . PHP_EOL;
    } else {
        echo "❌ Not Found: " . $file . PHP_EOL;
    }
}

echo PHP_EOL . "=== Analisis Logika Penggajian ===" . PHP_EOL;

// Check if there's specific mapping for BTKTL
echo "Kemungkinan Logika Saat Ini:" . PHP_EOL;
echo "1. Semua jenis pegawai (BTKL, BTKTL, dll) ke COA 52" . PHP_EOL;
echo "2. Tidak ada klasifikasi detail per jenis" . PHP_EOL;
echo "3. Menggunakan COA umum 'BIAYA TENAGA KERJA LANGSUNG'" . PHP_EOL;

echo PHP_EOL . "=== Masalah Utama ===" . PHP_EOL;
echo "❌ Inkonsistensi Data:" . PHP_EOL;
echo "   - Data pegawai: BTKL" . PHP_EOL;
echo "   - User expectation: BTKTL" . PHP_EOL;
echo "   - Journal entry: COA 52 (BTKL)" . PHP_EOL;

echo PHP_EOL . "=== Solusi yang Mungkin ===" . PHP_EOL;

echo "Solusi 1: Perbaiki Data Pegawai" . PHP_EOL;
echo "- Ubah jenis pegawai Dedi Gunawan dari BTKL ke BTKTL" . PHP_EOL;
echo "- Sesuaikan dengan kebutuhan user" . PHP_EOL;

echo PHP_EOL . "Solusi 2: Tambah COA Khusus BTKTL" . PHP_EOL;
echo "- Buat COA baru untuk BTKTL (misal: 52A)" . PHP_EOL;
echo "- Tambah logic mapping jenis pegawai ke COA" . PHP_EOL;

echo PHP_EOL . "Solusi 3: Perbaiki Logika Penggajian" . PHP_EOL;
echo "- Tambah logic untuk bedakan BTKL vs BTKTL" . PHP_EOL;
echo "- Gunakan COA berbeda per jenis" . PHP_EOL;

echo PHP_EOL . "=== Rekomendasi ===" . PHP_EOL;
echo "💡 Rekomendasi Terbaik: Solusi 1 (Perbaiki Data)" . PHP_EOL;
echo "✅ Lebih sederhana dan konsisten" . PHP_EOL;
echo "✅ Tidak perlu ubah struktur COA" . PHP_EOL;
echo "✅ Sesuai dengan kebutuhan user" . PHP_EOL;

echo PHP_EOL . "=== Action Items ===" . PHP_EOL;
echo "1. Konfirmasi data pegawai yang benar" . PHP_EOL;
echo "2. Update jenis pegawai Dedi Gunawan ke BTKTL" . PHP_EOL;
echo "3. Verifikasi penggajian berikutnya" . PHP_EOL;
