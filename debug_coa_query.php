<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 DEBUGGING COA QUERY FOR USER 4\n";
echo "===================================\n\n";

$userId = 4;

// Test 1: Raw count
echo "1️⃣ Total COA in database for user 4:\n";
$totalCount = DB::table('coas')->where('user_id', $userId)->count();
echo "   Total: $totalCount\n\n";

// Test 2: Count with nama_akun filter (like in controller)
echo "2️⃣ COA with nama_akun NOT NULL and NOT empty:\n";
$filteredCount = DB::table('coas')
    ->whereNotNull('nama_akun')
    ->where('nama_akun', '!=', '')
    ->where('user_id', $userId)
    ->count();
echo "   Total: $filteredCount\n\n";

// Test 3: Show COA with empty or null nama_akun
echo "3️⃣ COA with NULL or empty nama_akun:\n";
$emptyNama = DB::table('coas')
    ->where('user_id', $userId)
    ->where(function($query) {
        $query->whereNull('nama_akun')
              ->orWhere('nama_akun', '');
    })
    ->get(['id', 'kode_akun', 'nama_akun', 'tipe_akun']);

if ($emptyNama->count() > 0) {
    echo "   Found {$emptyNama->count()} COA with empty nama_akun:\n";
    foreach ($emptyNama as $coa) {
        echo "      ID: {$coa->id}, Kode: {$coa->kode_akun}, Nama: '{$coa->nama_akun}', Tipe: {$coa->tipe_akun}\n";
    }
} else {
    echo "   ✅ No COA with empty nama_akun\n";
}

echo "\n";

// Test 4: Show all COA
echo "4️⃣ All COA for user 4 (first 10):\n";
$allCoas = DB::table('coas')
    ->where('user_id', $userId)
    ->orderBy('kode_akun')
    ->limit(10)
    ->get(['id', 'kode_akun', 'nama_akun', 'tipe_akun']);

foreach ($allCoas as $coa) {
    $namaDisplay = $coa->nama_akun ?: '(empty)';
    echo "   {$coa->kode_akun} - {$namaDisplay} ({$coa->tipe_akun})\n";
}

echo "\n";

// Test 5: Simulate controller query
echo "5️⃣ Simulating controller query:\n";
$coas = \App\Models\Coa::whereNotNull('nama_akun')
    ->where('nama_akun', '!=', '')
    ->where('user_id', $userId)
    ->orderBy('kode_akun')
    ->get();

echo "   Result count: {$coas->count()}\n";
echo "   First 5 results:\n";
foreach ($coas->take(5) as $coa) {
    echo "      {$coa->kode_akun} - {$coa->nama_akun}\n";
}

echo "\n";

// Test 6: Check if there's any issue with the model
echo "6️⃣ Checking Coa model table name:\n";
$model = new \App\Models\Coa();
echo "   Table name: {$model->getTable()}\n";
echo "   Connection: {$model->getConnectionName()}\n";
