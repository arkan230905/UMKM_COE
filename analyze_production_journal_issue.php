<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ANALISIS MASALAH JURNAL PRODUKSI ===" . PHP_EOL;
echo PHP_EOL . "User melihat: Transfer WIP ke Barang Jadi MUNCUL PERTAMA" . PHP_EOL;
echo "User inginkan: Alokasi BTKL & BOP MUNCUL PERTAMA" . PHP_EOL;
echo PHP_EOL . "Database: Sudah BENAR (alokasi dulu, baru transfer)" . PHP_EOL;

echo PHP_EOL . "=== ROOT CAUSE ===" . PHP_EOL;
echo "Masalah: UI sorting atau query yang salah" . PHP_EOL;
echo "Database: Sequence benar" . PHP_EOL;
echo "UI: Menampilkan dengan urutan salah" . PHP_EOL;

echo PHP_EOL . "=== INVESTIGASI SEBELUMNYA ===" . PHP_EOL;

// Cek apakah ada masalah dengan query UI
echo "Mengecek kemungkinan masalah..." . PHP_EOL;

// 1. Cek apakah ada multiple entries dengan timestamp berbeda
$timestampIssues = DB::table('jurnal_umum')
    ->whereDate('tanggal', '2026-04-17')
    ->where(function($query) {
        $query->where('keterangan', 'like', '%Transfer WIP ke Barang Jadi%')
               ->orWhere('keterangan', 'like', '%Alokasi BTKL%')
               ->orWhere('keterangan', 'like', '%Alokasi BOP%');
    })
    ->select('keterangan', 'created_at', 'id')
    ->orderBy('created_at')
    ->get();

echo "Timestamp issues:" . PHP_EOL;
foreach ($timestampIssues as $issue) {
    echo "- " . $issue->keterangan . " | " . $issue->created_at . " | ID: " . $issue->id . PHP_EOL;
}

// 2. Cek apakah ada sorting yang salah di UI
echo PHP_EOL . "Kemungkinan penyebab:" . PHP_EOL;
echo "1. UI sorting by ID (bukan logika produksi)" . PHP_EOL;
echo "2. UI grouping yang salah" . PHP_EOL;
echo "3. Cache browser yang menampilkan data lama" . PHP_EOL;
echo "4. Query UI yang berbeda dengan database" . PHP_EOL;

echo PHP_EOL . "=== SOLUSI ===" . PHP_EOL;

echo "SOLUSI YANG DIBUTUHKAN:" . PHP_EOL;
echo "1. Database sudah BENAR - jangan diubah!" . PHP_EOL;
echo "2. Cek UI query di controller/view" . PHP_EOL;
echo "3. Pastikan UI sorting berdasarkan logika produksi" . PHP_EOL;
echo "4. Clear browser cache" . PHP_EOL;

echo PHP_EOL . "=== REKOMENDASI TINDAKAN ===" . PHP_EOL;

echo "Langkah-langkah:" . PHP_EOL;
echo "1. Cari file controller/view Jurnal Umum" . PHP_EOL;
echo "2. Periksa query yang digunakan" . PHP_EOL;
echo "3. Pastikan sorting sesuai sequence produksi" . PHP_EOL;
echo "4. Test dengan refresh browser" . PHP_EOL;

echo PHP_EOL . "=== CURRENT STATUS ===" . PHP_EOL;

echo "Database: ✅ BENAR (alokasi dulu, transfer setelahnya)" . PHP_EOL;
echo "UI: ❌ SALAH (transfer muncul pertama)" . PHP_EOL;
echo "Action: Perbaiki UI query/sorting" . PHP_EOL;

echo PHP_EOL . "=== CATATAN ===" . PHP_EOL;
echo "- JANGAN ubah data di database" . PHP_EOL;
echo "- Fokus pada perbaikan tampilan UI" . PHP_EOL;
echo "- Database sequence sudah correct" . PHP_EOL;
echo "- User melihat UI yang salah sorting" . PHP_EOL;
