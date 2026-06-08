<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

auth()->loginUsingId(3);

echo "=== DEBUG COA QUERY FOR AYAM KAMPUNG ===\n\n";

$bb = \App\Models\BahanBaku::find(2);

echo "Bahan Baku: {$bb->nama_bahan}\n";
echo "coa_persediaan_id: {$bb->coa_persediaan_id}\n";
echo "user_id in session: " . auth()->id() . "\n\n";

echo "Query 1: WITH user_id filter (user_id=3)\n";
$coa = \App\Models\Coa::where('kode_akun', $bb->coa_persediaan_id)
    ->where('user_id', 3)
    ->first();

if ($coa) {
    echo "✓ FOUND: ID={$coa->id}, Kode={$coa->kode_akun}, Nama={$coa->nama_akun}, user_id={$coa->user_id}\n";
} else {
    echo "✗ NOT FOUND\n";
}

echo "\nQuery 2: WITHOUT user_id filter\n";
$coa2 = \App\Models\Coa::where('kode_akun', $bb->coa_persediaan_id)->first();

if ($coa2) {
    echo "✓ FOUND: ID={$coa2->id}, Kode={$coa2->kode_akun}, Nama={$coa2->nama_akun}, user_id={$coa2->user_id}\n";
} else {
    echo "✗ NOT FOUND\n";
}

echo "\nQuery 3: All COAs with kode_akun=1142\n";
$allCoas = \App\Models\Coa::where('kode_akun', '1142')->get();
echo "Found {$allCoas->count()} COA(s)\n";
foreach ($allCoas as $c) {
    echo "  - ID={$c->id}, Nama={$c->nama_akun}, user_id={$c->user_id}\n";
}

echo "\n=== SOLUTION ===\n";
if ($coa2 && $coa2->user_id != 3) {
    echo "❌ PROBLEM: COA exists but with wrong user_id ({$coa2->user_id})\n";
    echo "FIX: Update COA user_id to 3\n";
} elseif (!$coa2) {
    echo "❌ PROBLEM: COA doesn't exist in database\n";
    echo "FIX: Create COA or update bahan_baku coa_persediaan_id\n";
} else {
    echo "✓ COA exists with correct user_id\n";
}
