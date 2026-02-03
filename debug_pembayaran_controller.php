<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: Simulasi PembayaranBebanController@create ===" . PHP_EOL;

// Simulasi query di controller
$akunBeban = \App\Models\BopLainnya::where('budget', '>', 0)
    ->where('is_active', true)
    ->with(['coa'])
    ->get()
    ->map(function($bop) {
        return $bop->coa;
    })
    ->filter(function($coa) {
        return $coa; // Hanya filter yang COA-nya ada
    });

echo "Total akun beban yang akan ditampilkan: " . $akunBeban->count() . PHP_EOL . PHP_EOL;

foreach ($akunBeban as $coa) {
    echo "- {$coa->kode_akun} - {$coa->nama_akun}" . PHP_EOL;
}

echo PHP_EOL . "=== Cek tipe data collection ===" . PHP_EOL;
echo "akunBeban type: " . get_class($akunBeban) . PHP_EOL;
echo "Is collection empty: " . ($akunBeban->isEmpty() ? 'Yes' : 'No') . PHP_EOL;

echo PHP_EOL . "=== Test dengan unique() ===" . PHP_EOL;

$akunBebanUnique = \App\Models\BopLainnya::where('budget', '>', 0)
    ->where('is_active', true)
    ->with(['coa'])
    ->get()
    ->map(function($bop) {
        return $bop->coa;
    })
    ->filter(function($coa) {
        return $coa;
    })
    ->unique('kode_akun');

echo "Total dengan unique(): " . $akunBebanUnique->count() . PHP_EOL;
foreach ($akunBebanUnique as $coa) {
    echo "- {$coa->kode_akun} - {$coa->nama_akun}" . PHP_EOL;
}
