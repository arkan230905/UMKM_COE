<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Services\PembelianJournalService;

try {
    echo "=== CREATING MISSING JOURNALS ===\n\n";
    
    // Find purchases without journals
    $purchasesWithoutJournals = Pembelian::whereDate('tanggal', '2026-04-09')
        ->whereNotExists(function($query) {
            $query->select('id')
                  ->from('journal_entries')
                  ->where('ref_type', 'purchase')
                  ->whereColumn('ref_id', 'pembelians.id');
        })
        ->with(['details.bahanBaku', 'details.bahanPendukung', 'vendor'])
        ->get();
    
    echo "Found " . $purchasesWithoutJournals->count() . " purchases without journals:\n\n";
    
    $journalService = new PembelianJournalService();
    
    foreach ($purchasesWithoutJournals as $purchase) {
        echo "Creating journal for Purchase ID {$purchase->id}: {$purchase->nomor_pembelian}\n";
        echo "- Vendor: {$purchase->vendor->nama_vendor}\n";
        echo "- Total: Rp " . number_format($purchase->total_harga) . "\n";
        echo "- Details: " . $purchase->details->count() . "\n";
        
        // Show what's in the purchase
        foreach ($purchase->details as $detail) {
            if ($detail->bahan_baku_id) {
                echo "  * Bahan Baku: {$detail->bahanBaku->nama_bahan}\n";
            }
            if ($detail->bahan_pendukung_id) {
                echo "  * Bahan Pendukung: {$detail->bahanPendukung->nama_bahan}\n";
            }
        }
        
        try {
            $journal = $journalService->createJournalFromPembelian($purchase);
            
            if ($journal) {
                echo "✅ Journal created (ID: {$journal->id})\n";
                echo "   Memo: {$journal->memo}\n";
            } else {
                echo "❌ Failed to create journal\n";
            }
        } catch (Exception $e) {
            echo "❌ Error creating journal: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // Final summary
    echo "🎯 FINAL STATUS:\n";
    $allPurchases = Pembelian::whereDate('tanggal', '2026-04-09')
        ->orderBy('id')
        ->get();
    
    foreach ($allPurchases as $purchase) {
        $journalCount = JournalEntry::where('ref_type', 'purchase')
            ->where('ref_id', $purchase->id)
            ->count();
        
        $journalStatus = $journalCount > 0 ? "✅ Has journal" : "❌ No journal";
        
        echo "- ID {$purchase->id}: {$purchase->nomor_pembelian} ({$journalStatus})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}