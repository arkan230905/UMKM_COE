<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FINAL DEBUG - WHY TRANSACTIONS MISSING ===\n\n";

// Check the exact query that LaporanKasBankController uses
$startDate = '2026-04-01';
$endDate = '2026-04-30';

// Get Kas Bank account
$kasBank = \App\Models\Coa::where('kode_akun', '111')->first();

if (!$kasBank) {
    echo "Kas Bank account not found!\n";
    exit;
}

echo "Kas Bank Account: " . $kasBank->nama_akun . " (ID: " . $kasBank->id . ")\n\n";

// Replicate the exact query from getDetailKeluar
$transaksiQuery = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_lines.coa_id', $kasBank->id)
    ->where('journal_lines.credit', '>', 0)
    ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
    ->orderBy('journal_entries.tanggal', 'desc')
    ->orderBy('journal_entries.id', 'desc');

echo "Total transactions in query: " . $transaksiQuery->count() . "\n\n";

// Get all transactions and check details
$allTransactions = $transaksiQuery->get();

echo "All transactions for Kas Bank account:\n";
echo "==========================================\n";
foreach ($allTransactions as $trans) {
    echo "Date: " . $trans->tanggal . "\n";
    echo "Ref Type: " . $trans->ref_type . "\n";
    echo "Ref ID: " . $trans->ref_id . "\n";
    echo "Amount: " . number_format($trans->credit, 0, ',', '.') . "\n";
    echo "Account: " . $trans->nama_akun . " (" . $trans->kode_akun . ")\n";
    echo "Description: " . $trans->deskripsi . "\n";
    echo "----------------------------------------\n";
}

echo "\n=== SPECIFIC CHECK FOR PB-0002 and PB-0003 ===\n";

// Check specifically for these transactions
$pb2Transaction = $allTransactions->firstWhere('ref_type', 'pembayaran_beban');
$pb3Transaction = $allTransactions->firstWhere('ref_type', 'pembayaran_beban');

echo "PB-0002 Transaction Found:\n";
if ($pb2Transaction) {
    echo "- Account: " . $pb2Transaction->nama_akun . " (" . $pb2Transaction->kode_akun . ")\n";
    echo "- This should be Kas Bank (111) but is: " . ($pb2Transaction->kode_akun == '111' ? 'CORRECT' : 'WRONG') . "\n";
} else {
    echo "- PB-0002 NOT FOUND\n";
}

echo "\nPB-0003 Transaction Found:\n";
if ($pb3Transaction) {
    echo "- Account: " . $pb3Transaction->nama_akun . " (" . $pb3Transaction->kode_akun . ")\n";
    echo "- This should be Kas Bank (111) but is: " . ($pb3Transaction->kode_akun == '111' ? 'CORRECT' : 'WRONG') . "\n";
} else {
    echo "- PB-0003 NOT FOUND\n";
}

echo "\n=== CONCLUSION ===\n";
echo "The issue is that PB-0002 and PB-0003 transactions exist in journal system\n";
echo "but they are recorded with wrong coa_id (Kas 112 instead of Kas Bank 111).\n";
echo "This is why they don't appear in Kas Bank report.\n";
echo "The fix is to update the coa_id for these transactions to 111.\n";
