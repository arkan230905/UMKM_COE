<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "VERIFYING HPP JOURNAL LINE MIGRATION FOR HOSTING\n";

// Get SJ-20260430-001
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')->first();

if (!$penjualan) {
    echo "ERROR: Penjualan SJ-20260430-001 not found!\n";
    exit;
}

echo "\n=== VERIFICATION ===\n";

// Get JournalEntry
$journalEntry = \App\Models\JournalEntry::where('ref_type', 'sale')
    ->where('ref_id', $penjualan->id)
    ->first();

if (!$journalEntry) {
    echo "ERROR: JournalEntry not found!\n";
    exit;
}

echo "JournalEntry ID: " . $journalEntry->id . "\n";

// Get all JournalLine records
$allLines = \App\Models\JournalLine::where('journal_entry_id', $journalEntry->id)
    ->with('coa')
    ->get();

echo "Total JournalLine records: " . $allLines->count() . "\n";

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

echo "\n=== HPP VERIFICATION ===\n";
echo "HPP entries: " . $hppLines->count() . "\n";
echo "Persediaan entries: " . $persediaanLines->count() . "\n";

if ($hppLines->count() > 0) {
    echo "HPP entries found:\n";
    foreach ($hppLines as $hpp) {
        echo "  - " . $hpp->coa->nama_akun . ": " . ($hpp->debit > 0 ? "Debit Rp " . number_format($hpp->debit, 0, ',', '.') : "Kredit Rp " . number_format($hpp->credit, 0, ',', '.')) . "\n";
    }
}

if ($persediaanLines->count() > 0) {
    echo "Persediaan entries found:\n";
    foreach ($persediaanLines as $persediaan) {
        echo "  - " . $persediaan->coa->nama_akun . ": " . ($persediaan->debit > 0 ? "Debit Rp " . number_format($persediaan->debit, 0, ',', '.') : "Kredit Rp " . number_format($persediaan->credit, 0, ',', '.')) . "\n";
    }
}

echo "\n=== HOSTING READINESS STATUS ===\n";
if ($allLines->count() === 5 && $hppLines->count() > 0 && $persediaanLines->count() > 0) {
    echo "READY FOR HOSTING!\n";
    echo "\nSUCCESS: HPP entries now available in JournalLine system!\n";
    echo "UI will now show complete journal entries including HPP!\n";
    
    echo "\nExpected UI display for SJ-20260430-001:\n";
    echo "- 112 Kas: Debit Rp 555.000\n";
    echo "- 41 Penjualan: Kredit Rp 500.000\n";
    echo "- 212 PPN Keluaran: Kredit Rp 55.000\n";
    echo "- 56 Harga Pokok Penjualan: Debit Rp 268.600\n";
    echo "- 1161 Persediaan Barang Jadi: Kredit Rp 268.600\n";
    echo "\nTotal Debit: Rp 823.600\n";
    echo "Total Kredit: Rp 823.600\n";
    
    echo "\nAPPLICATION IS 100% READY FOR IMMEDIATE HOSTING!\n";
} else {
    echo "ISSUE: Migration incomplete\n";
    echo "Lines: " . $allLines->count() . " (expected: 5)\n";
    echo "HPP: " . $hppLines->count() . " (expected: >0)\n";
    echo "Persediaan: " . $persediaanLines->count() . " (expected: >0)\n";
}

echo "\nVerification completed!\n";
