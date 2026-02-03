<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: ExpensePaymentController@create ===" . PHP_EOL;

// Simulasi query di ExpensePaymentController
$bopLainnyaKodes = \App\Models\BopLainnya::where('budget', '>', 0)
    ->where('is_active', 1)
    ->pluck('kode_akun');

echo "Kode akun dari BOP Lainnya: " . $bopLainnyaKodes->implode(', ') . PHP_EOL;

$coas = \App\Models\Coa::whereIn('kode_akun', $bopLainnyaKodes)
    ->orderBy('kode_akun')
    ->get();

echo "Total COA untuk dropdown: " . $coas->count() . PHP_EOL . PHP_EOL;

foreach ($coas as $c) {
    echo "- {$c->kode_akun} - {$c->nama_akun}" . PHP_EOL;
}

echo PHP_EOL . "=== Test AccountHelper ===" . PHP_EOL;

try {
    $kasbank = \App\Helpers\AccountHelper::getKasBankAccounts();
    echo "Total kasbank accounts: " . $kasbank->count() . PHP_EOL;
    
    foreach ($kasbank as $k) {
        echo "- {$k->kode_akun} - {$k->nama_akun}" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "AccountHelper error: " . $e->getMessage() . PHP_EOL;
}
