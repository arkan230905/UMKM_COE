<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "MIGRATING HPP TO JOURNAL LINE SYSTEM FOR HOSTING\n";

// Get SJ-20260430-001
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')->first();

if (!$penjualan) {
    echo "ERROR: Penjualan SJ-20260430-001 not found!\n";
    exit;
}

echo "\n=== CURRENT STATUS ===\n";
echo "Penjualan ID: " . $penjualan->id . "\n";

// Get existing JournalEntry
$journalEntry = \App\Models\JournalEntry::where('ref_type', 'sale')
    ->where('ref_id', $penjualan->id)
    ->first();

if (!$journalEntry) {
    echo "ERROR: JournalEntry not found!\n";
    exit;
}

echo "JournalEntry ID: " . $journalEntry->id . "\n";

// Get existing lines
$existingLines = \App\Models\JournalLine::where('journal_entry_id', $journalEntry->id)
    ->with('coa')
    ->get();

echo "Existing JournalLine records: " . $existingLines->count() . "\n";
foreach ($existingLines as $line) {
    echo "  - " . $line->coa->kode_akun . " " . $line->coa->nama_akun . "\n";
}

// Get HPP entries from JurnalUmum that are missing from JournalLine
echo "\n=== FINDING MISSING HPP ENTRIES ===\n";
$jurnalUmums = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)
    ->with('coa')
    ->get();

echo "JurnalUmum records: " . $jurnalUmums->count() . "\n";

// Find which JurnalUmum entries are not in JournalLine
$existingCoaIds = $existingLines->pluck('coa_id');
$missingEntries = $jurnalUmums->whereNotIn('coa_id', $existingCoaIds);

echo "Missing entries: " . $missingEntries->count() . "\n";

if ($missingEntries->count() > 0) {
    echo "\n=== ADDING MISSING HPP ENTRIES ===\n";
    
    foreach ($missingEntries as $missing) {
        echo "Adding: " . $missing->coa->kode_akun . " " . $missing->coa->nama_akun . "\n";
        
        \App\Models\JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $missing->coa_id,
            'debit' => $missing->debit,
            'credit' => $missing->kredit,
            'memo' => $missing->keterangan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "  - Created: " . ($missing->debit > 0 ? "Debit Rp " . number_format($missing->debit, 0, ',', '.') : "Kredit Rp " . number_format($missing->kredit, 0, ',', '.')) . "\n";
    }
    
    echo "\nSUCCESS: Added " . $missingEntries->count() . " missing entries!\n";
    
    // Verify the complete set
    echo "\n=== VERIFICATION ===\n";
    $allLines = \App\Models\JournalLine::where('journal_entry_id', $journalEntry->id)
        ->with('coa')
        ->orderBy('coa.kode_akun')
        ->get();
    
    echo "Complete JournalLine records: " . $allLines->count() . "\n";
    
    echo "\nComplete journal structure for SJ-20260430-001:\n";
    echo "Kode\tNama Akun\t\t\tDebit\t\tKredit\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($allLines as $line) {
        echo $line->coa->kode_akun . "\t";
        echo substr($line->coa->nama_akun, 0, 20) . "\t\t";
        echo ($line->debit > 0 ? "Rp " . number_format($line->debit, 0, ',', '.') : "-") . "\t";
        echo ($line->credit > 0 ? "Rp " . number_format($line->credit, 0, ',', '.') : "-") . "\n";
    }
    
    $totalDebit = $allLines->sum('debit');
    $totalKredit = $allLines->sum('kredit');
    
    echo str_repeat("-", 80) . "\n";
    echo "TOTAL\t\t\t\tRp " . number_format($totalDebit, 0, ',', '.') . "\tRp " . number_format($totalKredit, 0, ',', '.') . "\n";
    
    echo "\nBalance: " . ($totalDebit == $totalKredit ? "BALANCED" : "NOT BALANCED") . "\n";
    
    // Check HPP entries specifically
    $hppLines = $allLines->filter(function($line) {
        return $line->coa->kode_akun == '56' || 
               strpos($line->coa->nama_akun, 'Harga Pokok Penjualan') !== false;
    });
    
    $persediaanLines = $allLines->filter(function($line) {
        return $line->coa->kode_akun == '1161' || 
               strpos($line->coa->nama_akun, 'Persediaan Barang Jadi') !== false;
    });
    
    echo "\nHPP entries: " . $hppLines->count() . "\n";
    echo "Persediaan entries: " . $persediaanLines->count() . "\n";
    
    if ($hppLines->count() > 0 && $persediaanLines->count() > 0) {
        echo "\nSUCCESS: HPP entries now available in JournalLine system!\n";
        echo "UI will now show complete journal entries including HPP!\n";
        echo "\nExpected UI display for SJ-20260430-001:\n";
        echo "- 112 Kas: Debit Rp 555.000\n";
        echo "- 41 Penjualan: Kredit Rp 500.000\n";
        echo "- 212 PPN Keluaran: Kredit Rp 55.000\n";
        echo "- 56 Harga Pokok Penjualan: Debit Rp 268.600\n";
        echo "- 1161 Persediaan Barang Jadi: Kredit Rp 268.600\n";
        echo "\nREADY FOR HOSTING!\n";
    } else {
        echo "\nISSUE: HPP entries still missing\n";
    }
    
} else {
    echo "\nNo missing entries found - all entries already in JournalLine system\n";
}

echo "\nMigration completed!\n";
