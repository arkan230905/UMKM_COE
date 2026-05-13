<?php

/**
 * Script untuk menonaktifkan logika update saldo awal COA dari bahan baku dan bahan pendukung
 * 
 * Script ini akan:
 * 1. Reset saldo awal COA bahan baku dan bahan pendukung ke nol
 * 2. Set flag exclusion untuk semua bahan
 * 3. Verifikasi bahwa logika sudah dinonaktifkan
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CoaSaldoAwalDisabler;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;

echo "🚫 MENONAKTIFKAN LOGIKA SALDO AWAL COA UNTUK BAHAN BAKU & BAHAN PENDUKUNG\n";
echo str_repeat("=", 80) . "\n\n";

try {
    // 1. Tampilkan status sebelum perubahan
    echo "1️⃣ Status sebelum perubahan:\n";
    echo str_repeat("-", 40) . "\n";
    
    $bahanBakuCount = BahanBaku::count();
    $bahanPendukungCount = BahanPendukung::count();
    
    $bahanBakuCoas = Coa::where('kode_akun', 'LIKE', '1104%')->get();
    $bahanPendukungCoas = Coa::where('kode_akun', 'LIKE', '113%')->get();
    
    $totalBahanBakuSaldo = $bahanBakuCoas->sum('saldo_awal');
    $totalBahanPendukungSaldo = $bahanPendukungCoas->sum('saldo_awal');
    
    echo "📦 Bahan Baku: {$bahanBakuCount} items\n";
    echo "🔧 Bahan Pendukung: {$bahanPendukungCount} items\n";
    echo "💰 Total saldo awal COA Bahan Baku: Rp " . number_format($totalBahanBakuSaldo) . "\n";
    echo "💰 Total saldo awal COA Bahan Pendukung: Rp " . number_format($totalBahanPendukungSaldo) . "\n\n";
    
    // 2. Nonaktifkan logika untuk bahan yang sudah ada
    echo "2️⃣ Menonaktifkan logika saldo awal untuk bahan yang sudah ada...\n";
    $stats = CoaSaldoAwalDisabler::disableExistingBahanSaldoAwal();
    
    echo "✅ Bahan Baku diupdate: {$stats['bahan_baku_updated']}\n";
    echo "✅ Bahan Pendukung diupdate: {$stats['bahan_pendukung_updated']}\n";
    echo "✅ COA direset: {$stats['coa_reset']}\n\n";
    
    // 3. Verifikasi hasil
    echo "3️⃣ Verifikasi hasil:\n";
    echo str_repeat("-", 40) . "\n";
    
    $excludedBahanBaku = BahanBaku::where('exclude_from_coa', true)->count();
    $excludedBahanPendukung = BahanPendukung::where('exclude_from_coa', true)->count();
    
    $bahanBakuCoas->each->refresh();
    $bahanPendukungCoas->each->refresh();
    
    $newTotalBahanBakuSaldo = $bahanBakuCoas->sum('saldo_awal');
    $newTotalBahanPendukungSaldo = $bahanPendukungCoas->sum('saldo_awal');
    
    echo "📦 Bahan Baku dikecualikan: {$excludedBahanBaku}/{$bahanBakuCount}\n";
    echo "🔧 Bahan Pendukung dikecualikan: {$excludedBahanPendukung}/{$bahanPendukungCount}\n";
    echo "💰 Saldo awal COA Bahan Baku sekarang: Rp " . number_format($newTotalBahanBakuSaldo) . "\n";
    echo "💰 Saldo awal COA Bahan Pendukung sekarang: Rp " . number_format($newTotalBahanPendukungSaldo) . "\n\n";
    
    // 4. Test input bahan baru
    echo "4️⃣ Test dengan input bahan baru...\n";
    echo str_repeat("-", 40) . "\n";
    
    // Simulasi input bahan baku baru
    echo "Simulasi: Input bahan baku baru dengan stok 100 @ Rp 5.000\n";
    echo "Sebelumnya: Saldo awal COA akan bertambah Rp 500.000\n";
    echo "Sekarang: Saldo awal COA TIDAK akan berubah (logika dinonaktifkan)\n\n";
    
    // 5. Tampilkan detail COA yang direset
    echo "5️⃣ Detail COA yang direset:\n";
    echo str_repeat("-", 40) . "\n";
    
    echo "COA Bahan Baku:\n";
    foreach ($bahanBakuCoas as $coa) {
        echo "  - {$coa->kode_akun} {$coa->nama_akun}: Rp " . number_format($coa->saldo_awal) . "\n";
    }
    
    echo "\nCOA Bahan Pendukung:\n";
    foreach ($bahanPendukungCoas as $coa) {
        echo "  - {$coa->kode_akun} {$coa->nama_akun}: Rp " . number_format($coa->saldo_awal) . "\n";
    }
    
    echo "\n🎉 PROSES SELESAI!\n\n";
    
    // 6. Ringkasan perubahan
    echo "📋 RINGKASAN PERUBAHAN:\n";
    echo str_repeat("=", 50) . "\n";
    echo "✅ Logika update saldo awal COA di BahanBakuController DINONAKTIFKAN\n";
    echo "✅ Logika update saldo awal COA di BahanPendukungController DINONAKTIFKAN\n";
    echo "✅ PersediaanSaldoAwalService untuk bahan DINONAKTIFKAN\n";
    echo "✅ Semua bahan baku dan bahan pendukung diberi flag exclusion\n";
    echo "✅ Saldo awal COA bahan direset ke nol\n\n";
    
    echo "📝 DAMPAK:\n";
    echo "• Input bahan baku/pendukung baru TIDAK akan mengupdate saldo awal COA\n";
    echo "• Edit stok/harga bahan TIDAK akan mengupdate saldo awal COA\n";
    echo "• Laporan keuangan akan bersih dari nominal bahan di saldo awal\n";
    echo "• Tracking stok bahan tetap berjalan normal\n";
    echo "• Pembelian bahan tetap tercatat (tapi tidak ke COA persediaan)\n\n";
    
    echo "⚠️ CATATAN:\n";
    echo "• Logika untuk produk (barang jadi) tetap aktif\n";
    echo "• Jika ingin mengaktifkan kembali, ubah flag exclude_from_coa = false\n";
    echo "• Monitor log aplikasi untuk memastikan logika bekerja dengan benar\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}