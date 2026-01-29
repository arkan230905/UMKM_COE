<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking COA accounts for HPP...\n";

try {
    // Cek akun yang ada di COA
    $coas = \App\Models\Coa::orderBy('code')->get();
    
    echo "Existing COA accounts related to HPP/Cost:\n";
    foreach($coas as $coa) {
        if (stripos($coa->name, 'hpp') !== false || 
            stripos($coa->name, 'harga pokok') !== false ||
            stripos($coa->name, 'cost') !== false ||
            stripos($coa->name, 'beban') !== false ||
            stripos($coa->name, 'bop') !== false) {
            echo "  Code: {$coa->code} - Name: {$coa->name}\n";
        }
    }
    
    // Cek akun yang digunakan saat ini
    echo "\nCurrent HPP account being used:\n";
    $hppAccount = \App\Models\Account::where('code', '5102')->first();
    if ($hppAccount) {
        echo "  Code: {$hppAccount->code} - Name: {$hppAccount->name}\n";
    } else {
        echo "  Account 5102 not found in Accounts table\n";
    }
    
    // Cek range untuk akun beban (5xxx)
    echo "\nAccounts in 5xxx range (Expense accounts):\n";
    $expenseAccounts = \App\Models\Coa::where('code', 'like', '5%')->orderBy('code')->get();
    foreach($expenseAccounts as $coa) {
        echo "  Code: {$coa->code} - Name: {$coa->name}\n";
    }
    
    // Suggest proper HPP account
    echo "\nSuggested HPP account codes:\n";
    echo "  5101 - Harga Pokok Penjualan (HPP)\n";
    echo "  5201 - Harga Pokok Produksi\n";
    echo "  5301 - Cost of Goods Sold (COGS)\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "Done.\n";
