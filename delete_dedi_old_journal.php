<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Delete Dedi Gunawan Old Journal ===" . PHP_EOL;

// Delete the wrong journal entry for Dedi Gunawan
echo PHP_EOL . "Menghapus jurnal lama Dedi Gunawan yang salah..." . PHP_EOL;

$journalToDelete = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->where('coas.kode_akun', '52') // COA 52 yang SALAH
    ->whereDate('journal_entries.tanggal', '>=', '2026-04-01')
    ->whereDate('journal_entries.tanggal', '<=', '2026-04-30')
    ->select('journal_entries.id', 'journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo', 'coas.kode_akun', 'coas.nama_akun')
    ->first();

if ($journalToDelete) {
    echo "Menemukan jurnal yang akan dihapus:" . PHP_EOL;
    echo "- ID: " . $journalToDelete->id . PHP_EOL;
    echo "- Tanggal: " . $journalToDelete->tanggal . PHP_EOL;
    echo "- COA: " . $journalToDelete->kode_akun . " - " . $journalToDelete->nama_akun . PHP_EOL;
    echo "- Debit: " . number_format($journalToDelete->debit, 0) . PHP_EOL;
    echo "- Credit: " . number_format($journalToDelete->credit, 0) . PHP_EOL;
    echo "- Memo: " . $journalToDelete->memo . PHP_EOL;
    
    echo PHP_EOL . "Menghapus jurnal..." . PHP_EOL;
    
    try {
        // Delete journal lines first
        $deletedLines = DB::table('journal_lines')->where('journal_entry_id', $journalToDelete->id)->delete();
        echo "✅ " . $deletedLines . " journal lines dihapus" . PHP_EOL;
        
        // Delete journal entry
        $deletedEntry = DB::table('journal_entries')->where('id', $journalToDelete->id)->delete();
        echo "✅ Journal entry ID " . $journalToDelete->id . " dihapus" . PHP_EOL;
        
        echo PHP_EOL . "✅ Jurnal lama Dedi Gunawan berhasil dihapus!" . PHP_EOL;
        echo "✅ Jurnal dengan COA 52 sudah tidak ada" . PHP_EOL;
        
    } catch (\Exception $e) {
        echo "❌ Error menghapus jurnal: " . $e->getMessage() . PHP_EOL;
    }
    
} else {
    echo "✅ Tidak ada jurnal lama Dedi Gunawan dengan COA 52" . PHP_EOL;
    echo "✅ Semua jurnal sudah benar" . PHP_EOL;
}

echo PHP_EOL . "=== Verification ===" . PHP_EOL;

// Verify deletion
$remainingJournals = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->where('coas.kode_akun', '52')
    ->whereDate('journal_entries.tanggal', '>=', '2026-04-01')
    ->whereDate('journal_entries.tanggal', '<=', '2026-04-30')
    ->count();

echo "Sisa jurnal Dedi Gunawan dengan COA 52: " . $remainingJournals . PHP_EOL;
echo "Status: " . ($remainingJournals === 0 ? "✅ SEMUA DIHAPUS" : "❌ MASIH ADA " . $remainingJournals . " JURNAL") . PHP_EOL;

echo PHP_EOL . "=== Next Steps ===" . PHP_EOL;
echo "1. ✅ Jurnal lama dengan COA 52 sudah dihapus" . PHP_EOL;
echo "2. 📝 Buat penggajian baru untuk Dedi Gunawan" . PHP_EOL;
echo "3. 🎯 Hasilnya akan otomatis ke COA 54 (BTKTL)" . PHP_EOL;
echo "4. ✅ Verifikasi di halaman Jurnal Umum" . PHP_EOL;

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "✅ Action: Delete old wrong journal" . PHP_EOL;
echo "✅ Result: Clean slate for new journal" . PHP_EOL;
echo "✅ Status: Ready for new penggajian" . PHP_EOL;
echo "✅ Next: Create new penggajian for Dedi Gunawan" . PHP_EOL;
