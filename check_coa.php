<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING REQUIRED COA ===" . PHP_EOL . PHP_EOL;

// Check HPP COA
$hppCoa = \App\Models\Coa::where('kode_akun', '560')->first();
echo "COA 560 (HPP): " . ($hppCoa ? "✅ {$hppCoa->nama_akun}" : "❌ NOT FOUND") . PHP_EOL;

// Check Persediaan COA
$persediaanCoa = \App\Models\Coa::where('kode_akun', 'like', '116%')->first();
echo "COA 116 (Persediaan): " . ($persediaanCoa ? "✅ {$persediaanCoa->kode_akun} - {$persediaanCoa->nama_akun}" : "❌ NOT FOUND") . PHP_EOL;

// Check Pendapatan COA
$pendapatanCoa = \App\Models\Coa::where('kode_akun', '41')->first();
echo "COA 41 (Pendapatan): " . ($pendapatanCoa ? "✅ {$pendapatanCoa->nama_akun}" : "❌ NOT FOUND") . PHP_EOL;

// Check Kas COA
$kasCoa = \App\Models\Coa::where('kode_akun', '112')->first();
echo "COA 112 (Kas): " . ($kasCoa ? "✅ {$kasCoa->nama_akun}" : "❌ NOT FOUND") . PHP_EOL;

echo PHP_EOL;

// List all COA for reference
echo "=== ALL COA (first 20) ===" . PHP_EOL;
$allCoa = \App\Models\Coa::orderBy('kode_akun')->limit(20)->get();
foreach ($allCoa as $coa) {
    echo "{$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})" . PHP_EOL;
}
