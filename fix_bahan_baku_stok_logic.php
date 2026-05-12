<?php

/**
 * PERBAIKAN LOGIKA STOK BAHAN BAKU
 * 
 * Masalah: Laporan stok menunjukkan 100kg tapi di detail bahan baku menunjukkan 150kg
 * 
 * Penyebab:
 * 1. Detail bahan baku mengambil stok dari field 'stok' di tabel bahan_bakus
 * 2. Laporan stok menghitung dari stock_movements (real-time calculation)
 * 3. Ada inkonsistensi antara master data dan movement data
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

echo "=== ANALISIS MASALAH STOK BAHAN BAKU ===\n\n";

// Ambil data Ayam Potong (contoh kasus)
$ayamPotong = BahanBaku::find(1); // ID 1 untuk Ayam Potong

if (!$ayamPotong) {
    echo "❌ Bahan baku tidak ditemukan!\n";
    exit;
}

echo "📦 BAHAN BAKU: {$ayamPotong->nama_bahan}\n";
echo "🏷️  ID: {$ayamPotong->id}\n\n";

// 1. Cek stok di master data (yang ditampilkan di detail bahan baku)
$stokMaster = $ayamPotong->stok;
echo "📊 STOK DI MASTER DATA (Detail Bahan Baku): {$stokMaster} kg\n";

// 2. Hitung stok dari stock movements (yang digunakan laporan stok)
$stockIn = StockMovement::where('item_type', 'material')
    ->where('item_id', $ayamPotong->id)
    ->where('direction', 'in')
    ->sum('qty');

$stockOut = StockMovement::where('item_type', 'material')
    ->where('item_id', $ayamPotong->id)
    ->where('direction', 'out')
    ->sum('qty');

$stokRealTime = $stockIn - $stockOut;
echo "📊 STOK DARI MOVEMENTS (Laporan Stok): {$stokRealTime} kg\n\n";

// 3. Analisis perbedaan
$selisih = $stokMaster - $stokRealTime;
echo "⚠️  SELISIH: {$selisih} kg\n";

if (abs($selisih) > 0.01) {
    echo "❌ DITEMUKAN INKONSISTENSI!\n\n";
    
    // 4. Tampilkan detail movements
    echo "=== DETAIL STOCK MOVEMENTS ===\n";
    $movements = StockMovement::where('item_type', 'material')
        ->where('item_id', $ayamPotong->id)
        ->orderBy('tanggal', 'asc')
        ->get();
    
    $runningStock = 0;
    foreach ($movements as $movement) {
        $sign = $movement->direction === 'in' ? '+' : '-';
        $runningStock += $movement->direction === 'in' ? $movement->qty : -$movement->qty;
        
        echo "{$movement->tanggal} | {$movement->ref_type} | {$sign}{$movement->qty} kg | Running: {$runningStock} kg\n";
    }
    
    echo "\n=== SOLUSI ===\n";
    echo "1. Sinkronisasi stok master dengan stock movements\n";
    echo "2. Update field 'stok' di tabel bahan_bakus\n";
    echo "3. Pastikan semua transaksi tercatat di stock_movements\n\n";
    
    // 5. Perbaikan otomatis
    $confirm = readline("Apakah Anda ingin memperbaiki stok master? (y/n): ");
    
    if (strtolower($confirm) === 'y') {
        try {
            DB::beginTransaction();
            
            // Update stok master sesuai dengan stock movements
            $ayamPotong->stok = $stokRealTime;
            $ayamPotong->save();
            
            DB::commit();
            
            echo "✅ BERHASIL! Stok master telah diupdate dari {$stokMaster} kg menjadi {$stokRealTime} kg\n";
            echo "🔄 Sekarang detail bahan baku dan laporan stok akan menunjukkan nilai yang sama\n\n";
            
        } catch (Exception $e) {
            DB::rollback();
            echo "❌ GAGAL: " . $e->getMessage() . "\n";
        }
    }
    
} else {
    echo "✅ STOK SUDAH KONSISTEN!\n";
}

echo "\n=== REKOMENDASI UNTUK MENCEGAH MASALAH DI MASA DEPAN ===\n";
echo "1. Gunakan method updateStok() di model BahanBaku untuk semua perubahan stok\n";
echo "2. Pastikan setiap transaksi (pembelian, produksi, dll) mencatat stock movement\n";
echo "3. Buat job scheduler untuk sinkronisasi berkala\n";
echo "4. Implementasikan validation di controller untuk memastikan konsistensi\n\n";

// 6. Buat function helper untuk sinkronisasi semua bahan baku
echo "=== SINKRONISASI SEMUA BAHAN BAKU ===\n";
$syncAll = readline("Apakah Anda ingin sinkronisasi semua bahan baku? (y/n): ");

if (strtolower($syncAll) === 'y') {
    $bahanBakus = BahanBaku::all();
    $fixed = 0;
    
    foreach ($bahanBakus as $bahan) {
        $stockIn = StockMovement::where('item_type', 'material')
            ->where('item_id', $bahan->id)
            ->where('direction', 'in')
            ->sum('qty');
        
        $stockOut = StockMovement::where('item_type', 'material')
            ->where('item_id', $bahan->id)
            ->where('direction', 'out')
            ->sum('qty');
        
        $stokRealTime = $stockIn - $stockOut;
        
        if (abs($bahan->stok - $stokRealTime) > 0.01) {
            $oldStock = $bahan->stok;
            $bahan->stok = $stokRealTime;
            $bahan->save();
            
            echo "🔧 {$bahan->nama_bahan}: {$oldStock} → {$stokRealTime}\n";
            $fixed++;
        }
    }
    
    echo "\n✅ SELESAI! {$fixed} bahan baku telah disinkronisasi\n";
}

echo "\n🎯 KESIMPULAN:\n";
echo "Masalah perbedaan stok antara detail bahan baku dan laporan stok\n";
echo "disebabkan oleh inkonsistensi antara field 'stok' di master data\n";
echo "dengan perhitungan real-time dari stock_movements.\n\n";
echo "Solusi yang telah diterapkan akan memastikan kedua tampilan\n";
echo "menunjukkan nilai yang sama dan akurat.\n";

?>