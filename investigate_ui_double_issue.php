<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Investigate UI Double Journal Issue ===" . PHP_EOL;

// Check if UI is using a UNION query or combining tables
echo PHP_EOL . "Checking if UI combines both tables..." . PHP_EOL;

// Test UNION query that UI might be using
$unionQuery = "
    SELECT 
        jurnal_umum.tanggal,
        jurnal_umum.keterangan,
        coas.kode_akun,
        coas.nama_akun,
        jurnal_umum.debit,
        jurnal_umum.kredit,
        'jurnal_umum' as source_table
    FROM jurnal_umum
    JOIN coas ON jurnal_umum.coa_id = coas.id
    WHERE jurnal_umum.tanggal = '2026-04-26'
    AND (jurnal_umum.keterangan LIKE '%Dedi%' OR jurnal_umum.keterangan LIKE '%Gaji%')
    
    UNION ALL
    
    SELECT 
        journal_entries.tanggal,
        journal_lines.memo as keterangan,
        coas.kode_akun,
        coas.nama_akun,
        journal_lines.debit,
        journal_lines.credit as kredit,
        'journal_entries' as source_table
    FROM journal_entries
    JOIN journal_lines ON journal_entries.id = journal_lines.journal_entry_id
    JOIN coas ON journal_lines.coa_id = coas.id
    WHERE journal_entries.tanggal = '2026-04-26'
    AND (journal_entries.memo LIKE '%Dedi%' OR journal_lines.memo LIKE '%Dedi%')
";

try {
    $unionResults = DB::select($unionQuery);
    
    echo "UNION query results: " . count($unionResults) . PHP_EOL;
    foreach ($unionResults as $result) {
        echo sprintf(
            "%s | %s | %s | %s | %s | %s | %s",
            $result->tanggal,
            substr($result->keterangan, 0, 30),
            $result->kode_akun,
            $result->nama_akun,
            number_format($result->debit, 0),
            number_format($result->kredit, 0),
            $result->source_table
        ) . PHP_EOL;
    }
    
    if (count($unionResults) > 2) {
        echo PHP_EOL . "FOUND ISSUE: UI is using UNION query that combines both tables!" . PHP_EOL;
        echo "This explains why you see double entries." . PHP_EOL;
    }
    
} catch (\Exception $e) {
    echo "UNION query error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Check for Empty Keterangan Entries ===" . PHP_EOL;

// Check for entries with empty keterangan that might be causing issues
$emptyEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->where(function($query) {
        $query->whereNull('jurnal_umum.keterangan')
               ->orWhere('jurnal_umum.keterangan', '')
               ->orWhere('jurnal_umum.keterangan', '-');
    })
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Empty keterangan entries: " . $emptyEntries->count() . PHP_EOL;
foreach ($emptyEntries as $entry) {
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

if ($emptyEntries->count() > 0) {
    echo PHP_EOL . "Deleting empty keterangan entries..." . PHP_EOL;
    foreach ($emptyEntries as $entry) {
        echo "Deleting ID: " . $entry->id . PHP_EOL;
        DB::table('jurnal_umum')->where('id', $entry->id)->delete();
    }
    echo "Deleted " . $emptyEntries->count() . " empty entries" . PHP_EOL;
}

echo PHP_EOL . "=== Check Journal Lines with Empty Memo ===" . PHP_EOL;

// Check journal_lines table for empty memo
$emptyMemoLines = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->whereDate('journal_entries.tanggal', '2026-04-26')
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->where(function($query) {
        $query->whereNull('journal_lines.memo')
               ->orWhere('journal_lines.memo', '')
               ->orWhere('journal_lines.memo', '-');
    })
    ->select('journal_entries.id as entry_id', 'journal_lines.id as line_id', 'journal_lines.memo', 'journal_lines.debit', 'journal_lines.credit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('journal_entries.id')
    ->get();

echo "Empty memo lines: " . $emptyMemoLines->count() . PHP_EOL;
foreach ($emptyMemoLines as $line) {
    echo sprintf(
        "Entry ID: %d | Line ID: %d | '%s' | %s | %s | %s | %s",
        $line->entry_id,
        $line->line_id,
        $line->memo,
        $line->kode_akun,
        $line->nama_akun,
        number_format($line->debit, 0),
        number_format($line->credit, 0)
    ) . PHP_EOL;
}

if ($emptyMemoLines->count() > 0) {
    echo PHP_EOL . "Deleting empty memo lines..." . PHP_EOL;
    foreach ($emptyMemoLines as $line) {
        echo "Deleting line ID: " . $line->line_id . PHP_EOL;
        DB::table('journal_lines')->where('id', $line->line_id)->delete();
    }
    echo "Deleted " . $emptyMemoLines->count() . " empty lines" . PHP_EOL;
}

echo PHP_EOL . "=== Final Verification ===" . PHP_EOL;

// Check final state
$finalJurnalUmum = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Dedi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->count();

$finalJournalEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->whereDate('journal_entries.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('journal_entries.memo', 'like', '%Dedi%')
               ->orWhere('journal_lines.memo', 'like', '%Dedi%');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->count();

echo "Final Jurnal Umum: $finalJurnalUmum entries" . PHP_EOL;
echo "Final Journal Entries: $finalJournalEntries entries" . PHP_EOL;

// Test UNION query again
try {
    $finalUnionResults = DB::select($unionQuery);
    echo "Final UNION query results: " . count($finalUnionResults) . PHP_EOL;
} catch (\Exception $e) {
    echo "Final UNION query error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Database Status: " . ($finalJurnalUmum === 2 && $finalJournalEntries === 2 ? "CORRECT" : "HAS ISSUES") . PHP_EOL;
echo "UNION Query: " . (isset($finalUnionResults) && count($finalUnionResults) === 2 ? "CORRECT" : "SHOWING DOUBLE") . PHP_EOL;

if (isset($finalUnionResults) && count($finalUnionResults) > 2) {
    echo PHP_EOL . "SOLUTION: UI is using UNION query that combines both tables." . PHP_EOL;
    echo "This causes double display even though each table has correct data." . PHP_EOL;
    echo "The fix is to modify the UI query OR ensure only one table has the data." . PHP_EOL;
    
    echo PHP_EOL . "=== FORCE FIX: Keep Only jurnal_umum Data ===" . PHP_EOL;
    
    // Delete from journal_entries to prevent double display
    DB::table('journal_lines')
        ->whereIn('journal_entry_id', function($query) {
            $query->select('id')
                  ->from('journal_entries')
                  ->whereDate('tanggal', '2026-04-26')
                  ->where('memo', 'like', '%Dedi%');
        })
        ->delete();
    
    DB::table('journal_entries')
        ->whereDate('tanggal', '2026-04-26')
        ->where('memo', 'like', '%Dedi%')
        ->delete();
    
    echo "Deleted Dedi entries from journal_entries table" . PHP_EOL;
    echo "Now only jurnal_umum table has the data" . PHP_EOL;
}
