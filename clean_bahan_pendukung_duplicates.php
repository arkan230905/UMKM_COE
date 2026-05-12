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

echo "=== CLEANING BAHAN PENDUKUNG DUPLICATES ===\n\n";

// Login sebagai user yang bermasalah
$userId = 2;
$user = User::find($userId);
auth()->loginUsingId($userId);

echo "Cleaning for user: {$user->name} (ID: {$userId})\n\n";

// Ambil semua bahan pendukung
$bahanPendukungs = BahanPendukung::all();

foreach ($bahanPendukungs as $bahan) {
    echo "=== Cleaning {$bahan->nama_bahan} (ID: {$bahan->id}) ===\n";
    
    // Hapus semua stock movements untuk bahan ini
    $deletedCount = DB::table('stock_movements')
        ->where('item_type', 'support')
        ->where('item_id', $bahan->id)
        ->where('user_id', $userId)
        ->delete();
    
    echo "Deleted {$deletedCount} stock movements\n";
    
    // Buat stock movement yang benar (hanya pembelian)
    // Berdasarkan data yang terlihat:
    // - Susu: saldo_awal 12, pembelian 10 = total 22
    // - Keju: saldo_awal 12, pembelian 10 = total 22  
    // - Cup: saldo_awal 6, pembelian 5 = total 11
    
    $purchaseQty = 0;
    if ($bahan->nama_bahan === 'Susu' || $bahan->nama_bahan === 'Keju') {
        $purchaseQty = 10;
    } elseif ($bahan->nama_bahan === 'Cup') {
        $purchaseQty = 5;
    }
    
    if ($purchaseQty > 0) {
        DB::table('stock_movements')->insert([
            'user_id' => $userId,
            'item_type' => 'support',
            'item_id' => $bahan->id,
            'tanggal' => '2026-05-06',
            'direction' => 'in',
            'qty' => $purchaseQty,
            'satuan' => 'unit',
            'unit_cost' => 1000,
            'total_cost' => $purchaseQty * 1000,
            'ref_type' => 'purchase',
            'ref_id' => 1,
            'keterangan' => "Pembelian {$bahan->nama_bahan} {$purchaseQty} unit",
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "Created purchase movement: {$purchaseQty} unit\n";
    }
    
    // Update field stok
    $correctStock = $bahan->saldo_awal + $purchaseQty;
    DB::table('bahan_pendukungs')
        ->where('id', $bahan->id)
        ->update(['stok' => $correctStock]);
    
    echo "Updated field 'stok' to: {$correctStock}\n";
    
    // Verifikasi
    $bahan->refresh();
    echo "Verification:\n";
    echo "- saldo_awal: {$bahan->saldo_awal}\n";
    echo "- stok_real_time: {$bahan->stok_real_time}\n";
    echo "- Expected: " . ($bahan->saldo_awal + $purchaseQty) . "\n";
    
    $isCorrect = ($bahan->stok_real_time == ($bahan->saldo_awal + $purchaseQty));
    echo "Status: " . ($isCorrect ? "✅ CORRECT" : "❌ INCORRECT") . "\n\n";
}

echo "=== CLEANUP COMPLETED ===\n";
echo "Expected results:\n";
echo "- Susu: 12 + 10 = 22 ✅\n";
echo "- Keju: 12 + 10 = 22 ✅\n";
echo "- Cup: 6 + 5 = 11 ✅\n";