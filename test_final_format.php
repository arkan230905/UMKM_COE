<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Final Journal Format (User Requirements)...\n\n";

// Test with actual pembelian data
$pembelian = \App\Models\Pembelian::latest()->first();
if (!$pembelian) {
    echo "No pembelian found\n";
    exit;
}

echo "Pembelian: {$pembelian->nomor_pembelian}\n\n";

echo "Expected Format (User Requirements):\n";
echo "Tanggal | Akun | Keterangan | Debet | Kredit\n";
echo "30-04-2026 | Pers. Bahan Pendukung Susu | Pembelian #... | Rp 120.000 | -\n";
echo "30-04-2026 | Pers. Bahan Pendukung Keju | Pembelian #... | Rp 250.000 | -\n";
echo "30-04-2026 | Pers. Bahan Pendukung Kemasan (Cup) | Pembelian #... | Rp 100.000 | -\n";
echo "30-04-2026 | PPN Masukkan | Pembelian #... | Rp 51.700 | -\n";
echo "30-04-2026 | Kas | Pembelian #... | - | Rp 521.700\n";
echo "Total: | | | Rp 521.700 | Rp 521.700\n\n";

echo "Actual Data from Database:\n";
$journalEntries = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
    ->where('referensi', $pembelian->nomor_pembelian)
    ->with('coa')
    ->orderBy('id', 'asc')
    ->get();

foreach ($journalEntries as $entry) {
    // Format date: DD-MM-YYYY
    $tanggal = \Carbon\Carbon::parse($entry->tanggal)->format('d-m-Y');
    
    // Format number: Rp 120.000 (no decimals)
    $debit = $entry->debit > 0 ? 'Rp ' . number_format($entry->debit, 0, ',', '.') : '-';
    $kredit = $entry->kredit > 0 ? 'Rp ' . number_format($entry->kredit, 0, ',', '.') : '-';
    
    echo "{$tanggal} | {$entry->coa->nama_akun} | {$entry->keterangan} | {$debit} | {$kredit}\n";
}

// Calculate totals
$totalDebit = $journalEntries->sum('debit');
$totalCredit = $journalEntries->sum('kredit');

echo "Total: | | | Rp " . number_format($totalDebit, 0, ',', '.') . " | Rp " . number_format($totalCredit, 0, ',', '.') . "\n\n";

echo "JavaScript Equivalent Testing:\n";
echo "Date format: new Date('2026-04-30').toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\\//g, '-')\n";
echo "// Should output: '30-04-2026'\n\n";

echo "Number format: 120000.toLocaleString('id-ID')\n";  
echo "// Should output: '120.000'\n\n";

echo "Format verification:\n";
echo "Date: " . \Carbon\Carbon::parse($pembelian->tanggal)->format('d-m-Y') . " (should be DD-MM-YYYY)\n";
echo "Number: Rp " . number_format(120000, 0, ',', '.') . " (should be Rp 120.000)\n";

echo "\nFinal format test completed!\n";
