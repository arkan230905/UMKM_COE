<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BopLainnya;
use App\Models\Coa;

echo "=== DEBUG: Pembayaran Beban Dropdown ===" . PHP_EOL;

// Cek BOP Lainnya dengan budget > 0
$bopLainnya = BopLainnya::where('budget', '>', 0)
    ->where('is_active', true)
    ->with(['coa'])
    ->get();

echo "Total BOP Lainnya dengan budget > 0: " . $bopLainnya->count() . PHP_EOL . PHP_EOL;

foreach ($bopLainnya as $bop) {
    echo "- BOP ID: {$bop->id} | Kode: {$bop->kode_akun} | Budget: {$bop->budget} | Aktual: {$bop->aktual}" . PHP_EOL;
    if ($bop->coa) {
        echo "  COA: {$bop->coa->kode_akun} - {$bop->coa->nama_akun} | Active: " . ($bop->coa->is_active ? 'Yes' : 'No') . PHP_EOL;
    } else {
        echo "  COA: NULL" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Mapping ke COA ===" . PHP_EOL;

$akunBeban = $bopLainnya->map(function($bop) {
    return $bop->coa;
})->filter(function($coa) {
    return $coa && $coa->is_active;
});

echo "Total akun beban yang akan ditampilkan: " . $akunBeban->count() . PHP_EOL . PHP_EOL;

foreach ($akunBeban as $coa) {
    echo "- {$coa->kode_akun} - {$coa->nama_akun}" . PHP_EOL;
}

echo PHP_EOL . "=== Perbandingan dengan cara lama ===" . PHP_EOL;

$akunBebanLama = Coa::where('kode_akun', 'like', '5%')
    ->orderBy('kode_akun')
    ->get();

echo "Total akun beban (cara lama): " . $akunBebanLama->count() . PHP_EOL;

foreach ($akunBebanLama as $coa) {
    echo "- {$coa->kode_akun} - {$coa->nama_akun}" . PHP_EOL;
}
