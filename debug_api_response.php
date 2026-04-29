<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging API Response for Journal Data...\n\n";

// Get pembelian
$pembelian = \App\Models\Pembelian::latest()->first();
if (!$pembelian) {
    echo "No pembelian found\n";
    exit;
}

echo "Pembelian: {$pembelian->nomor_pembelian}\n\n";

// Simulate the exact API response
$journalEntries = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
    ->where('referensi', $pembelian->nomor_pembelian)
    ->with('coa')
    ->orderBy('id', 'asc')
    ->get();

echo "Raw API Response Structure:\n";
echo "========================\n";

$response = [
    'success' => true,
    'journals' => $journalEntries->toArray(),
    'pembelian' => [
        'id' => $pembelian->id,
        'nomor_pembelian' => $pembelian->nomor_pembelian,
        'total_harga' => $pembelian->total_harga
    ]
];

// Show first journal entry structure
if (isset($response['journals'][0])) {
    $firstEntry = $response['journals'][0];
    echo "First journal entry:\n";
    echo "  id: {$firstEntry['id']}\n";
    echo "  tanggal: {$firstEntry['tanggal']}\n";
    echo "  debit: {$firstEntry['debit']} (type: " . gettype($firstEntry['debit']) . ")\n";
    echo "  kredit: {$firstEntry['kredit']} (type: " . gettype($firstEntry['kredit']) . ")\n";
    echo "  coa.nama_akun: {$firstEntry['coa']['nama_akun']}\n";
    echo "  coa.kode_akun: {$firstEntry['coa']['kode_akun']}\n";
    echo "  keterangan: {$firstEntry['keterangan']}\n\n";
}

// Test JavaScript formatting simulation
echo "JavaScript Formatting Simulation:\n";
echo "================================\n";

foreach ($response['journals'] as $entry) {
    // Simulate JavaScript: entry.debit.toLocaleString('id-ID')
    $debitFormatted = $entry['debit'] > 0 ? 'Rp ' . number_format($entry['debit'], 0, ',', '.') : '-';
    $kreditFormatted = $entry['kredit'] > 0 ? 'Rp ' . number_format($entry['kredit'], 0, ',', '.') : '-';
    
    // Simulate JavaScript date formatting
    $tanggal = date('d-m-Y', strtotime($entry['tanggal']));
    
    echo "Entry ID {$entry['id']}:\n";
    echo "  Raw debit: {$entry['debit']} -> Formatted: {$debitFormatted}\n";
    echo "  Raw kredit: {$entry['kredit']} -> Formatted: {$kreditFormatted}\n";
    echo "  Raw date: {$entry['tanggal']} -> Formatted: {$tanggal}\n";
    echo "\n";
}

// Test JavaScript console simulation
echo "JavaScript Console Simulation:\n";
echo "===============================\n";
echo "console.log('120000'.toLocaleString('id-ID'))\n";
echo "// Expected: '120.000'\n";
echo "// Actual PHP simulation: " . '120000' . "\n\n";

echo "console.log(120000.toLocaleString('id-ID'))\n";
echo "// Expected: '120.000'\n";
echo "// Actual PHP simulation: " . number_format(120000, 0, ',', '.') . "\n\n";

echo "API Response Debug completed!\n";
