<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Dedi Gunawan Duplicates Final ===" . PHP_EOL;

// Check both tables for duplicates
echo PHP_EOL . "Checking journal_entries table..." . PHP_EOL;

$journalEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereDate('journal_entries.tanggal', '2026-04-26')
    ->select('journal_entries.id', 'journal_lines.debit', 'journal_lines.credit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('journal_entries.id')
    ->get();

echo "Journal Entries: " . $journalEntries->count() . PHP_EOL;
foreach ($journalEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s",
        $entry->id,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->credit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "Checking jurnal_umum table..." . PHP_EOL;

$jurnalUmum = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->join('pegawais', 'jurnal_umum.keterangan', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->select('jurnal_umum.id', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Jurnal Umum: " . $jurnalUmum->count() . PHP_EOL;
foreach ($jurnalUmum as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s",
        $entry->id,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->credit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

$expectedEntries = 2; // 1 debit + 1 credit per table

if ($journalEntries->count() > $expectedEntries) {
    echo "journal_entries: TOO MANY (" . $journalEntries->count() . ") - Expected: " . $expectedEntries . PHP_EOL;
} else {
    echo "journal_entries: OK (" . $journalEntries->count() . ")" . PHP_EOL;
}

if ($jurnalUmum->count() > $expectedEntries) {
    echo "jurnal_umum: TOO MANY (" . $jurnalUmum->count() . ") - Expected: " . $expectedEntries . PHP_EOL;
    
    // Delete duplicates from jurnal_umum
    echo PHP_EOL . "Deleting duplicates from jurnal_umum..." . PHP_EOL;
    $entriesToDelete = $jurnalUmum->slice($expectedEntries);
    
    foreach ($entriesToDelete as $entry) {
        echo "Deleting ID: " . $entry->id . PHP_EOL;
        DB::table('jurnal_umum')->where('id', $entry->id)->delete();
    }
    
    echo "Deleted " . $entriesToDelete->count() . " entries" . PHP_EOL;
    
} else {
    echo "jurnal_umum: OK (" . $jurnalUmum->count() . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Final Verification ===" . PHP_EOL;

// Re-check after cleanup
$finalJurnalUmum = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->join('pegawais', 'jurnal_umum.keterangan', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->count();

echo "Final jurnal_umum count: " . $finalJurnalUmum . PHP_EOL;

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Status: " . ($finalJurnalUmum === 2 ? "FIXED" : "NEEDS ATTENTION") . PHP_EOL;
echo "Expected: 2 entries (1 debit + 1 credit)" . PHP_EOL;
echo "Actual: " . $finalJurnalUmum . " entries" . PHP_EOL;
