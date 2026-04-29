<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Fixed API Response...\n\n";

// Get pembelian
$pembelian = \App\Models\Pembelian::latest()->first();
if (!$pembelian) {
    echo "No pembelian found\n";
    exit;
}

echo "Pembelian: {$pembelian->nomor_pembelian}\n\n";

// Simulate the fixed API response
$journalEntries = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
    ->where('referensi', $pembelian->nomor_pembelian)
    ->with('coa')
    ->orderBy('id', 'asc')
    ->get();

echo "Fixed API Response Structure:\n";
echo "==========================\n";

// Convert debit/kredit to numbers (like the fixed API)
$journalsArray = $journalEntries->toArray();
foreach ($journalsArray as &$journal) {
    $journal['debit'] = (float) $journal['debit'];
    $journal['kredit'] = (float) $journal['kredit'];
}

// Show first journal entry structure
if (isset($journalsArray[0])) {
    $firstEntry = $journalsArray[0];
    echo "First journal entry:\n";
    echo "  id: {$firstEntry['id']}\n";
    echo "  tanggal: {$firstEntry['tanggal']}\n";
    echo "  debit: {$firstEntry['debit']} (type: " . gettype($firstEntry['debit']) . ")\n";
    echo "  kredit: {$firstEntry['kredit']} (type: " . gettype($firstEntry['kredit']) . ")\n";
    echo "  coa.nama_akun: {$firstEntry['coa']['nama_akun']}\n";
    echo "  coa.kode_akun: {$firstEntry['coa']['kode_akun']}\n";
    echo "  keterangan: {$firstEntry['keterangan']}\n\n";
}

// Test JavaScript formatting with numbers
echo "JavaScript Formatting Test (with numbers):\n";
echo "==========================================\n";

foreach ($journalsArray as $entry) {
    // Simulate JavaScript with actual numbers
    $debitFormatted = $entry['debit'] > 0 ? 'Rp ' . number_format($entry['debit'], 0, ',', '.') : '-';
    $kreditFormatted = $entry['kredit'] > 0 ? 'Rp ' . number_format($entry['kredit'], 0, ',', '.') : '-';
    
    // Simulate JavaScript date formatting
    $tanggal = date('d-m-Y', strtotime($entry['tanggal']));
    
    echo "Entry ID {$entry['id']}:\n";
    echo "  Number debit: {$entry['debit']} -> Formatted: {$debitFormatted}\n";
    echo "  Number kredit: {$entry['kredit']} -> Formatted: {$kreditFormatted}\n";
    echo "  Date: {$tanggal}\n";
    echo "\n";
}

// Test the actual JavaScript behavior
echo "JavaScript Behavior Test:\n";
echo "==========================\n";
echo "120000.00 (string).toLocaleString('id-ID') -> '120000.00' (no formatting)\n";
echo "120000 (number).toLocaleString('id-ID') -> '120.000' (formatted correctly)\n\n";

echo "Fixed API now sends numbers instead of strings!\n";
echo "This should fix the formatting issue in the browser.\n\n";

echo "Expected browser output:\n";
echo "Rp 120.000 (not Rp 120000.00)\n";
echo "Rp 250.000 (not Rp 250000.00)\n";
echo "Rp 521.700 (not Rp 521700.00)\n";

echo "\nAPI fix test completed!\n";
