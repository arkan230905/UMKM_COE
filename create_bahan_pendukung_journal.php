<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pembelian;
use App\Services\PembelianJournalService;

try {
    echo "=== CREATING BAHAN PENDUKUNG JOURNAL ===\n\n";
    
    // Get an existing bahan pendukung purchase
    $pembelian = Pembelian::with([
        'details.bahanBaku', 
        'details.bahanPendukung',
        'vendor'
    ])->whereHas('details', function($query) {
        $query->whereNotNull('bahan_pendukung_id');
    })->first();
    
    if (!$pembelian) {
        echo "❌ No bahan pendukung purchase found\n";
        exit;
    }
    
    echo "Found purchase: {$pembelian->nomor_pembelian}\n";
    echo "Vendor: {$pembelian->vendor->nama_vendor}\n";
    echo "Date: {$pembelian->tanggal}\n";
    echo "Payment method: {$pembelian->payment_method}\n\n";
    
    echo "Purchase details:\n";
    foreach ($pembelian->details as $detail) {
        if ($detail->bahan_pendukung_id) {
            $total = $detail->jumlah * $detail->harga_satuan;
            echo "- {$detail->bahanPendukung->nama_bahan}: {$detail->jumlah} x Rp " . number_format($detail->harga_satuan) . " = Rp " . number_format($total) . "\n";
        }
    }
    
    echo "\nTotals:\n";
    echo "Subtotal: Rp " . number_format($pembelian->subtotal) . "\n";
    echo "PPN: Rp " . number_format($pembelian->ppn_nominal) . "\n";
    echo "Total: Rp " . number_format($pembelian->total_harga) . "\n\n";
    
    // Create journal
    $journalService = new PembelianJournalService();
    $journal = $journalService->createJournalFromPembelian($pembelian);
    
    if ($journal) {
        echo "✅ Journal created successfully!\n";
        echo "Journal ID: {$journal->id}\n";
        echo "Date: {$journal->tanggal}\n";
        echo "Memo: {$journal->memo}\n\n";
        
        echo "Journal Lines:\n";
        foreach ($journal->lines as $line) {
            $amount = number_format($line->debit ?: $line->credit);
            $type = $line->debit ? 'Debit' : 'Credit';
            
            // Show the memo (which includes indentation for credit)
            echo "{$line->memo} - {$type}: Rp {$amount}\n";
        }
        
        // Calculate totals
        $totalDebit = $journal->lines->sum('debit');
        $totalCredit = $journal->lines->sum('credit');
        
        echo "\nTotals:\n";
        echo "Total Debit: Rp " . number_format($totalDebit) . "\n";
        echo "Total Credit: Rp " . number_format($totalCredit) . "\n";
        echo "Balance: " . ($totalDebit == $totalCredit ? "✅ Balanced" : "❌ Not Balanced") . "\n";
        
    } else {
        echo "❌ Failed to create journal\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}