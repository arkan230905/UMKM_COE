<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Dedi Gunawan Journal Issue ===" . PHP_EOL;

// Check Dedi Gunawan's penggajian status
echo PHP_EOL . "Mengecek status penggajian Dedi Gunawan..." . PHP_EOL;

$penggajian = DB::table('penggajians')
    ->join('pegawais', 'penggajians.pegawai_id', '=', 'pegawais.id')
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereMonth('penggajians.tanggal_penggajian', 4)
    ->whereYear('penggajians.tanggal_penggajian', 2026)
    ->select('penggajians.id', 'penggajians.tanggal_penggajian', 'penggajians.total_gaji', 'penggajians.status_pembayaran', 'pegawais.nama', 'pegawais.jabatan', 'pegawais.jenis_pegawai')
    ->orderBy('penggajians.tanggal_penggajian')
    ->first();

if ($penggajian) {
    echo "Data Penggajian Dedi Gunawan:" . PHP_EOL;
    echo "- ID: " . $penggajian->id . PHP_EOL;
    echo "- Tanggal: " . $penggajian->tanggal_penggajian . PHP_EOL;
    echo "- Total Gaji: Rp " . number_format($penggajian->total_gaji, 0) . PHP_EOL;
    echo "- Status: " . $penggajian->status_pembayaran . PHP_EOL;
    echo "- Nama: " . $penggajian->nama . PHP_EOL;
    echo "- Jabatan: " . $penggajian->jabatan . PHP_EOL;
    echo "- Jenis Pegawai: " . $penggajian->jenis_pegawai . PHP_EOL;
    
    echo PHP_EOL . "Status Pembayaran: " . $penggajian->status_pembayaran . PHP_EOL;
    
    if ($penggajian->status_pembayaran === 'Sudah Dibayar') {
        echo "✅ Penggajian SUDAH DIBAYAR" . PHP_EOL;
        echo "✅ Seharusnya jurnal sudah dibuat" . PHP_EOL;
        
        // Check if journal entries exist
        $journalCount = DB::table('journal_entries')
            ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
            ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
            ->where('journal_entries.ref_type', 'penggajian')
            ->where('journal_entries.ref_id', $penggajian->id)
            ->count();
        
        echo "- Jurnal yang ditemukan: " . $journalCount . PHP_EOL;
        
        if ($journalCount === 0) {
            echo PHP_EOL . "❌ MASALAH: Penggajian SUDAH DIBAYAR tapi JURNAL TIDAK DIBUAT!" . PHP_EOL;
            echo PHP_EOL . "Kemungkinan:" . PHP_EOL;
            echo "1. Error di createJournalEntry() function" . PHP_EOL;
            echo "2. Error di createJournalEntryModern() function" . PHP_EOL;
            echo "3. Exception yang tidak ditangkap" . PHP_EOL;
            echo "4. Transaction rollback terjadi" . PHP_EOL;
            
            echo PHP_EOL . "=== Debugging Function Calls ===" . PHP_EOL;
            
            // Check if the functions are being called
            echo "Mengecek apakah journal functions dipanggil..." . PHP_EOL;
            
            // Look for the function calls in the PenggajianController
            $controllerFile = __DIR__ . '/app/Http/Controllers/PenggajianController.php';
            $controllerContent = file_get_contents($controllerFile);
            
            if (strpos($controllerContent, 'createJournalEntry($penggajian, $pegawai)') !== false) {
                echo "✅ createJournalEntry() dipanggil" . PHP_EOL;
            } else {
                echo "❌ createJournalEntry() TIDAK dipanggil" . PHP_EOL;
            }
            
            if (strpos($controllerContent, 'createJournalEntryModern($penggajian, $pegawai)') !== false) {
                echo "✅ createJournalEntryModern() dipanggil" . PHP_EOL;
            } else {
                echo "❌ createJournalEntryModern() TIDAK dipanggil" . PHP_EOL;
            }
            
            echo PHP_EOL . "=== Rekomendasi ===" . PHP_EOL;
            echo "1. Cek error log di Laravel storage/logs" . PHP_EOL;
            echo "2. Tambah try-catch di createJournalEntry functions" . PHP_EOL;
            echo "3. Verifikasi database connection" . PHP_EOL;
            echo "4. Test pembuatan jurnal manual" . PHP_EOL;
            
        } else {
            echo "✅ Jurnal sudah dibuat (" . $journalCount . " entries)" . PHP_EOL;
        }
        
    } else {
        echo "❌ Penggajian BELUM DIBAYAR" . PHP_EOL;
        echo "Status: " . $penggajian->status_pembayaran . PHP_EOL;
    }
    
} else {
    echo "❌ Data penggajian Dedi Gunawan tidak ditemukan" . PHP_EOL;
}

echo PHP_EOL . "=== Check Journal Entries for Dedi Gunawan ===" . PHP_EOL;

// Check all journal entries for Dedi Gunawan
$allJournals = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereMonth('journal_entries.tanggal', 4)
    ->whereYear('journal_entries.tanggal', 2026)
    ->select('journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo', 'coas.kode_akun', 'coas.nama_akun', 'journal_entries.ref_type')
    ->orderBy('journal_entries.tanggal')
    ->get();

echo "Semua Jurnal Dedi Gunawan (April 2026):" . PHP_EOL;
foreach ($allJournals as $journal) {
    echo sprintf(
        "%s | %s | %s | %s | %s | %s | %s",
        $journal->tanggal,
        $journal->kode_akun,
        $journal->nama_akun,
        number_format($journal->debit, 0),
        number_format($journal->credit, 0),
        $journal->memo,
        $journal->ref_type
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Total Jurnal Dedi Gunawan: " . $allJournals->count() . PHP_EOL;
echo "Status: " . ($allJournals->count() === 0 ? "❌ TIDAK ADA JURNAL" : "✅ ADA JURNAL") . PHP_EOL;

if ($allJournals->count() === 0 && $penggajian && $penggajian->status_pembayaran === 'Sudah Dibayar') {
    echo PHP_EOL . "🔧 ACTION REQUIRED:" . PHP_EOL;
    echo "1. Debug PenggajianController store() method" . PHP_EOL;
    echo "2. Cek apakah createJournalEntry functions dipanggil" . PHP_EOL;
    echo "3. Cek Laravel error logs" . PHP_EOL;
    echo "4. Test pembuatan jurnal manual" . PHP_EOL;
}
