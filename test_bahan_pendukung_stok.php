<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanPendukung;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TESTING BAHAN PENDUKUNG STOCK CALCULATION ===\n\n";

// Login sebagai user yang bermasalah
$userId = 2;
$user = User::find($userId);
auth()->loginUsingId($userId);

echo "Testing as user: {$user->name} (ID: {$userId})\n\n";

// Ambil semua bahan pendukung untuk user ini
$bahanPendukungs = BahanPendukung::all();

echo "Found " . $bahanPendukungs->count() . " bahan pendukung:\n\n";

foreach ($bahanPendukungs as $bahan) {
    echo "=== {$bahan->nama_bahan} (ID: {$bahan->id}) ===\n";
    
    // Test perhitungan stok
    $saldoAwal = $bahan->saldo_awal ?? 0;
    $stokRealTime = $bahan->stok_real_time ?? 0;
    $stokAccessor = $bahan->stok ?? 0;
    $rawStok = DB::table('bahan_pendukungs')->where('id', $bahan->id)->value('stok') ?? 0;
    
    echo "Stock calculations:\n";
    echo "- saldo_awal: {$saldoAwal}\n";
    echo "- stok_real_time: {$stokRealTime}\n";
    echo "- stok accessor: {$stokAccessor}\n";
    echo "- Raw field 'stok': {$rawStok}\n";
    
    // Cek stock movements
    $movements = StockMovement::where('item_type', 'support')
        ->where('item_id', $bahan->id)
        ->get();
    
    echo "Stock movements: " . $movements->count() . "\n";
    
    $totalIn = 0;
    $totalOut = 0;
    
    foreach ($movements as $movement) {
        echo "- {$movement->tanggal}: {$movement->direction} {$movement->qty} ({$movement->ref_type})\n";
        if ($movement->direction === 'in') {
            $totalIn += $movement->qty;
        } else {
            $totalOut += $movement->qty;
        }
    }
    
    $expectedTotal = $saldoAwal + $totalIn - $totalOut;
    echo "Expected total: {$saldoAwal} + {$totalIn} - {$totalOut} = {$expectedTotal}\n";
    
    // Check consistency
    $isConsistent = ($stokRealTime == $expectedTotal && $stokAccessor == $expectedTotal);
    echo "Consistency: " . ($isConsistent ? "✅ GOOD" : "❌ INCONSISTENT") . "\n";
    
    // Update raw field if needed
    if (abs($rawStok - $expectedTotal) > 0.01) {
        echo "Updating raw field from {$rawStok} to {$expectedTotal}\n";
        DB::table('bahan_pendukungs')
            ->where('id', $bahan->id)
            ->update(['stok' => $expectedTotal]);
    }
    
    echo "\n";
}

echo "=== TESTING COMPLETED ===\n";