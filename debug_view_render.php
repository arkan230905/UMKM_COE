<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: View Rendering Simulation ===" . PHP_EOL;

// Simulasi data dari controller
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

echo "Data yang akan dikirim ke view:" . PHP_EOL;
echo "akunBeban count: " . $akunBeban->count() . PHP_EOL . PHP_EOL;

foreach ($akunBeban as $beban) {
    echo "- ID: {$beban->id}" . PHP_EOL;
    echo "- kode_akun: {$beban->kode_akun}" . PHP_EOL;
    echo "- nama_akun: {$beban->nama_akun}" . PHP_EOL;
    echo "- Property exists check:" . PHP_EOL;
    echo "  - isset(\$beban->id): " . (isset($beban->id) ? 'YES' : 'NO') . PHP_EOL;
    echo "  - isset(\$beban->kode_akun): " . (isset($beban->kode_akun) ? 'YES' : 'NO') . PHP_EOL;
    echo "  - isset(\$beban->nama_akun): " . (isset($beban->nama_akun) ? 'YES' : 'NO') . PHP_EOL;
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "=== Simulasi @foreach loop ===" . PHP_EOL;

if ($akunBeban->isEmpty()) {
    echo "akunBeban is EMPTY - tidak akan ada @foreach" . PHP_EOL;
} else {
    echo "akunBeban NOT EMPTY - akan menjalankan @foreach" . PHP_EOL;
    foreach ($akunBeban as $beban) {
        $optionValue = $beban->id ?? 'NULL_ID';
        $optionText = ($beban->kode_akun ?? 'NULL_KODE') . ' - ' . ($beban->nama_akun ?? 'NULL_NAMA');
        echo "<option value=\"{$optionValue}\">{$optionText}</option>" . PHP_EOL;
    }
}
