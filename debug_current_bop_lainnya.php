<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BopLainnya;

echo "=== DEBUG: Current BOP Lainnya Data ===" . PHP_EOL;

// Ambil semua data BOP Lainnya
$bopLainnya = BopLainnya::all();

echo "Total BOP Lainnya: " . $bopLainnya->count() . PHP_EOL . PHP_EOL;

foreach ($bopLainnya as $bop) {
    echo "- ID: {$bop->id}" . PHP_EOL;
    echo "  Kode Akun: {$bop->kode_akun}" . PHP_EOL;
    echo "  Nama Akun: {$bop->nama_akun}" . PHP_EOL;
    echo "  Budget: " . number_format($bop->budget, 0) . PHP_EOL;
    echo "  Aktual: " . number_format($bop->aktual, 0) . PHP_EOL;
    echo "  Is Active: " . ($bop->is_active ? 'Yes' : 'No') . PHP_EOL;
    echo "  Periode: {$bop->periode}" . PHP_EOL;
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "=== Filter Budget > 0 ===" . PHP_EOL;

$bopDenganBudget = BopLainnya::where('budget', '>', 0)
    ->where('is_active', true)
    ->with(['coa'])
    ->get();

echo "Total dengan budget > 0 dan active: " . $bopDenganBudget->count() . PHP_EOL . PHP_EOL;

foreach ($bopDenganBudget as $bop) {
    echo "- ID: {$bop->id} | Kode: {$bop->kode_akun} | Budget: " . number_format($bop->budget, 0) . PHP_EOL;
    if ($bop->coa) {
        echo "  COA: {$bop->coa->kode_akun} - {$bop->coa->nama_akun}" . PHP_EOL;
    } else {
        echo "  COA: NULL" . PHP_EOL;
    }
    echo "---" . PHP_EOL;
}
