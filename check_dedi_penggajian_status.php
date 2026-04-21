<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Dedi Gunawan Penggajian Status ===" . PHP_EOL;

// Check if Dedi Gunawan has any penggajian records
echo PHP_EOL . "Mengecek data penggajian Dedi Gunawan..." . PHP_EOL;

$penggajianRecords = DB::table('penggajians')
    ->join('pegawais', 'penggajians.pegawai_id', '=', 'pegawais.id')
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereMonth('penggajians.tanggal_penggajian', 4)
    ->whereYear('penggajians.tanggal_penggajian', 2026)
    ->select('penggajians.id', 'penggajians.tanggal_penggajian', 'penggajians.total_gaji', 'penggajians.status_pembayaran', 'pegawais.nama', 'pegawais.jabatan', 'pegawais.jenis_pegawai')
    ->orderBy('penggajians.tanggal_penggajian')
    ->get();

echo "Data Penggajian Dedi Gunawan (April 2026):" . PHP_EOL;
foreach ($penggajianRecords as $penggajian) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s",
        $penggajian->id,
        $penggajian->tanggal_penggajian,
        number_format($penggajian->total_gaji, 0),
        $penggajian->status_pembayaran,
        $penggajian->nama,
        $penggajian->jabatan,
        $penggajian->jenis_pegawai
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analisis ===" . PHP_EOL;

if ($penggajianRecords->count() === 0) {
    echo "❌ TIDAK ADA penggajian Dedi Gunawan untuk April 2026" . PHP_EOL;
    echo "❌ Ini penyebab tidak ada jurnal di Jurnal Umum" . PHP_EOL;
    echo PHP_EOL . "Solusi:" . PHP_EOL;
    echo "1. Buat penggajian baru untuk Dedi Gunawan" . PHP_EOL;
    echo "2. Pastikan data pegawai sudah benar" . PHP_EOL;
    echo "3. Sistem akan otomatis buat jurnal ke COA 54" . PHP_EOL;
} else {
    echo "✅ ADA " . $penggajianRecords->count() . " penggajian Dedi Gunawan" . PHP_EOL;
    
    // Check if any have been processed (have journal entries)
    $processedCount = 0;
    foreach ($penggajianRecords as $penggajian) {
        if ($penggajian->status_pembayaran === 'Sudah Dibayar') {
            $processedCount++;
        }
    }
    
    echo "- Jumlah yang sudah dibayar: " . $processedCount . PHP_EOL;
    
    if ($processedCount > 0) {
        echo "✅ Penggajian sudah diproses" . PHP_EOL;
        echo "✅ Seharusnya ada jurnal di Jurnal Umum" . PHP_EOL;
        
        // Check if journals exist
        $journalCount = DB::table('journal_entries')
            ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
            ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
            ->whereMonth('journal_entries.tanggal', 4)
            ->whereYear('journal_entries.tanggal', 2026)
            ->where('journal_entries.ref_type', 'penggajian')
            ->count();
        
        echo "- Jurnal yang ditemukan: " . $journalCount . PHP_EOL;
        
        if ($journalCount === 0) {
            echo "❌ TAPI JURNAL TIDAK DITEMUKAN!" . PHP_EOL;
            echo "❌ Masalah: Jurnal tidak dibuat saat pembayaran" . PHP_EOL;
            echo "❌ Mungkin ada error di sistem" . PHP_EOL;
        } else {
            echo "✅ Jurnal ditemukan dan seharusnya muncul di Jurnal Umum" . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== Check Current System Status ===" . PHP_EOL;

// Check if there are any issues with journal creation
echo "Mengecek status sistem penggajian..." . PHP_EOL;

// Check if there are any recent errors
$recentErrors = DB::table('error_logs')
    ->where('created_at', '>=', now()->subDays(7))
    ->where('message', 'like', '%penggajian%')
    ->count();

echo "Error logs terkait penggajian (7 hari terakhir): " . $recentErrors . PHP_EOL;

echo PHP_EOL . "=== Rekomendasi ===" . PHP_EOL;

if ($penggajianRecords->count() === 0) {
    echo "🔧 ACTION REQUIRED:" . PHP_EOL;
    echo "1. Buat penggajian baru untuk Dedi Gunawan" . PHP_EOL;
    echo "2. Pastikan data pegawai lengkap" . PHP_EOL;
    echo "3. Test proses pembayaran" . PHP_EOL;
    echo "4. Verifikasi jurnal terbuat" . PHP_EOL;
} else if ($processedCount > 0 && $journalCount === 0) {
    echo "🔧 ACTION REQUIRED:" . PHP_EOL;
    echo "1. Periksa error log sistem" . PHP_EOL;
    echo "2. Debug journal creation process" . PHP_EOL;
    echo "3. Manual create journal jika perlu" . PHP_EOL;
    echo "4. Restart aplikasi/web server" . PHP_EOL;
} else {
    echo "✅ STATUS: Sistem penggajian berfungsi normal" . PHP_EOL;
    echo "✅ Jurnal seharusnya muncul di Jurnal Umum" . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Total Penggajian: " . $penggajianRecords->count() . PHP_EOL;
echo "Status: " . ($penggajianRecords->count() === 0 ? "TIDAK ADA" : ($journalCount === 0 ? "PERLU DEBUG" : "NORMAL")) . PHP_EOL;
echo "Action: " . ($penggajianRecords->count() === 0 ? "BUAT PENGGAJIAN" : ($journalCount === 0 ? "DEBUG JURNAL" : "VERIFIKASI")) . PHP_EOL;
