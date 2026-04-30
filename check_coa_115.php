<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "COA dengan prefix 115:\n";
echo str_repeat("=", 80) . "\n";

$coas = \App\Models\Coa::withoutGlobalScopes()
    ->where('user_id', 1)
    ->where('kode_akun', 'LIKE', '115%')
    ->orderBy('kode_akun')
    ->get();

foreach($coas as $coa) {
    echo $coa->kode_akun . ' - ' . $coa->nama_akun . ' (Saldo: Rp ' . number_format($coa->saldo ?? 0, 0, ',', '.') . ')' . PHP_EOL;
}

echo "\nTotal: " . $coas->count() . " COA\n";
