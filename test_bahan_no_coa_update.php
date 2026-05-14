<?php

/**
 * Script untuk test bahwa input bahan TIDAK mengupdate COA saldo_awal
 * Jalankan: php test_bahan_no_coa_update.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;
use App\Models\Satuan;
use App\Models\StockMovement;

echo "🧪 TEST: Input Bahan TIDAK Update COA Saldo Awal\n";
echo "==================================================\n\n";

// Login as user 1
auth()->loginUsingId(1);

try {
    // 1. Get COA Persediaan untuk test
    $coaPersediaan = Coa::where('kode_akun', 'LIKE', '114%')
        ->where('user_id', 1)
        ->first();
    
    if (!$coaPersediaan) {
        echo "❌ COA Persediaan tidak ditemukan\n";
        exit(1);
    }
    
    echo "📋 COA Test: {$coaPersediaan->kode_akun} - {$coaPersediaan->nama_akun}\n";
    echo "   Saldo Awal Sebelum: Rp " . number_format($coaPersediaan->saldo_awal, 0, ',', '.') . "\n\n";
    
    // 2. Get Satuan
    $satuan = Satuan::where('user_id', 1)->first();
    
    if (!$satuan) {
        echo "❌ Satuan tidak ditemukan\n";
        exit(1);
    }
    
    // 3. Create Bahan Baku Test
    echo "🔨 Membuat Bahan Baku Test...\n";
    
    $bahanBaku = BahanBaku::create([
        'user_id' => 1,
        'kode_bahan' => 'TEST-BB-' . time(),
        'nama_bahan' => 'Test Bahan Baku ' . time(),
        'satuan_id' => $satuan->id,
        'saldo_awal' => 100, // 100 unit
        'harga_satuan' => 5000, // Rp 5.000 per unit
        'stok_minimum' => 10,
        'coa_persediaan_id' => $coaPersediaan->kode_akun,
        'tanggal_saldo_awal' => now(),
    ]);
    
    echo "✅ Bahan Baku Created: {$bahanBaku->nama_bahan}\n";
    echo "   Stok: {$bahanBaku->saldo_awal} unit\n";
    echo "   Harga: Rp " . number_format($bahanBaku->harga_satuan, 0, ',', '.') . "\n";
    echo "   Total Nilai: Rp " . number_format($bahanBaku->saldo_awal * $bahanBaku->harga_satuan, 0, ',', '.') . "\n\n";
    
    // 4. Check StockMovement created
    $stockMovement = StockMovement::where('item_type', 'material')
        ->where('item_id', $bahanBaku->id)
        ->where('ref_type', 'initial_stock')
        ->first();
    
    if ($stockMovement) {
        echo "✅ StockMovement Created\n";
        echo "   Qty: {$stockMovement->qty}\n";
        echo "   Unit Cost: Rp " . number_format($stockMovement->unit_cost, 0, ',', '.') . "\n";
        echo "   Total Cost: Rp " . number_format($stockMovement->total_cost, 0, ',', '.') . "\n\n";
    } else {
        echo "⚠️  StockMovement TIDAK dibuat\n\n";
    }
    
    // 5. Check COA Saldo Awal (SHOULD NOT CHANGE)
    $coaPersediaan->refresh();
    
    echo "🔍 Verifikasi COA Saldo Awal...\n";
    echo "   Saldo Awal Sesudah: Rp " . number_format($coaPersediaan->saldo_awal, 0, ',', '.') . "\n\n";
    
    if ($coaPersediaan->saldo_awal == 0) {
        echo "✅ SUKSES: COA saldo_awal TIDAK berubah (tetap 0)\n";
        echo "✅ Logika update COA saldo_awal BERHASIL DINONAKTIFKAN\n\n";
    } else {
        echo "❌ GAGAL: COA saldo_awal berubah menjadi Rp " . number_format($coaPersediaan->saldo_awal, 0, ',', '.') . "\n";
        echo "❌ Logika update COA saldo_awal MASIH AKTIF!\n\n";
    }
    
    // 6. Cleanup - Delete test data
    echo "🧹 Cleanup test data...\n";
    
    if ($stockMovement) {
        $stockMovement->delete();
        echo "   ✅ StockMovement deleted\n";
    }
    
    $bahanBaku->delete();
    echo "   ✅ Bahan Baku deleted\n\n";
    
    echo "==================================================\n";
    echo "✅ TEST SELESAI\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
