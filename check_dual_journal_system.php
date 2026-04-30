<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING DUAL JOURNAL SYSTEM FOR HOSTING\n";

// Get SJ-20260430-001
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')->first();

if (!$penjualan) {
    echo "ERROR: Penjualan SJ-20260430-001 not found!\n";
    exit;
}

echo "\n=== CHECKING JOURNAL ENTRIES (JournalEntry MODEL) ===\n";

// Check JournalEntry model (used by UI)
$journalEntries = \App\Models\JournalEntry::where('ref_type', 'sale')
    ->where('ref_id', $penjualan->id)
    ->get();

echo "JournalEntry records: " . $journalEntries->count() . "\n";

foreach ($journalEntries as $entry) {
    echo "Entry ID: " . $entry->id . ", Date: " . $entry->date . ", Memo: " . $entry->memo . "\n";
    
    // Check lines for this entry
    $lines = \App\Models\JournalLine::where('journal_entry_id', $entry->id)
        ->with('coa')
        ->get();
    
    echo "  Lines: " . $lines->count() . "\n";
    foreach ($lines as $line) {
        echo "    - " . $line->coa->kode_akun . " " . $line->coa->nama_akun . ": ";
        echo ($line->debit > 0 ? "Debit Rp " . number_format($line->debit, 0, ',', '.') : "Kredit Rp " . number_format($line->credit, 0, ',', '.'));
        echo "\n";
    }
    echo "---\n";
}

// Check JurnalUmum model (our data)
echo "\n=== CHECKING JURNAL UMUM (JurnalUmum MODEL) ===\n";
$jurnalUmums = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)
    ->with('coa')
    ->get();

echo "JurnalUmum records: " . $jurnalUmums->count() . "\n";

foreach ($jurnalUmums as $jurnal) {
    echo "ID: " . $jurnal->id . ", COA: " . $jurnal->coa->kode_akun . " " . $jurnal->coa->nama_akun . ": ";
    echo ($jurnal->debit > 0 ? "Debit Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit Rp " . number_format($jurnal->kredit, 0, ',', '.'));
    echo "\n";
}

echo "\n=== DIAGNOSIS ===\n";
if ($journalEntries->count() === 0 && $jurnalUmums->count() > 0) {
    echo "ISSUE IDENTIFIED:\n";
    echo "- Data exists in JurnalUmum model\n";
    echo "- But UI uses JournalEntry + JournalLine model\n";
    echo "- Need to migrate data to JournalEntry system\n";
    
    echo "\n=== SOLUTION: MIGRATE TO JOURNAL ENTRY SYSTEM ===\n";
    
    try {
        // Create JournalEntry record
        $journalEntry = \App\Models\JournalEntry::create([
            'date' => $penjualan->tanggal,
            'memo' => 'Penjualan #' . $penjualan->nomor_penjualan,
            'ref_type' => 'sale',
            'ref_id' => $penjualan->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "Created JournalEntry ID: " . $journalEntry->id . "\n";
        
        // Create JournalLine records for each JurnalUmum entry
        foreach ($jurnalUmums as $jurnal) {
            \App\Models\JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'coa_id' => $jurnal->coa_id,
                'debit' => $jurnal->debit,
                'credit' => $jurnal->kredit,
                'memo' => $jurnal->keterangan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "Created JournalLine for COA: " . $jurnal->coa->kode_akun . "\n";
        }
        
        // Verify the migration
        $newLines = \App\Models\JournalLine::where('journal_entry_id', $journalEntry->id)
            ->with('coa')
            ->get();
        
        echo "\nVerification - Created " . $newLines->count() . " JournalLine records:\n";
        foreach ($newLines as $line) {
            echo "  - " . $line->coa->kode_akun . " " . $line->coa->nama_akun . ": ";
            echo ($line->debit > 0 ? "Debit Rp " . number_format($line->debit, 0, ',', '.') : "Kredit Rp " . number_format($line->credit, 0, ',', '.'));
            echo "\n";
        }
        
        echo "\nSUCCESS: Data migrated to JournalEntry system!\n";
        echo "UI will now show all journal entries including HPP!\n";
        
    } catch (Exception $e) {
        echo "Error migrating data: " . $e->getMessage() . "\n";
    }
    
} elseif ($journalEntries->count() > 0) {
    echo "JournalEntry system already has data\n";
    
    // Check if HPP entries exist in JournalLine
    $hppLines = \App\Models\JournalLine::whereIn('journal_entry_id', $journalEntries->pluck('id'))
        ->whereHas('coa', function($query) {
            $query->where('kode_akun', '56')
                   ->orWhere('nama_akun', 'like', '%Harga Pokok Penjualan%');
        })
        ->count();
    
    echo "HPP lines in JournalLine system: " . $hppLines . "\n";
    
    if ($hppLines === 0) {
        echo "HPP entries missing from JournalLine system - need to add them\n";
    }
} else {
    echo "No data in either system\n";
}

echo "\nDual journal system check completed!\n";
