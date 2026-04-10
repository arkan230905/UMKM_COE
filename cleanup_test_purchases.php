<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\JournalEntry;
use App\Models\JournalLine;

try {
    echo "=== CLEANING UP TEST PURCHASES ===\n\n";
    
    // Find test purchases
    $testPurchases = Pembelian::where('nomor_pembelian', 'like', '%TEST%')
        ->orWhere('nomor_pembelian', 'like', '%BP-%')
        ->get();
    
    echo "Found " . $testPurchases->count() . " test purchases to clean up:\n\n";
    
    foreach ($testPurchases as $purchase) {
        echo "Cleaning up Purchase ID {$purchase->id}: {$purchase->nomor_pembelian}\n";
        
        // 1. Delete associated journal entries and lines
        $journals = JournalEntry::where('ref_type', 'purchase')
            ->where('ref_id', $purchase->id)
            ->get();
        
        foreach ($journals as $journal) {
            echo "  - Deleting journal ID {$journal->id}\n";
            
            // Delete journal lines first
            JournalLine::where('journal_entry_id', $journal->id)->delete();
            
            // Delete journal entry
            $journal->delete();
        }
        
        // 2. Delete purchase details
        $detailCount = PembelianDetail::where('pembelian_id', $purchase->id)->count();
        if ($detailCount > 0) {
            echo "  - Deleting {$detailCount} purchase details\n";
            PembelianDetail::where('pembelian_id', $purchase->id)->delete();
        }
        
        // 3. Delete the purchase itself
        $purchase->delete();
        echo "  ✅ Purchase {$purchase->id} deleted\n\n";
    }
    
    echo "🎯 CLEANUP COMPLETE!\n\n";
    
    // Show remaining legitimate purchases
    echo "Remaining legitimate purchases:\n";
    $remainingPurchases = Pembelian::whereDate('tanggal', '2026-04-09')
        ->where('nomor_pembelian', 'not like', '%TEST%')
        ->where('nomor_pembelian', 'not like', '%BP-%')
        ->orderBy('id')
        ->get();
    
    foreach ($remainingPurchases as $purchase) {
        $journalCount = JournalEntry::where('ref_type', 'purchase')
            ->where('ref_id', $purchase->id)
            ->count();
        
        $journalStatus = $journalCount > 0 ? "✅ Has journal" : "❌ No journal";
        
        echo "- ID {$purchase->id}: {$purchase->nomor_pembelian} - Rp " . number_format($purchase->total_harga) . " ({$journalStatus})\n";
    }
    
    echo "\n📊 SUMMARY:\n";
    echo "- Deleted " . $testPurchases->count() . " test purchases\n";
    echo "- Remaining legitimate purchases: " . $remainingPurchases->count() . "\n";
    echo "- Purchase list should now be clean\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}