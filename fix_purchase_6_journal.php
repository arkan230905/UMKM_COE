<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pembelian;
use App\Services\PembelianJournalService;

try {
    echo "=== FIXING PURCHASE #6 JOURNAL ===\n\n";
    
    // Get purchase #6
    $pembelian = Pembelian::with([
        'details.bahanBaku', 
        'details.bahanPendukung',
        'vendor'
    ])->find(6);
    
    if (!$pembelian) {
        echo "❌ Purchase #6 not found\n";
        exit;
    }
    
    echo "Found purchase #6:\n";
    echo "- Nomor: {$pembelian->nomor_pembelian}\n";
    echo "- Vendor: {$pembelian->vendor->nama_vendor}\n";
    echo "- Tanggal: {$pembelian->tanggal}\n";
    echo "- Total: Rp " . number_format($pembelian->total_harga) . "\n";
    echo "- Payment method: {$pembelian->payment_method}\n";
    echo "- Details: " . $pembelian->details->count() . "\n\n";
    
    // Show details
    echo "Purchase details:\n";
    foreach ($pembelian->details as $detail) {
        if ($detail->bahan_baku_id) {
            echo "- Bahan Baku: {$detail->bahanBaku->nama_bahan} - {$detail->jumlah} x Rp " . number_format($detail->harga_satuan) . "\n";
        }
        if ($detail->bahan_pendukung_id) {
            echo "- Bahan Pendukung: {$detail->bahanPendukung->nama_bahan} - {$detail->jumlah} x Rp " . number_format($detail->harga_satuan) . "\n";
        }
    }
    
    // Check if journal already exists
    $existingJournal = \App\Models\JournalEntry::where('ref_type', 'purchase')
        ->where('ref_id', 6)
        ->first();
    
    if ($existingJournal) {
        echo "\n✅ Journal already exists (ID: {$existingJournal->id})\n";
        echo "Memo: {$existingJournal->memo}\n";
    } else {
        echo "\n❌ No journal exists for this purchase\n";
        echo "Creating journal...\n";
        
        // Create journal
        $journalService = new PembelianJournalService();
        $journal = $journalService->createJournalFromPembelian($pembelian);
        
        if ($journal) {
            echo "✅ Journal created successfully!\n";
            echo "Journal ID: {$journal->id}\n";
            echo "Memo: {$journal->memo}\n\n";
            
            echo "Journal lines:\n";
            foreach ($journal->lines as $line) {
                $amount = number_format($line->debit ?: $line->credit);
                $type = $line->debit > 0 ? 'Debit' : 'Credit';
                $indent = $line->credit > 0 ? '    ' : '';
                
                echo "{$indent}{$line->coa->nama_akun} - {$type}: Rp {$amount}\n";
            }
        } else {
            echo "❌ Failed to create journal\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}