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

echo "=== SIMPLE STOCK CLEANUP ===\n\n";

// Login sebagai user yang bermasalah
$userId = 2;
$user = User::find($userId);
auth()->loginUsingId($userId);

$jagung = BahanBaku::where('nama_bahan', 'like', '%jagung%')->first();

echo "Cleaning stock for: {$jagung->nama_bahan} (ID: {$jagung->id})\n";
echo "User: {$user->name} (ID: {$userId})\n\n";

// 1. Hapus semua stock movements untuk bahan ini (langsung dari database)
echo "Deleting all stock movements for this item...\n";
$deletedCount = DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->where('user_id', $userId)
    ->delete();

echo "Deleted {$deletedCount} stock movements\n";

// 2. Buat ulang stock movement yang benar (hanya pembelian 10 kg)
echo "Creating correct stock movement (purchase 10 kg)...\n";
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

echo "Created new purchase stock movement\n";

// 3. Update field stok di bahan_bakus
$correctStock = 22; // 12 (saldo_awal) + 10 (pembelian) = 22
DB::table('bahan_bakus')
    ->where('id', $jagung->id)
    ->update(['stok' => $correctStock]);

echo "Updated field 'stok' to: {$correctStock} kg\n";

// 4. Verifikasi hasil
echo "\nVerification:\n";
$jagung->refresh();

$saldoAwal = $jagung->saldo_awal;
$stokRealTime = $jagung->stok_real_time;
$stokAccessor = $jagung->stok;

echo "- saldo_awal: {$saldoAwal} kg\n";
echo "- stok_real_time: {$stokRealTime} kg\n";
echo "- stok accessor: {$stokAccessor} kg\n";

// Cek stock movements
$movements = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->where('user_id', $userId)
    ->get();

echo "- Stock movements count: " . $movements->count() . "\n";
foreach ($movements as $movement) {
    echo "  * {$movement->tanggal}: {$movement->direction} {$movement->qty} kg ({$movement->ref_type})\n";
}

$expectedTotal = $saldoAwal + $movements->where('direction', 'in')->sum('qty') - $movements->where('direction', 'out')->sum('qty');
echo "- Expected total: {$expectedTotal} kg\n";

if ($expectedTotal == 22) {
    echo "\n✅ SUCCESS: Stock is now correct (22 kg)\n";
} else {
    echo "\n❌ ERROR: Stock is still incorrect\n";
}

echo "\n=== CLEANUP COMPLETED ===\n";