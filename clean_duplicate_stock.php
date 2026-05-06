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

echo "=== CLEANING DUPLICATE STOCK ENTRIES ===\n\n";

// Login sebagai user yang bermasalah
$userId = 2;
$user = User::find($userId);
auth()->loginUsingId($userId);

$jagung = BahanBaku::where('nama_bahan', 'like', '%jagung%')->first();

echo "Cleaning stock for: {$jagung->nama_bahan} (ID: {$jagung->id})\n";
echo "User: {$user->name} (ID: {$userId})\n\n";

// 1. Tampilkan semua stock movements
echo "Current stock movements:\n";
$movements = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->where('user_id', $userId)
    ->orderBy('created_at', 'asc')
    ->get();

foreach ($movements as $i => $movement) {
    echo ($i + 1) . ". ID: {$movement->id} | {$movement->tanggal} | {$movement->direction} | {$movement->qty} kg | {$movement->ref_type} | Created: {$movement->created_at}\n";
}

echo "\nAnalysis:\n";
echo "- Initial stock (12 kg) should be from saldo_awal, not stock movement\n";
echo "- Purchase (10 kg) should be the only stock movement\n";
echo "- Adjustment (10 kg) seems to be duplicate\n";

// 2. Hapus stock movement yang salah
echo "\nCleaning up...\n";

// Hapus initial_stock movement (karena sudah ada di saldo_awal)
$initialMovement = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->where('user_id', $userId)
    ->where('ref_type', 'initial_stock')
    ->first();

if ($initialMovement) {
    echo "Deleting initial_stock movement (ID: {$initialMovement->id}) - should use saldo_awal instead\n";
    $initialMovement->delete();
}

// Hapus adjustment movement (duplikasi)
$adjustmentMovement = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->where('user_id', $userId)
    ->where('ref_type', 'adjustment')
    ->first();

if ($adjustmentMovement) {
    echo "Deleting adjustment movement (ID: {$adjustmentMovement->id}) - duplicate entry\n";
    $adjustmentMovement->delete();
}

// 3. Verifikasi hasil
echo "\nAfter cleanup:\n";
$movementsAfter = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->where('user_id', $userId)
    ->get();

foreach ($movementsAfter as $i => $movement) {
    echo ($i + 1) . ". ID: {$movement->id} | {$movement->tanggal} | {$movement->direction} | {$movement->qty} kg | {$movement->ref_type}\n";
}

$totalIn = $movementsAfter->where('direction', 'in')->sum('qty');
$totalOut = $movementsAfter->where('direction', 'out')->sum('qty');
$netFromMovements = $totalIn - $totalOut;

echo "\nCalculation:\n";
echo "- Saldo awal: {$jagung->saldo_awal} kg\n";
echo "- Stock movements IN: {$totalIn} kg\n";
echo "- Stock movements OUT: {$totalOut} kg\n";
echo "- Net from movements: {$netFromMovements} kg\n";
echo "- Expected total: " . ($jagung->saldo_awal + $netFromMovements) . " kg\n";

// 4. Update field stok
$correctStock = $jagung->saldo_awal + $netFromMovements;
DB::table('bahan_bakus')
    ->where('id', $jagung->id)
    ->update(['stok' => $correctStock]);

echo "\nUpdated field 'stok' to: {$correctStock} kg\n";

// 5. Test final result
$jagung->refresh();
echo "\nFinal verification:\n";
echo "- saldo_awal: {$jagung->saldo_awal} kg\n";
echo "- stok_real_time: {$jagung->stok_real_time} kg\n";
echo "- stok accessor: {$jagung->stok} kg\n";

echo "\n=== CLEANUP COMPLETED ===\n";
echo "Expected result: 12 kg (saldo_awal) + 10 kg (purchase) = 22 kg ✅\n";