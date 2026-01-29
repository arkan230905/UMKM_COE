<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing no minus display...\n";

try {
    // Test the current balances
    echo "\n=== TESTING CURRENT BALANCES ===\n";
    $kasbank = \App\Models\Coa::whereIn('kode_akun', ['1101', '1102', '1103'])
        ->where('tipe_akun', 'Asset')
        ->where('is_akun_header', '!=', 1)
        ->orderBy('kode_akun')
        ->get();
    
    $currentBalances = [];
    foreach ($kasbank as $bank) {
        $account = \App\Models\Account::where('code', $bank->kode_akun)->first();
        if ($account) {
            $journalLines = \App\Models\JournalLine::where('account_id', $account->id)->get();
            $totalDebit = $journalLines->sum('debit');
            $totalCredit = $journalLines->sum('credit');
            $currentBalance = $totalDebit - $totalCredit;
            $currentBalances[$bank->kode_akun] = $currentBalance;
            
            echo "Account {$bank->kode_akun}: Raw Balance = {$currentBalance}\n";
        }
    }
    
    // Test display with abs()
    echo "\n=== DISPLAY WITH ABS() ===\n";
    foreach ($kasbank as $bank) {
        $balance = $currentBalances[$bank->kode_akun] ?? 0;
        $displayBalance = abs($balance);
        
        echo "Dropdown: {$bank->nama_akun} - {$bank->kode_akun}\n";
        echo "  Raw: {$balance}\n";
        echo "  Display: {$displayBalance}\n";
        echo "  Formatted: Rp " . number_format($displayBalance, 0, ',', '.') . "\n";
        echo "\n";
    }
    
    echo "=== EXPECTED RESULT ===\n";
    echo "Kas (1101): Raw -13.463.500 -> Display 13.463.500 -> Rp 13.463.500\n";
    echo "Bank BCA (1102): Raw -9.000.000 -> Display 9.000.000 -> Rp 9.000.000\n";
    echo "Bank BNI (1103): Raw 0 -> Display 0 -> Rp 0\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "Done.\n";
