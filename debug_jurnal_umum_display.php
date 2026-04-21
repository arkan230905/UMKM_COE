<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Jurnal Umum Display Issue ===" . PHP_EOL;

// Check the exact query that might be used in Jurnal Umum page
echo PHP_EOL . "Checking all possible Dedi Gunawan entries..." . PHP_EOL;

// Check with different patterns
$patterns = [
    '%Dedi Gunawan%',
    '%Penggajian Dedi Gunawan%',
    '%Pembayaran Gaji Dedi Gunawan%'
];

foreach ($patterns as $pattern) {
    echo PHP_EOL . "Pattern: " . $pattern . PHP_EOL;
    
    $entries = DB::table('jurnal_umum')
        ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
        ->where('jurnal_umum.keterangan', 'like', $pattern)
        ->whereDate('jurnal_umum.tanggal', '2026-04-26')
        ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
        ->orderBy('jurnal_umum.id')
        ->get();
    
    echo "Count: " . $entries->count() . PHP_EOL;
    foreach ($entries as $entry) {
        echo sprintf(
            "ID: %d | %s | %s | %s | %s | %s",
            $entry->id,
            $entry->keterangan,
            $entry->kode_akun,
            $entry->nama_akun,
            number_format($entry->debit, 0),
            number_format($entry->kredit, 0)
        ) . PHP_EOL;
    }
}

echo PHP_EOL . "=== Check for Other Tables ===" . PHP_EOL;

// Check if there are entries in other tables that might be displayed
echo "Checking journal_entries table..." . PHP_EOL;

$journalEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.memo', 'like', '%Dedi Gunawan%')
    ->whereDate('journal_entries.tanggal', '2026-04-26')
    ->select('journal_entries.id', 'journal_entries.memo', 'journal_lines.debit', 'journal_lines.credit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('journal_entries.id')
    ->get();

echo "Journal Entries Count: " . $journalEntries->count() . PHP_EOL;
foreach ($journalEntries as $entry) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s",
        $entry->id,
        $entry->memo,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->credit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Check for Empty Keterangan ===" . PHP_EOL;

// Check for entries with empty keterangan that might be shown
$emptyKeterangan = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', '')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Dedi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Empty/Dedi-related entries: " . $emptyKeterangan->count() . PHP_EOL;
foreach ($emptyKeterangan as $entry) {
    echo sprintf(
        "ID: %d | '%s' | %s | %s | %s | %s",
        $entry->id,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Find All 2026-04-26 Entries with COA 54 or 112 ===" . PHP_EOL;

// Check all entries on that date with the relevant COAs
$allRelevantEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "All relevant entries: " . $allRelevantEntries->count() . PHP_EOL;
foreach ($allRelevantEntries as $entry) {
    echo sprintf(
        "ID: %d | '%s' | %s | %s | %s | %s",
        $entry->id,
        $entry->keterangan,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->kredit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Database Status: " . ($allEntries->count() === 2 ? "CORRECT" : "HAS ISSUES") . PHP_EOL;
echo "If database is correct but UI shows double, the issue might be:" . PHP_EOL;
echo "1. UI query is different from our check" . PHP_EOL;
echo "2. Cache issue - need to clear browser cache" . PHP_EOL;
echo "3. Multiple data sources being combined" . PHP_EOL;
echo "4. Date filtering issue" . PHP_EOL;
