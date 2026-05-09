<?php
// Test script untuk debug journal creation

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Penjualan;
use App\Services\JournalService;

// Get the latest penjualan
$penjualan = Penjualan::with('details.produk', 'produk')
    ->orderBy('id', 'desc')
    ->first();

if (!$penjualan) {
    echo "Tidak ada penjualan ditemukan\n";
    exit(1);
}

echo "Testing journal creation for penjualan: {$penjualan->nomor_penjualan}\n";
echo "ID: {$penjualan->id}\n";
echo "User ID: {$penjualan->user_id}\n";
echo "\n";

// Set auth
\Illuminate\Support\Facades\Auth::loginUsingId($penjualan->user_id);

// Test validation
$validator = new \App\Services\JournalValidationService();
$validation = $validator->validate($penjualan);

echo "=== VALIDATION RESULT ===\n";
echo "Valid: " . ($validation['valid'] ? 'YES' : 'NO') . "\n";

if (!$validation['valid']) {
    echo "\nMissing Accounts:\n";
    foreach ($validation['missing'] as $item) {
        echo "  - {$item['nama']} ({$item['tipe']}): {$item['pesan']}\n";
    }
    exit(1);
}

echo "\nFound Accounts:\n";
foreach ($validation['accounts'] as $key => $account) {
    echo "  - {$account->kode_akun}: {$account->nama_akun}\n";
}

echo "\n=== CREATING JOURNAL ===\n";

try {
    JournalService::createJournalFromPenjualan($penjualan);
    echo "✓ Journal created successfully!\n";
    
    // Check if journal was created
    $journal = \App\Models\JournalEntry::where('ref_type', 'sale')
        ->where('ref_id', $penjualan->id)
        ->with('linesWithAccount')
        ->first();
    
    if ($journal) {
        echo "\nJournal Details:\n";
        echo "ID: {$journal->id}\n";
        echo "Lines: " . $journal->linesWithAccount->count() . "\n";
        
        $totalDebit = 0;
        $totalKredit = 0;
        foreach ($journal->linesWithAccount as $line) {
            echo "  {$line->coa->kode_akun} | {$line->coa->nama_akun} | Dr: {$line->debit} | Cr: {$line->credit}\n";
            $totalDebit += $line->debit;
            $totalKredit += $line->credit;
        }
        echo "\nTotal Debit: {$totalDebit}\n";
        echo "Total Kredit: {$totalKredit}\n";
        echo "Balance: " . ($totalDebit == $totalKredit ? "✓ YES" : "✗ NO") . "\n";
    } else {
        echo "✗ Journal entry not found in database!\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
