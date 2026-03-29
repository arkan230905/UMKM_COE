<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing final fix for account_id issues...\n";

// Test 1: Laba Rugi Query
echo "\n1. Testing Laba Rugi queries...\n";
try {
    $revenue = \App\Models\Coa::where('tipe_akun','Revenue')->get();
    $expense = \App\Models\Coa::where('tipe_akun','Expense')->get();
    
    echo "✓ Revenue accounts: " . $revenue->count() . "\n";
    echo "✓ Expense accounts: " . $expense->count() . "\n";
    
    // Test query for first revenue account
    if ($revenue->count() > 0) {
        $acc = $revenue->first();
        $q = \App\Models\JournalLine::where('coa_id', $acc->id);
        $row = $q->selectRaw('COALESCE(SUM(credit - debit),0) as bal')->first();
        echo "✓ Revenue query works for {$acc->nama_akun}\n";
    }
    
} catch (Exception $e) {
    echo "✗ Laba Rugi test failed: " . $e->getMessage() . "\n";
}

// Test 2: AccountHelper
echo "\n2. Testing AccountHelper...\n";
try {
    $kasBank = \App\Helpers\AccountHelper::getKasBankAccounts();
    echo "✓ Kas/Bank accounts found: " . $kasBank->count() . "\n";
    
    if ($kasBank->count() > 0) {
        $akun = $kasBank->first();
        $saldo = \App\Helpers\AccountHelper::getCurrentBalance($akun->kode_akun);
        echo "✓ Balance calculation works for {$akun->nama_akun}: Rp " . number_format($saldo, 0, ',', '.') . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ AccountHelper test failed: " . $e->getMessage() . "\n";
}

// Test 3: Journal Entry for Pelunasan Utang
echo "\n3. Testing Pelunasan Utang journal integration...\n";
try {
    $pelunasan = \App\Models\PelunasanUtang::with(['akunKas'])->first();
    if ($pelunasan) {
        $journalEntries = \App\Models\JournalEntry::where('ref_type', 'debt_payment')
            ->where('ref_id', $pelunasan->id)
            ->count();
        echo "✓ Pelunasan utang journal entries: " . $journalEntries . "\n";
        
        if ($pelunasan->akunKas) {
            $balance = \App\Models\JournalLine::where('coa_id', $pelunasan->akunKas->id)
                ->selectRaw('SUM(debit) - SUM(credit) as balance')
                ->value('balance') ?? 0;
            echo "✓ Account balance calculation works: Rp " . number_format($balance, 0, ',', '.') . "\n";
        }
    } else {
        echo "- No pelunasan utang records found\n";
    }
    
} catch (Exception $e) {
    echo "✗ Pelunasan Utang test failed: " . $e->getMessage() . "\n";
}

echo "\nAll tests completed!\n";