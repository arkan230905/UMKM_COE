<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Dedi Gunawan COA Usage ===" . PHP_EOL;

// Check Dedi Gunawan's journal entries in database
echo PHP_EOL . "Mengecek jurnal Dedi Gunawan di database..." . PHP_EOL;

$dediJournals = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereDate('journal_entries.tanggal', '>=', '2026-04-01')
    ->whereDate('journal_entries.tanggal', '<=', '2026-04-30')
    ->select('journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo', 'coas.kode_akun', 'coas.nama_akun', 'pegawais.nama as nama_pegawai', 'pegawais.jabatan', 'pegawais.jenis_pegawai')
    ->orderBy('journal_entries.tanggal')
    ->get();

echo "Jurnal Dedi Gunawan (April 2026):" . PHP_EOL;
foreach ($dediJournals as $journal) {
    echo sprintf(
        "%s | %s | %s | %s | %s | %s | %s | %s",
        $journal->tanggal,
        $journal->kode_akun,
        $journal->nama_akun,
        number_format($journal->debit, 0),
        number_format($journal->credit, 0),
        $journal->memo,
        $journal->nama_pegawai,
        $journal->jabatan
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analisis ===" . PHP_EOL;

// Analyze COA usage
$coa52Count = 0;
$coa54Count = 0;

foreach ($dediJournals as $journal) {
    if ($journal->kode_akun === '52') {
        $coa52Count++;
    } elseif ($journal->kode_akun === '54') {
        $coa54Count++;
    }
}

echo "COA Usage untuk Dedi Gunawan:" . PHP_EOL;
echo "- COA 52 (BTKL): " . $coa52Count . " transaksi" . PHP_EOL;
echo "- COA 54 (BTKTL): " . $coa54Count . " transaksi" . PHP_EOL;

if ($coa52Count > 0 && $coa54Count === 0) {
    echo PHP_EOL . "❌ MASALAH: Dedi Gunawan masih menggunakan COA 52!" . PHP_EOL;
    echo "- Seharusnya: COA 54 (BTKTL) untuk Bagian Gudang" . PHP_EOL;
    echo "- Status: PERLU DIPERBAIKI" . PHP_EOL;
} elseif ($coa54Count > 0 && $coa52Count === 0) {
    echo PHP_EOL . "✅ BENAR: Dedi Gunawan sudah menggunakan COA 54!" . PHP_EOL;
    echo "- Status: SUDAH DIPERBAIKI" . PHP_EOL;
} else {
    echo PHP_EOL . "⚠️  MIXED: Dedi Gunawan menggunakan kedua COA" . PHP_EOL;
    echo "- Status: PERLU DIVERIFIKASI" . PHP_EOL;
}

echo PHP_EOL . "=== Rekomendasi ===" . PHP_EOL;

if ($coa52Count > 0 && $coa54Count === 0) {
    echo "🔧 ACTION REQUIRED:" . PHP_EOL;
    echo "1. Pastikan PenggajianController sudah diperbaiki" . PHP_EOL;
    echo "2. Restart aplikasi/web server" . PHP_EOL;
    echo "3. Test penggajian baru untuk Dedi Gunawan" . PHP_EOL;
    echo "4. Verifikasi jurnal menggunakan COA 54" . PHP_EOL;
} else {
    echo "✅ STATUS: Tidak ada action yang diperlukan" . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Total Jurnal Dedi Gunawan: " . ($coa52Count + $coa54Count) . PHP_EOL;
echo "Status: " . ($coa54Count > 0 ? "✅ SUDAH BENAR" : ($coa52Count > 0 ? "❌ MASIH SALAH" : "⚠️ PERLU DIVERIFIKASI")) . PHP_EOL;
