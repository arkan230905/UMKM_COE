<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Indonesian Number Formatting...\n\n";

// Test the same numbers from user's example
$testNumbers = [
    120000,
    250000,
    100000,
    51700,
    500000,
    55000,
    521700,
    555000
];

echo "Expected Format (Indonesian):\n";
foreach ($testNumbers as $number) {
    // Simulate JavaScript toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    $formatted = 'Rp ' . number_format($number, 2, ',', '.');
    echo "   {$number} -> {$formatted}\n";
}

echo "\nTesting with actual journal data:\n";
$pembelian = \App\Models\Pembelian::latest()->first();
if ($pembelian) {
    $journalEntries = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
        ->where('referensi', $pembelian->nomor_pembelian)
        ->with('coa')
        ->orderBy('id', 'asc')
        ->get();
    
    echo "Journal entries for {$pembelian->nomor_pembelian}:\n";
    foreach ($journalEntries as $entry) {
        if ($entry->debit > 0) {
            $formatted = 'Rp ' . number_format($entry->debit, 2, ',', '.');
            echo "   DEBIT: {$entry->coa->nama_akun} -> {$formatted}\n";
        }
        if ($entry->kredit > 0) {
            $formatted = 'Rp ' . number_format($entry->kredit, 2, ',', '.');
            echo "   CREDIT: {$entry->coa->nama_akun} -> {$formatted}\n";
        }
    }
    
    $totalDebit = $journalEntries->sum('debit');
    $totalCredit = $journalEntries->sum('kredit');
    
    echo "\nTotals:\n";
    echo "   Total Debit: Rp " . number_format($totalDebit, 2, ',', '.') . "\n";
    echo "   Total Credit: Rp " . number_format($totalCredit, 2, ',', '.') . "\n";
}

echo "\nJavaScript equivalent test:\n";
echo "120000.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })\n";
echo "// Should output: '120.000,00'\n";

echo "\nExpected final format:\n";
echo "Tanggal | Akun | Keterangan | Debet | Kredit\n";
echo "30/4/2026 | Pers. Bahan Pendukung Susu | Pembelian #... | Rp 120.000,00 | -\n";
echo "Total: | | | Rp 521.700,00 | Rp 521.700,00\n";

echo "\nFormat test completed!\n";
