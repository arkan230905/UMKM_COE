<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: Data yang dikirim ke view ===" . PHP_EOL;

// Simulasi query di controller
$akunBeban = \App\Models\BopLainnya::where('budget', '>', 0)
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

echo "Total akun beban: " . $akunBeban->count() . PHP_EOL . PHP_EOL;

foreach ($akunBeban as $beban) {
    echo "ID: {$beban->id}" . PHP_EOL;
    echo "kode_akun: {$beban->kode_akun}" . PHP_EOL;
    echo "nama_akun: {$beban->nama_akun}" . PHP_EOL;
    echo "kode: " . (isset($beban->kode) ? $beban->kode : 'NULL') . PHP_EOL;
    echo "nama: " . (isset($beban->nama) ? $beban->nama : 'NULL') . PHP_EOL;
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "=== Test view rendering ===" . PHP_EOL;

// Simulasi rendering view
foreach ($akunBeban as $beban) {
    $optionValue = $beban->id;
    $optionText = isset($beban->kode_akun) ? 
        ($beban->kode_akun . ' - ' . $beban->nama_akun) : 
        'ERROR - Missing properties';
    
    echo "<option value=\"{$optionValue}\">{$optionText}</option>" . PHP_EOL;
}
