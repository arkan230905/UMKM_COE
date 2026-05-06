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

echo "=== FIXING JAGUNG FINAL ===\n\n";

// Login sebagai user yang bermasalah
$userId = 2;
$user = User::find($userId);
auth()->loginUsingId($userId);

$jagung = BahanBaku::where('nama_bahan', 'like', '%jagung%')->first();

echo "Fixing: {$jagung->nama_bahan} (ID: {$jagung->id})\n";

// Cek stock movements saat ini
$movements = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->get();

echo "Current stock movements:\n";
foreach ($movements as $movement) {
    echo "- ID: {$movement->id} | {$movement->tanggal} | {$movement->direction} | {$movement->qty} kg | {$movement->ref_type}\n";
}

$totalIn = $movements->where('direction', 'in')->sum('qty');
echo "Total IN: {$totalIn} kg\n";
echo "Expected: saldo_awal (12) + purchase (10) = 22 kg\n";
echo "Actual IN: {$totalIn} kg\n";

if ($totalIn > 10) {
    echo "\nThere's still duplicate data. Cleaning up...\n";
    
    // Hapus semua stock movements
    DB::table('stock_movements')
        ->where('item_type', 'material')
        ->where('item_id', $jagung->id)
        ->where('user_id', $userId)
        ->delete();
    
    echo "Deleted all stock movements\n";
    
    // Buat hanya 1 stock movement yang benar (pembelian 10 kg)
    DB::table('stock_movements')->insert([
        'user_id' => $userId,
        'item_type' => 'material',
        'item_id' => $jagung->id,
        'tanggal' => '2026-05-06',
        'direction' => 'in',
        'qty' => 10.0000,
        'satuan' => 'KG',
        'unit_cost' => 50000,
        'total_cost' => 500000,
        'ref_type' => 'purchase',
        'ref_id' => 1,
        'keterangan' => 'Pembelian jagung 10 kg',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "Created correct purchase movement: 10 kg\n";
    
    // Update field stok
    $correctStock = 22; // 12 (saldo_awal) + 10 (pembelian)
    DB::table('bahan_bakus')
        ->where('id', $jagung->id)
        ->update(['stok' => $correctStock]);
    
    echo "Updated field 'stok' to: {$correctStock} kg\n";
}

// Verifikasi final
$jagung->refresh();
echo "\nFinal verification:\n";
echo "- saldo_awal: {$jagung->saldo_awal} kg\n";
echo "- stok_real_time: {$jagung->stok_real_time} kg\n";
echo "- stok accessor: {$jagung->stok} kg\n";

$movementsAfter = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->get();

echo "- Stock movements: " . $movementsAfter->count() . "\n";
foreach ($movementsAfter as $movement) {
    echo "  * {$movement->tanggal}: {$movement->direction} {$movement->qty} kg ({$movement->ref_type})\n";
}

$expected = $jagung->saldo_awal + $movementsAfter->where('direction', 'in')->sum('qty');
echo "- Expected total: {$expected} kg\n";

if ($jagung->stok_real_time == 22 && $jagung->stok == 22) {
    echo "\n🎉 SUCCESS: Jagung stock is now correct (22 kg)!\n";
} else {
    echo "\n❌ ERROR: Still inconsistent\n";
}

echo "\n=== FIX COMPLETED ===\n";