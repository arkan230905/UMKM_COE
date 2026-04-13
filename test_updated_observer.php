<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Testing updated PembelianObserver with existing purchase ID 10...\n\n";

// First, delete the manually created journal to test the observer
echo "Cleaning up existing journal for purchase ID 10...\n";
$journalService = new \App\Services\JournalService();
$journalService->deleteByRef('purchase', 10);
echo "Existing journal deleted.\n\n";

// Now test the observer
$pembelian = \App\Models\Pembelian::find(10);
if (!$pembelian) {
    echo "Purchase ID 10 not found!\n";
    exit;
}

echo "Purchase details:\n";
echo "ID: {$pembelian->id}\n";
echo "Nomor: {$pembelian->nomor_pembelian}\n";
echo "Total: {$pembelian->total_harga}\n";
echo "PPN Nominal: {$pembelian->ppn_nominal}\n";
echo "Payment Method: {$pembelian->payment_method}\n\n";

echo "Purchase details with COA pembelian:\n";
foreach ($pembelian->details as $detail) {
    echo "Detail ID: {$detail->id}\n";
    
    if ($detail->bahanBaku) {
        echo "  Bahan Baku: {$detail->bahanBaku->nama_bahan}\n";
        echo "  COA Pembelian ID: " . ($detail->bahanBaku->coa_pembelian_id ?? 'NULL') . "\n";
        if ($detail->bahanBaku->coa_pembelian_id && $detail->bahanBaku->coaPembelian) {
            echo "  COA Pembelian: {$detail->bahanBaku->coaPembelian->nama_akun} ({$detail->bahanBaku->coaPembelian->kode_akun})\n";
        }
    } elseif ($detail->bahanPendukung) {
        echo "  Bahan Pendukung: {$detail->bahanPendukung->nama_bahan}\n";
        echo "  COA Pembelian ID: " . ($detail->bahanPendukung->coa_pembelian_id ?? 'NULL') . "\n";
        if ($detail->bahanPendukung->coa_pembelian_id && $detail->bahanPendukung->coaPembelian) {
            echo "  COA Pembelian: {$detail->bahanPendukung->coaPembelian->nama_akun} ({$detail->bahanPendukung->coaPembelian->kode_akun})\n";
        }
    }
    echo "  Subtotal: {$detail->subtotal}\n\n";
}

echo "Testing observer manually...\n";
$observer = new \App\Observers\PembelianObserver();
$observer->created($pembelian);

echo "\nChecking if journal was created...\n";
$journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
    ->where('ref_id', 10)
    ->with('lines.coa')
    ->get();

echo "Journal entries found: " . $journalEntries->count() . "\n\n";

if ($journalEntries->count() > 0) {
    foreach ($journalEntries as $journal) {
        echo "Journal Entry ID: {$journal->id}\n";
        echo "Tanggal: {$journal->tanggal}\n";
        echo "Memo: {$journal->memo}\n";
        echo "Lines:\n";
        
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($journal->lines as $line) {
            $coaName = $line->coa ? $line->coa->nama_akun : 'UNKNOWN';
            echo "  {$coaName}: Debit={$line->debit}, Credit={$line->credit}\n";
            $totalDebit += $line->debit;
            $totalCredit += $line->credit;
        }
        
        echo "  Total Debit: {$totalDebit}\n";
        echo "  Total Credit: {$totalCredit}\n";
        echo "  Balance: " . ($totalDebit == $totalCredit ? 'BALANCED' : 'NOT BALANCED') . "\n\n";
    }
} else {
    echo "No journal entries created. Check logs for errors.\n";
}

echo "\nDone.\n";
