<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

echo "=== REMOVING DUPLICATE TRANSFER WIP JOURNALS ===\n\n";

try {
    DB::beginTransaction();
    
    echo "1. Identifying duplicate 'Transfer WIP ke Barang Jadi' entries...\n";
    
    // Get COA for WIP and Finished Goods
    $coaWIP = Coa::where('kode_akun', '117')->first();
    $coaFinishedGoods = Coa::where('kode_akun', '116')->first();
    
    if (!$coaWIP || !$coaFinishedGoods) {
        throw new Exception("Required COA accounts not found!");
    }
    
    echo "   WIP COA: {$coaWIP->kode_akun} - {$coaWIP->nama_akun} (ID: {$coaWIP->id})\n";
    echo "   Finished Goods COA: {$coaFinishedGoods->kode_akun} - {$coaFinishedGoods->nama_akun} (ID: {$coaFinishedGoods->id})\n\n";
    
    // Find all transfer journals on 17/04/2026
    $transferDate = '2026-04-17';
    
    $transferJournals = JurnalUmum::whereDate('tanggal', $transferDate)
        ->where('keterangan', 'like', '%Transfer WIP ke Barang Jadi%')
        ->orderBy('id')
        ->get();
    
    echo "   Found " . $transferJournals->count() . " transfer journals on {$transferDate}:\n\n";
    
    // Group by product name
    $journalsByProduct = [];
    
    foreach ($transferJournals as $journal) {
        // Extract product name from description
        $productName = '';
        if (strpos($journal->keterangan, 'Ayam Crispy Macdi') !== false) {
            $productName = 'Ayam Crispy Macdi';
        } elseif (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false) {
            $productName = 'Ayam Goreng Bundo';
        } else {
            $productName = 'Unknown';
        }
        
        if (!isset($journalsByProduct[$productName])) {
            $journalsByProduct[$productName] = [];
        }
        
        $journalsByProduct[$productName][] = $journal;
        
        echo "     ID: {$journal->id} | COA: {$journal->coa_id} | Product: {$productName}\n";
        echo "       Debit: Rp " . number_format($journal->debit, 0, ',', '.') . " | Credit: Rp " . number_format($journal->kredit, 0, ',', '.') . "\n";
        echo "       Description: {$journal->keterangan}\n\n";
    }
    
    echo "2. Processing duplicates by product...\n\n";
    
    $removedCount = 0;
    
    foreach ($journalsByProduct as $productName => $journals) {
        echo "   Product: {$productName}\n";
        echo "   Found " . count($journals) . " journals for this product\n";
        
        if (count($journals) <= 2) {
            echo "     ✅ Normal count (2 journals expected: 1 debit FG + 1 credit WIP)\n\n";
            continue;
        }
        
        // Group by debit/credit and COA
        $debitJournals = [];
        $creditJournals = [];
        
        foreach ($journals as $journal) {
            if ($journal->debit > 0) {
                $debitJournals[] = $journal;
            } else {
                $creditJournals[] = $journal;
            }
        }
        
        echo "     Debit journals (Finished Goods): " . count($debitJournals) . "\n";
        echo "     Credit journals (WIP): " . count($creditJournals) . "\n";
        
        // Remove duplicates - keep the first one, remove the rest
        if (count($debitJournals) > 1) {
            echo "     Removing duplicate debit journals...\n";
            for ($i = 1; $i < count($debitJournals); $i++) {
                $journal = $debitJournals[$i];
                echo "       Removing debit journal ID: {$journal->id} - Rp " . number_format($journal->debit, 0, ',', '.') . "\n";
                $journal->delete();
                $removedCount++;
            }
        }
        
        if (count($creditJournals) > 1) {
            echo "     Removing duplicate credit journals...\n";
            for ($i = 1; $i < count($creditJournals); $i++) {
                $journal = $creditJournals[$i];
                echo "       Removing credit journal ID: {$journal->id} - Rp " . number_format($journal->kredit, 0, ',', '.') . "\n";
                $journal->delete();
                $removedCount++;
            }
        }
        
        echo "     ✅ Duplicates removed for {$productName}\n\n";
    }
    
    echo "3. Verifying remaining transfer journals...\n";
    
    $remainingTransfers = JurnalUmum::whereDate('tanggal', $transferDate)
        ->where('keterangan', 'like', '%Transfer WIP ke Barang Jadi%')
        ->orderBy('id')
        ->get();
    
    echo "   Remaining transfer journals: " . $remainingTransfers->count() . "\n\n";
    
    $remainingByProduct = [];
    foreach ($remainingTransfers as $journal) {
        $productName = '';
        if (strpos($journal->keterangan, 'Ayam Crispy Macdi') !== false) {
            $productName = 'Ayam Crispy Macdi';
        } elseif (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false) {
            $productName = 'Ayam Goreng Bundo';
        }
        
        if (!isset($remainingByProduct[$productName])) {
            $remainingByProduct[$productName] = [];
        }
        $remainingByProduct[$productName][] = $journal;
        
        echo "     ID: {$journal->id} | Product: {$productName}\n";
        echo "       Debit: Rp " . number_format($journal->debit, 0, ',', '.') . " | Credit: Rp " . number_format($journal->kredit, 0, ',', '.') . "\n";
    }
    
    echo "\n4. Verifying WIP balance...\n";
    
    $wipJournals = JurnalUmum::where('coa_id', $coaWIP->id)->get();
    $wipDebit = $wipJournals->sum('debit');
    $wipCredit = $wipJournals->sum('kredit');
    $wipBalance = $wipDebit - $wipCredit;
    
    echo "   WIP Account (117 - Barang Dalam Proses):\n";
    echo "   - Total Debit: Rp " . number_format($wipDebit, 0, ',', '.') . "\n";
    echo "   - Total Credit: Rp " . number_format($wipCredit, 0, ',', '.') . "\n";
    echo "   - Balance: Rp " . number_format($wipBalance, 0, ',', '.') . "\n";
    
    if ($wipBalance >= 0) {
        echo "   ✅ WIP balance is positive or zero\n\n";
    } else {
        echo "   ❌ WIP balance is negative\n\n";
    }
    
    echo "5. Summary by product after cleanup...\n";
    
    foreach ($remainingByProduct as $productName => $journals) {
        echo "   {$productName}: " . count($journals) . " journals\n";
        
        $productDebit = 0;
        $productCredit = 0;
        
        foreach ($journals as $journal) {
            $productDebit += $journal->debit;
            $productCredit += $journal->kredit;
        }
        
        echo "     Total Debit: Rp " . number_format($productDebit, 0, ',', '.') . "\n";
        echo "     Total Credit: Rp " . number_format($productCredit, 0, ',', '.') . "\n";
        echo "     Balanced: " . ($productDebit == $productCredit ? "✅ Yes" : "❌ No") . "\n\n";
    }
    
    DB::commit();
    
    echo str_repeat("=", 60) . "\n";
    echo "✅ DUPLICATE TRANSFER JOURNALS REMOVED!\n";
    echo str_repeat("=", 60) . "\n";
    echo "Summary:\n";
    echo "- Removed {$removedCount} duplicate journal entries\n";
    echo "- Each product now has only 1 debit and 1 credit entry\n";
    echo "- WIP balance: Rp " . number_format($wipBalance, 0, ',', '.') . "\n";
    echo "- Remaining transfer journals: " . $remainingTransfers->count() . "\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back.\n";
}