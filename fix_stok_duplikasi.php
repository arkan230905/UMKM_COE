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

echo "=== FIXING STOCK DUPLICATION ISSUE ===\n\n";

// Login sebagai user yang sedang bermasalah (user ID 2 - nayla dzakira)
$userId = 2;
$user = User::find($userId);

if (!$user) {
    echo "User ID {$userId} not found!\n";
    exit(1);
}

echo "Working with user: {$user->name} (ID: {$userId})\n";
auth()->loginUsingId($userId);

// Cari bahan baku Jagung
$jagung = BahanBaku::where('nama_bahan', 'like', '%jagung%')->first();

if (!$jagung) {
    echo "Jagung not found!\n";
    exit(1);
}

echo "\nAnalyzing: {$jagung->nama_bahan} (ID: {$jagung->id})\n";

// 1. Cek data di database
$rawStok = DB::table('bahan_bakus')->where('id', $jagung->id)->value('stok');
$saldoAwal = DB::table('bahan_bakus')->where('id', $jagung->id)->value('saldo_awal');

echo "Raw data from database:\n";
echo "- Field 'stok': {$rawStok}\n";
echo "- Field 'saldo_awal': {$saldoAwal}\n";

// 2. Cek stock movements
echo "\nStock movements for this user:\n";
$stockMovements = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->where('user_id', $userId)
    ->orderBy('tanggal', 'asc')
    ->get();

$totalIn = 0;
$totalOut = 0;

foreach ($stockMovements as $movement) {
    echo "- {$movement->tanggal}: {$movement->direction} {$movement->qty} kg ({$movement->ref_type})\n";
    if ($movement->direction === 'in') {
        $totalIn += $movement->qty;
    } else {
        $totalOut += $movement->qty;
    }
}

$netStock = $totalIn - $totalOut;
echo "\nCalculation:\n";
echo "- Total IN: {$totalIn} kg\n";
echo "- Total OUT: {$totalOut} kg\n";
echo "- Net Stock: {$netStock} kg\n";

// 3. Cek apakah ada duplikasi stock movements
echo "\nChecking for duplicates...\n";
$duplicates = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->where('user_id', $userId)
    ->select('tanggal', 'direction', 'qty', 'ref_type', 'ref_id', DB::raw('COUNT(*) as count'))
    ->groupBy('tanggal', 'direction', 'qty', 'ref_type', 'ref_id')
    ->having('count', '>', 1)
    ->get();

if ($duplicates->count() > 0) {
    echo "Found duplicates:\n";
    foreach ($duplicates as $dup) {
        echo "- {$dup->tanggal}: {$dup->direction} {$dup->qty} kg ({$dup->ref_type}) - {$dup->count} times\n";
        
        // Hapus duplikasi, sisakan 1
        $movementsToDelete = StockMovement::where('item_type', 'material')
            ->where('item_id', $jagung->id)
            ->where('user_id', $userId)
            ->where('tanggal', $dup->tanggal)
            ->where('direction', $dup->direction)
            ->where('qty', $dup->qty)
            ->where('ref_type', $dup->ref_type)
            ->where('ref_id', $dup->ref_id)
            ->skip(1) // Skip first one
            ->take($dup->count - 1) // Take the rest
            ->get();
            
        foreach ($movementsToDelete as $toDelete) {
            echo "  Deleting duplicate movement ID: {$toDelete->id}\n";
            $toDelete->delete();
        }
    }
} else {
    echo "No duplicates found.\n";
}

// 4. Recalculate after cleanup
echo "\nRecalculating after cleanup...\n";
$stockMovementsAfter = StockMovement::where('item_type', 'material')
    ->where('item_id', $jagung->id)
    ->where('user_id', $userId)
    ->get();

$totalInAfter = $stockMovementsAfter->where('direction', 'in')->sum('qty');
$totalOutAfter = $stockMovementsAfter->where('direction', 'out')->sum('qty');
$netStockAfter = $totalInAfter - $totalOutAfter;

echo "After cleanup:\n";
echo "- Total IN: {$totalInAfter} kg\n";
echo "- Total OUT: {$totalOutAfter} kg\n";
echo "- Net Stock: {$netStockAfter} kg\n";

// 5. Update field stok di database jika perlu
if (abs($rawStok - $netStockAfter) > 0.01) {
    echo "\nUpdating field 'stok' from {$rawStok} to {$netStockAfter}\n";
    DB::table('bahan_bakus')
        ->where('id', $jagung->id)
        ->update(['stok' => $netStockAfter]);
    echo "✅ Updated successfully\n";
} else {
    echo "\n✅ Field 'stok' is already correct\n";
}

// 6. Test accessor
$jagung->refresh();
echo "\nFinal verification:\n";
echo "- stok_real_time: {$jagung->stok_real_time}\n";
echo "- stok accessor: {$jagung->stok}\n";
echo "- Raw field: " . DB::table('bahan_bakus')->where('id', $jagung->id)->value('stok') . "\n";

echo "\n=== STOCK DUPLICATION FIX COMPLETED ===\n";