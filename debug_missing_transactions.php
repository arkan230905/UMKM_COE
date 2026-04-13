<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG MISSING TRANSACTIONS ===\n\n";

// Check if these transactions exist in journal system
$date = '2026-04-12';

echo "1. CHECK PB-0002 IN JOURNAL SYSTEM:\n";
echo "====================================\n";
$pb2Journal = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.ref_type', 'pembayaran_beban')
    ->where('journal_entries.ref_id', 2)
    ->where('journal_lines.credit', '>', 0)
    ->where('journal_entries.tanggal', $date)
    ->first();

if ($pb2Journal) {
    echo "✓ Found PB-0002 in journal system\n";
    echo "- Amount: " . number_format($pb2Journal->credit, 0, ',', '.') . "\n";
    echo "- Account: " . $pb2Journal->nama_akun . " (" . $pb2Journal->kode_akun . ")\n";
    echo "- Date: " . $pb2Journal->tanggal . "\n";
} else {
    echo "✗ PB-0002 NOT found in journal system\n";
}

echo "\n2. CHECK PB-0003 IN JOURNAL SYSTEM:\n";
echo "====================================\n";
$pb3Journal = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.ref_type', 'pembayaran_beban')
    ->where('journal_entries.ref_id', 3)
    ->where('journal_lines.credit', '>', 0)
    ->where('journal_entries.tanggal', $date)
    ->first();

if ($pb3Journal) {
    echo "✓ Found PB-0003 in journal system\n";
    echo "- Amount: " . number_format($pb3Journal->credit, 0, ',', '.') . "\n";
    echo "- Account: " . $pb3Journal->nama_akun . " (" . $pb3Journal->kode_akun . ")\n";
    echo "- Date: " . $pb3Journal->tanggal . "\n";
} else {
    echo "✗ PB-0003 NOT found in journal system\n";
}

echo "\n3. CHECK WHY NOT IN LAPORAN:\n";
echo "===============================\n";

// Check if there are any filters that might exclude these
echo "Checking LaporanKasBankController getDetailKeluar method...\n";

// Simulate the getDetailKeluar call
$startDate = '2026-04-01';
$endDate = '2026-04-30';

// Get Kas Bank account
$kasBank = \App\Models\Coa::where('kode_akun', '111')->first();

if (!$kasBank) {
    echo "Kas Bank account not found!\n";
    exit;
}

echo "Kas Bank Account: " . $kasBank->nama_akun . " (ID: " . $kasBank->id . ")\n";

// Check the actual query in getDetailKeluar
$detailQuery = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_lines.coa_id', $kasBank->id)
    ->where('journal_lines.credit', '>', 0)
    ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
    ->orderBy('journal_entries.tanggal', 'desc')
    ->orderBy('journal_entries.id', 'desc');

echo "Total transactions that should appear: " . $detailQuery->count() . "\n";

// Check if PB-0002 and PB-0003 are in the results
$pb2InResults = $detailQuery->where('journal_entries.ref_type', 'pembayaran_beban')
    ->where('journal_entries.ref_id', 2)
    ->exists();

$pb3InResults = $detailQuery->where('journal_entries.ref_type', 'pembayaran_beban')
    ->where('journal_entries.ref_id', 3)
    ->exists();

echo "PB-0002 in results: " . ($pb2InResults ? 'YES' : 'NO') . "\n";
echo "PB-0003 in results: " . ($pb3InResults ? 'YES' : 'NO') . "\n";

echo "\n4. RECOMMENDATION:\n";
echo "==================\n";
echo "If transactions exist in journal but not in report:\n";
echo "1. Check if there are additional filters in the view\n";
echo "2. Clear application cache\n";
echo "3. Check if JavaScript is filtering out these transactions\n";
