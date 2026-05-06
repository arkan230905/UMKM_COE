<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== FINAL STOCK TEST ===\n\n";

// Login sebagai user yang bermasalah
$userId = 2;
$user = User::find($userId);
auth()->loginUsingId($userId);

$jagung = BahanBaku::where('nama_bahan', 'like', '%jagung%')->first();

echo "Testing: {$jagung->nama_bahan} (ID: {$jagung->id})\n";
echo "User: {$user->name} (ID: {$userId})\n\n";

// Test semua method perhitungan stok
echo "Stock calculations:\n";
echo "1. saldo_awal: {$jagung->saldo_awal} kg\n";
echo "2. stok_real_time: {$jagung->stok_real_time} kg\n";
echo "3. stok accessor: {$jagung->stok} kg\n";
echo "4. Raw field 'stok': " . DB::table('bahan_bakus')->where('id', $jagung->id)->value('stok') . " kg\n";

// Cek stock movements
echo "\nStock movements:\n";
$movements = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->get();

$totalIn = 0;
$totalOut = 0;

foreach ($movements as $movement) {
    echo "- {$movement->tanggal}: {$movement->direction} {$movement->qty} kg ({$movement->ref_type})\n";
    if ($movement->direction === 'in') {
        $totalIn += $movement->qty;
    } else {
        $totalOut += $movement->qty;
    }
}

echo "\nCalculation breakdown:\n";
echo "- Saldo awal: {$jagung->saldo_awal} kg\n";
echo "- Total IN movements: {$totalIn} kg\n";
echo "- Total OUT movements: {$totalOut} kg\n";
echo "- Expected total: " . ($jagung->saldo_awal + $totalIn - $totalOut) . " kg\n";

// Test apakah semua method memberikan hasil yang sama
$expected = $jagung->saldo_awal + $totalIn - $totalOut;
$realTime = $jagung->stok_real_time;
$accessor = $jagung->stok;

echo "\nConsistency check:\n";
echo "- Expected: {$expected} kg\n";
echo "- Real-time: {$realTime} kg " . ($realTime == $expected ? "✅" : "❌") . "\n";
echo "- Accessor: {$accessor} kg " . ($accessor == $expected ? "✅" : "❌") . "\n";

if ($expected == 22 && $realTime == 22 && $accessor == 22) {
    echo "\n🎉 SUCCESS: All calculations are correct (22 kg)!\n";
    echo "✅ Saldo awal (12 kg) + Pembelian (10 kg) = 22 kg\n";
} else {
    echo "\n❌ ERROR: Calculations are inconsistent\n";
}

echo "\n=== TEST COMPLETED ===\n";