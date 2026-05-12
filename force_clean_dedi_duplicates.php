<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Force Clean Dedi Gunawan Duplicates ===" . PHP_EOL;

// Get ALL entries that might be related to Dedi Gunawan on 2026-04-26
echo PHP_EOL . "Finding ALL Dedi Gunawan related entries..." . PHP_EOL;

// Check jurnal_umum table
$jurnalUmumEntries = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->whereDate('jurnal_umum.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('jurnal_umum.keterangan', 'like', '%Dedi%')
               ->orWhere('jurnal_umum.keterangan', 'like', '%Gaji%')
               ->orWhere('jurnal_umum.keterangan', '')
               ->orWhereNull('jurnal_umum.keterangan')
               ->orWhere('jurnal_umum.keterangan', '-');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->select('jurnal_umum.id', 'jurnal_umum.keterangan', 'jurnal_umum.debit', 'jurnal_umum.kredit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('jurnal_umum.id')
    ->get();

echo "Jurnal Umum entries found: " . $jurnalUmumEntries->count() . PHP_EOL;
foreach ($jurnalUmumEntries as $entry) {
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

// Check journal_entries table
$journalEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->whereDate('journal_entries.tanggal', '2026-04-26')
    ->where(function($query) {
        $query->where('journal_entries.memo', 'like', '%Dedi%')
               ->orWhere('journal_lines.memo', 'like', '%Dedi%')
               ->orWhere('journal_lines.memo', 'like', '%Gaji%');
    })
    ->whereIn('coas.kode_akun', ['54', '112'])
    ->select('journal_entries.id as entry_id', 'journal_entries.memo', 'journal_lines.id', 'journal_lines.debit', 'journal_lines.credit', 'coas.kode_akun', 'coas.nama_akun')
    ->orderBy('journal_entries.id')
    ->get();

echo PHP_EOL . "Journal Entries found: " . $journalEntries->count() . PHP_EOL;
foreach ($journalEntries as $entry) {
    echo sprintf(
        "Entry ID: %d | Line ID: %d | '%s' | %s | %s | %s | %s",
        $entry->entry_id,
        $entry->id,
        $entry->memo,
        $entry->kode_akun,
        $entry->nama_akun,
        number_format($entry->debit, 0),
        number_format($entry->credit, 0)
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

$totalJurnalUmum = $jurnalUmumEntries->count();
$totalJournalEntries = $journalEntries->count();

echo "Jurnal Umum: " . $totalJurnalUmum . " entries (Expected: 2)" . PHP_EOL;
echo "Journal Entries: " . $totalJournalEntries . " entries (Expected: 2)" . PHP_EOL;

if ($totalJurnalUmum > 2 || $totalJournalEntries > 2) {
    echo PHP_EOL . "DUPLICATES DETECTED! Cleaning up..." . PHP_EOL;
    
    // Clean jurnal_umum table
    if ($totalJurnalUmum > 2) {
        echo PHP_EOL . "Cleaning jurnal_umum table..." . PHP_EOL;
        
        // Keep only the first 2 entries that form a complete journal
        $entriesArray = $jurnalUmumEntries->toArray();
        $keepIds = [];
        $deleteIds = [];
        
        // Find one debit and one credit entry to keep
        $debitFound = false;
        $creditFound = false;
        
        foreach ($entriesArray as $entry) {
            if (!$debitFound && $entry->debit > 0) {
                $keepIds[] = $entry->id;
                $debitFound = true;
                continue;
            }
            if (!$creditFound && $entry->kredit > 0) {
                $keepIds[] = $entry->id;
                $creditFound = true;
                continue;
            }
            $deleteIds[] = $entry->id;
        }
        
        echo "Keeping IDs: " . implode(', ', $keepIds) . PHP_EOL;
        echo "Deleting IDs: " . implode(', ', $deleteIds) . PHP_EOL;
        
        foreach ($deleteIds as $id) {
            DB::table('jurnal_umum')->where('id', $id)->delete();
            echo "Deleted jurnal_umum ID: " . $id . PHP_EOL;
        }
    }
    
    // Clean journal_entries table
    if ($totalJournalEntries > 2) {
        echo PHP_EOL . "Cleaning journal_entries table..." . PHP_EOL;
        
        // Group by entry_id and keep only one complete entry
        $entryGroups = [];
        foreach ($journalEntries as $entry) {
            $entryGroups[$entry->entry_id][] = $entry;
        }
        
        $keepEntryIds = [];
        $deleteEntryIds = [];
        
        foreach ($entryGroups as $entryId => $lines) {
            $hasDebit = false;
            $hasCredit = false;
            
            foreach ($lines as $line) {
                if ($line->debit > 0) $hasDebit = true;
                if ($line->credit > 0) $hasCredit = true;
            }
            
            if ($hasDebit && $hasCredit) {
                if (empty($keepEntryIds)) {
                    $keepEntryIds[] = $entryId;
                } else {
                    $deleteEntryIds[] = $entryId;
                }
            }
        }
        
        echo "Keeping Entry IDs: " . implode(', ', $keepEntryIds) . PHP_EOL;
        echo "Deleting Entry IDs: " . implode(', ', $deleteEntryIds) . PHP_EOL;
        
        foreach ($deleteEntryIds as $entryId) {
            // Delete journal lines first
            $deletedLines = DB::table('journal_lines')->where('journal_entry_id', $entryId)->delete();
            // Delete journal entry
            DB::table('journal_entries')->where('id', $entryId)->delete();
            echo "Deleted journal_entry ID: " . $entryId . " (and " . $deletedLines . " lines)" . PHP_EOL;
        }
    }
    
} else {
    echo "No duplicates detected in database." . PHP_EOL;
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

echo "Final Jurnal Umum: " . $finalJurnalUmum . " entries" . PHP_EOL;
echo "Final Journal Entries: " . $finalJournalEntries . " entries" . PHP_EOL;

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Status: " . ($finalJurnalUmum === 2 && $finalJournalEntries === 2 ? "SUCCESS" : "NEEDS ATTENTION") . PHP_EOL;
echo "Expected: 2 entries per table" . PHP_EOL;
echo "Actual: " . $finalJurnalUmum . " (jurnal_umum) | " . $finalJournalEntries . " (journal_entries)" . PHP_EOL;

if ($finalJurnalUmum === 2 && $finalJournalEntries === 2) {
    echo PHP_EOL . "RECOMMENDATIONS:" . PHP_EOL;
    echo "1. Clear browser cache and refresh Jurnal Umum page" . PHP_EOL;
    echo "2. Check if UI is reading from correct table" . PHP_EOL;
    echo "3. Verify date filters are correct" . PHP_EOL;
    echo "4. If still showing double, restart web server" . PHP_EOL;
}
