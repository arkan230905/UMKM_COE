<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Dedi Gunawan Double Journal ===" . PHP_EOL;

// Check all journal entries for Dedi Gunawan
echo PHP_EOL . "Mengecek semua jurnal Dedi Gunawan..." . PHP_EOL;

$allJournals = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
    ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
    ->whereDate('journal_entries.tanggal', '2026-04-26')
    ->select('journal_entries.id', 'journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo', 'coas.kode_akun', 'coas.nama_akun', 'journal_entries.ref_type', 'journal_entries.ref_id')
    ->orderBy('journal_entries.id')
    ->get();

echo "Semua Jurnal Dedi Gunawan (26/04/2026):" . PHP_EOL;
foreach ($allJournals as $journal) {
    echo sprintf(
        "ID: %d | %s | %s | %s | %s | %s | %s | %s | %d",
        $journal->id,
        $journal->tanggal,
        $journal->kode_akun,
        $journal->nama_akun,
        number_format($journal->debit, 0),
        number_format($journal->credit, 0),
        $journal->memo,
        $journal->ref_type,
        $journal->ref_id
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Analysis ===" . PHP_EOL;

if ($allJournals->count() > 2) {
    echo "Double jurnal terdeteksi!" . PHP_EOL;
    echo "Total jurnal: " . $allJournals->count() . PHP_EOL;
    echo "Seharusnya: 2 jurnal (debit + credit)" . PHP_EOL;
    
    // Group by journal entry ID
    $journalGroups = [];
    foreach ($allJournals as $journal) {
        $journalGroups[$journal->id][] = $journal;
    }
    
    echo PHP_EOL . "Journal Entry Groups:" . PHP_EOL;
    foreach ($journalGroups as $entryId => $lines) {
        echo "Entry ID " . $entryId . ": " . count($lines) . " lines" . PHP_EOL;
        foreach ($lines as $line) {
            echo "  - " . $line->kode_akun . ": " . number_format($line->debit, 0) . " | " . number_format($line->credit, 0) . PHP_EOL;
        }
    }
    
    // Find the complete journal entry (with both debit and credit)
    $completeEntries = [];
    foreach ($journalGroups as $entryId => $lines) {
        $hasDebit = false;
        $hasCredit = false;
        foreach ($lines as $line) {
            if ($line->debit > 0) $hasDebit = true;
            if ($line->credit > 0) $hasCredit = true;
        }
        if ($hasDebit && $hasCredit) {
            $completeEntries[] = $entryId;
        }
    }
    
    echo PHP_EOL . "Complete Journal Entries (with debit + credit):" . PHP_EOL;
    foreach ($completeEntries as $entryId) {
        echo "- Entry ID: " . $entryId . PHP_EOL;
    }
    
    // Keep only the first complete entry, delete others
    if (count($completeEntries) > 1) {
        echo PHP_EOL . "=== Deleting Duplicate Entries ===" . PHP_EOL;
        
        $keepEntry = array_shift($completeEntries);
        $deleteEntries = $completeEntries;
        
        echo "Keeping Entry ID: " . $keepEntry . PHP_EOL;
        echo "Deleting Entries: " . implode(', ', $deleteEntries) . PHP_EOL;
        
        foreach ($deleteEntries as $entryId) {
            echo PHP_EOL . "Menghapus Entry ID: " . $entryId . PHP_EOL;
            
            // Delete journal lines first
            $deletedLines = DB::table('journal_lines')->where('journal_entry_id', $entryId)->delete();
            echo "  - " . $deletedLines . " journal lines dihapus" . PHP_EOL;
            
            // Delete journal entry
            $deletedEntry = DB::table('journal_entries')->where('id', $entryId)->delete();
            echo "  - Journal entry ID " . $entryId . " dihapus" . PHP_EOL;
        }
        
        echo PHP_EOL . "=== Also Check Jurnal Umum Table ===" . PHP_EOL;
        
        // Check jurnal_umum table for duplicates
        $jurnalUmumEntries = DB::table('jurnal_umum')
            ->where('tipe_referensi', 'penggajian')
            ->where('referensi', 5) // Dedi Gunawan's penggajian ID
            ->whereDate('tanggal', '2026-04-26')
            ->get();
        
        echo "Jurnal Umum entries: " . $jurnalUmumEntries->count() . PHP_EOL;
        
        if ($jurnalUmumEntries->count() > 2) {
            echo "Deleting duplicate jurnal_umum entries..." . PHP_EOL;
            
            // Keep first 2 entries, delete the rest
            $keepCount = 0;
            foreach ($jurnalUmumEntries as $index => $entry) {
                if ($keepCount < 2) {
                    $keepCount++;
                    continue;
                }
                
                DB::table('jurnal_umum')->where('id', $entry->id)->delete();
                echo "  - Deleted jurnal_umum ID: " . $entry->id . PHP_EOL;
            }
        }
        
        echo PHP_EOL . "=== Verification ===" . PHP_EOL;
        
        // Check remaining journals
        $remainingJournals = DB::table('journal_entries')
            ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
            ->join('pegawais', 'journal_lines.memo', 'like', DB::raw("CONCAT('%', pegawais.nama, '%')"))
            ->where('pegawais.nama', 'like', '%Dedi Gunawan%')
            ->whereDate('journal_entries.tanggal', '2026-04-26')
            ->select('journal_entries.id', 'journal_lines.debit', 'journal_lines.credit', 'coas.kode_akun', 'coas.nama_akun')
            ->orderBy('journal_entries.id')
            ->get();
        
        echo "Remaining Journals:" . PHP_EOL;
        foreach ($remainingJournals as $journal) {
            echo sprintf(
                "ID: %d | %s | %s | %s | %s | %s",
                $journal->id,
                $journal->kode_akun,
                $journal->nama_akun,
                number_format($journal->debit, 0),
                number_format($journal->credit, 0)
            ) . PHP_EOL;
        }
        
        echo PHP_EOL . "Total Remaining: " . $remainingJournals->count() . PHP_EOL;
        echo "Status: " . ($remainingJournals->count() === 2 ? "FIXED" : "STILL HAS ISSUES") . PHP_EOL;
        
    } else {
        echo PHP_EOL . "No duplicate complete entries found." . PHP_EOL;
    }
    
} else {
    echo "No double journal detected." . PHP_EOL;
    echo "Total jurnal: " . $allJournals->count() . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Action: Delete duplicate journal entries" . PHP_EOL;
echo "Result: Keep only one complete journal entry" . PHP_EOL;
echo "Status: " . ($remainingJournals->count() === 2 ? "SUCCESS" : "NEEDS ATTENTION") . PHP_EOL;
