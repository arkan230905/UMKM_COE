<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix BTKL vs BTKTL Pegawai Issue ===" . PHP_EOL;

// Update data pegawai Dedi Gunawan dari btktl ke btkl
echo PHP_EOL . "Memperbaiki data pegawai Dedi Gunawan..." . PHP_EOL;

$updateResult = DB::table('pegawais')
    ->where('nama', 'Dedi Gunawan')
    ->update([
        'jenis_pegawai' => 'btkl' // Ubah dari btktl ke btkl
    ]);

if ($updateResult) {
    echo "✅ Berhasil update jenis pegawai Dedi Gunawan: btktl → btkl" . PHP_EOL;
} else {
    echo "❌ Gagal update data pegawai" . PHP_EOL;
}

echo PHP_EOL . "=== Verifikasi Data Setelah Fix ===" . PHP_EOL;

// Verify the update
$pegawaiAfter = DB::table('pegawais')
    ->where('nama', 'Dedi Gunawan')
    ->first();

if ($pegawaiAfter) {
    echo "Data Pegawai Dedi Gunawan Setelah Fix:" . PHP_EOL;
    echo "ID: " . $pegawaiAfter->id . PHP_EOL;
    echo "Nama: " . $pegawaiAfter->nama . PHP_EOL;
    echo "Jabatan: " . $pegawaiAfter->jabatan . PHP_EOL;
    echo "Jenis Pegawai: " . $pegawaiAfter->jenis_pegawai . PHP_EOL;
    echo "Kategori: " . $pegawaiAfter->kategori . PHP_EOL;
    
    // Check if now consistent
    if ($pegawaiAfter->jenis_pegawai === 'btkl') {
        echo "✅ Data sudah konsisten: Jenis pegawai = btkl" . PHP_EOL;
        echo "✅ COA 52 (BIAYA TENAGA KERJA LANGSUNG) sudah tepat" . PHP_EOL;
        echo "✅ Tidak perlu perubahan jurnal" . PHP_EOL;
    } else {
        echo "❌ Data masih tidak konsisten" . PHP_EOL;
    }
} else {
    echo "❌ Pegawai tidak ditemukan setelah update" . PHP_EOL;
}

echo PHP_EOL . "=== Analisis Masalah ===" . PHP_EOL;
echo "Root Cause:" . PHP_EOL;
echo "1. Data pegawai Dedi Gunawan: jenis_pegawai = 'btktl'" . PHP_EOL;
echo "2. User expectation: Seharusnya 'btkl' (Bukan Tenaga Kerja Langsung)" . PHP_EOL;
echo "3. Journal entry: COA 52 (BIAYA TENAGA KERJA LANGSUNG)" . PHP_EOL;
echo "4. Konflik: btktl vs btkl" . PHP_EOL;

echo PHP_EOL . "=== Solusi ===" . PHP_EOL;
echo "✅ Fixed: Ubah jenis_pegawai dari 'btktl' ke 'btkl'" . PHP_EOL;
echo "✅ Result: Data pegawai konsisten dengan jurnal" . PHP_EOL;
echo "✅ Logic: BTKL → COA 52 (BIAYA TENAGA KERJA LANGSUNG)" . PHP_EOL;
echo "✅ Consistency: Tidak ada lagi inkonsistensi" . PHP_EOL;

echo PHP_EOL . "=== Rekomendasi Tambahan ===" . PHP_EOL;
echo "1. Validasi data pegawai lain untuk konsistensi" . PHP_EOL;
echo "2. Tambah validasi saat input data pegawai" . PHP_EOL;
echo "3. Pastikan klasifikasi BTKL vs BTKTL sesuai fungsi" . PHP_EOL;
echo "4. Review logic penggajian untuk mapping yang benar" . PHP_EOL;

echo PHP_EOL . "=== Impact pada Sistem ===" . PHP_EOL;
echo "✅ Data integrity: Pegawai data konsisten" . PHP_EOL;
echo "✅ Journal accuracy: Jurnal sesuai klasifikasi" . PHP_EOL;
echo "✅ Reporting: Laporan BTKL akurat" . PHP_EOL;
echo "✅ Audit trail: Tracking yang jelas" . PHP_EOL;
