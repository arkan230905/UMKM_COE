<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CHECKING HPP (HARGA POKOK PENJUALAN) ===\n";

// Check what the Laba Rugi controller is looking for HPP
echo "1. HPP accounts that Laba Rugi controller is looking for (16xx):\n";
$hppAccounts = \App\Models\Coa::where('tipe_akun','Expense')
                              ->where('kode_akun', 'LIKE', '16%')
                              ->get();

echo "Found " . $hppAccounts->count() . " HPP accounts with code 16xx:\n";
foreach ($hppAccounts as $coa) {
    echo "- {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})\n";
}

// Check all expense accounts to see if HPP is elsewhere
echo "\n2. All Expense accounts:\n";
$allExpenses = \App\Models\Coa::where('tipe_akun', 'Expense')->get();
foreach ($allExpenses as $coa) {
    $journalLines = \App\Models\JournalLine::where('coa_id', $coa->id)->get();
    $totalDebit = $journalLines->sum('debit');
    $totalCredit = $journalLines->sum('credit');
    $balance = $totalDebit - $totalCredit;
    
    echo "- {$coa->kode_akun} - {$coa->nama_akun}";
    if ($balance > 0) {
        echo " - Balance: Rp " . number_format($balance, 0, ',', '.');
    }
    echo "\n";
}

// Check production-related accounts that might be HPP
echo "\n3. Production-related accounts (might contain HPP data):\n";
$productionAccounts = \App\Models\Coa::where(function($query) {
    $query->where('nama_akun', 'like', '%HPP%')
          ->orWhere('nama_akun', 'like', '%Harga Pokok%')
          ->orWhere('nama_akun', 'like', '%Pokok Penjualan%')
          ->orWhere('nama_akun', 'like', '%BBB%')
          ->orWhere('nama_akun', 'like', '%BTKL%')
          ->orWhere('nama_akun', 'like', '%BOP%')
          ->orWhere('kode_akun', 'like', '5%'); // Cost accounts usually start with 5
})->get();

foreach ($productionAccounts as $coa) {
    $journalLines = \App\Models\JournalLine::where('coa_id', $coa->id)->get();
    $totalDebit = $journalLines->sum('debit');
    $totalCredit = $journalLines->sum('credit');
    $balance = $totalDebit - $totalCredit;
    
    echo "- {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})";
    if ($balance != 0) {
        echo " - Balance: Rp " . number_format($balance, 0, ',', '.');
    }
    echo "\n";
}

// Check if there are sales transactions and corresponding production costs
echo "\n4. Sales vs Production Analysis:\n";
$salesAccount = \App\Models\Coa::where('kode_akun', '41')->first();
if ($salesAccount) {
    $salesLines = \App\Models\JournalLine::where('coa_id', $salesAccount->id)->get();
    $salesCredit = $salesLines->sum('credit');
    echo "Total Sales (Credit): Rp " . number_format($salesCredit, 0, ',', '.') . "\n";
    
    // Check production costs that should become HPP
    $productionCosts = 0;
    
    // BBB (Biaya Bahan Baku)
    $bbbAccounts = \App\Models\Coa::where('kode_akun', 'like', '51%')->get();
    foreach ($bbbAccounts as $coa) {
        $lines = \App\Models\JournalLine::where('coa_id', $coa->id)->get();
        $balance = $lines->sum('debit') - $lines->sum('credit');
        $productionCosts += $balance;
        if ($balance > 0) {
            echo "BBB - {$coa->kode_akun} - {$coa->nama_akun}: Rp " . number_format($balance, 0, ',', '.') . "\n";
        }
    }
    
    // BTKL (Biaya Tenaga Kerja Langsung)
    $btklAccounts = \App\Models\Coa::where('kode_akun', 'like', '52%')->get();
    foreach ($btklAccounts as $coa) {
        $lines = \App\Models\JournalLine::where('coa_id', $coa->id)->get();
        $balance = $lines->sum('debit') - $lines->sum('credit');
        $productionCosts += $balance;
        if ($balance > 0) {
            echo "BTKL - {$coa->kode_akun} - {$coa->nama_akun}: Rp " . number_format($balance, 0, ',', '.') . "\n";
        }
    }
    
    // BOP (Biaya Overhead Pabrik)
    $bopAccounts = \App\Models\Coa::where('kode_akun', 'like', '53%')
                                  ->orWhere('kode_akun', 'like', '54%')
                                  ->orWhere('kode_akun', 'like', '55%')
                                  ->get();
    foreach ($bopAccounts as $coa) {
        $lines = \App\Models\JournalLine::where('coa_id', $coa->id)->get();
        $balance = $lines->sum('debit') - $lines->sum('credit');
        $productionCosts += $balance;
        if ($balance > 0) {
            echo "BOP - {$coa->kode_akun} - {$coa->nama_akun}: Rp " . number_format($balance, 0, ',', '.') . "\n";
        }
    }
    
    echo "\nTotal Production Costs: Rp " . number_format($productionCosts, 0, ',', '.') . "\n";
    echo "Gross Profit (Sales - Production Costs): Rp " . number_format($salesCredit - $productionCosts, 0, ',', '.') . "\n";
}

echo "\n=== ANALYSIS ===\n";
echo "The issue might be:\n";
echo "1. HPP accounts don't exist (need to create 16xx accounts)\n";
echo "2. Production costs (BBB, BTKL, BOP) are not being transferred to HPP\n";
echo "3. Laba Rugi controller is looking in wrong place for HPP\n";
echo "4. Need to create journal entries to transfer production costs to HPP when goods are sold\n";

echo "\n=== COMPLETED ===\n";