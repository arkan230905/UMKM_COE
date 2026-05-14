<?php

/**
 * Test script untuk memverifikasi bahwa logika saldo awal COA sudah dinonaktifkan
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;
use App\Services\PersediaanSaldoAwalService;

echo "🧪 TEST LOGIKA SALDO AWAL COA SUDAH DINONAKTIFKAN\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // 1. Test dengan bahan baku yang sudah ada
    echo "1️⃣ Test dengan bahan baku yang sudah ada:\n";
    $bahanBaku = BahanBaku::first();
    
    if ($bahanBaku) {
        echo "   Bahan: {$bahanBaku->nama_bahan}\n";
        echo "   Exclude from COA: " . ($bahanBaku->exclude_from_coa ? 'YES' : 'NO') . "\n";
        echo "   COA recording disabled: " . ($bahanBaku->coa_recording_disabled ? 'YES' : 'NO') . "\n";
        
        // Test PersediaanSaldoAwalService
        echo "   Testing PersediaanSaldoAwalService...\n";
        $result = PersediaanSaldoAwalService::updateSaldoAwalItem($bahanBaku, 'bahan_baku');
        echo "   Result: " . ($result ? 'UPDATED (❌ MASIH AKTIF!)' : 'SKIPPED (✅ DINONAKTIFKAN)') . "\n\n";
    } else {
        echo "   Tidak ada bahan baku ditemukan\n\n";
    }
    
    // 2. Test dengan bahan pendukung yang sudah ada
    echo "2️⃣ Test dengan bahan pendukung yang sudah ada:\n";
    $bahanPendukung = BahanPendukung::first();
    
    if ($bahanPendukung) {
        echo "   Bahan: {$bahanPendukung->nama_bahan}\n";
        echo "   Exclude from COA: " . ($bahanPendukung->exclude_from_coa ? 'YES' : 'NO') . "\n";
        echo "   COA recording disabled: " . ($bahanPendukung->coa_recording_disabled ? 'YES' : 'NO') . "\n";
        
        // Test PersediaanSaldoAwalService
        echo "   Testing PersediaanSaldoAwalService...\n";
        $result = PersediaanSaldoAwalService::updateSaldoAwalItem($bahanPendukung, 'bahan_pendukung');
        echo "   Result: " . ($result ? 'UPDATED (❌ MASIH AKTIF!)' : 'SKIPPED (✅ DINONAKTIFKAN)') . "\n\n";
    } else {
        echo "   Tidak ada bahan pendukung ditemukan\n\n";
    }
    
    // 3. Test postSaldoAwalPersediaan
    echo "3️⃣ Test postSaldoAwalPersediaan method:\n";
    
    // Ambil saldo awal COA sebelum test
    $bahanBakuCoas = Coa::where('kode_akun', 'LIKE', '1104%')->get();
    $bahanPendukungCoas = Coa::where('kode_akun', 'LIKE', '113%')->get();
    
    $saldoSebelum = [
        'bahan_baku' => $bahanBakuCoas->sum('saldo_awal'),
        'bahan_pendukung' => $bahanPendukungCoas->sum('saldo_awal')
    ];
    
    echo "   Saldo awal COA sebelum test:\n";
    echo "   - Bahan Baku: Rp " . number_format($saldoSebelum['bahan_baku']) . "\n";
    echo "   - Bahan Pendukung: Rp " . number_format($saldoSebelum['bahan_pendukung']) . "\n";
    
    // Jalankan postSaldoAwalPersediaan
    echo "   Menjalankan postSaldoAwalPersediaan...\n";
    $result = PersediaanSaldoAwalService::postSaldoAwalPersediaan();
    
    echo "   Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    echo "   Message: {$result['message']}\n";
    
    // Cek saldo setelah test
    $bahanBakuCoas->each->refresh();
    $bahanPendukungCoas->each->refresh();
    
    $saldoSesudah = [
        'bahan_baku' => $bahanBakuCoas->sum('saldo_awal'),
        'bahan_pendukung' => $bahanPendukungCoas->sum('saldo_awal')
    ];
    
    echo "   Saldo awal COA setelah test:\n";
    echo "   - Bahan Baku: Rp " . number_format($saldoSesudah['bahan_baku']) . "\n";
    echo "   - Bahan Pendukung: Rp " . number_format($saldoSesudah['bahan_pendukung']) . "\n";
    
    // Verifikasi tidak ada perubahan
    $bahanBakuBerubah = $saldoSebelum['bahan_baku'] != $saldoSesudah['bahan_baku'];
    $bahanPendukungBerubah = $saldoSebelum['bahan_pendukung'] != $saldoSesudah['bahan_pendukung'];
    
    echo "   Verifikasi:\n";
    echo "   - Bahan Baku: " . ($bahanBakuBerubah ? '❌ BERUBAH (MASIH AKTIF!)' : '✅ TIDAK BERUBAH (DINONAKTIFKAN)') . "\n";
    echo "   - Bahan Pendukung: " . ($bahanPendukungBerubah ? '❌ BERUBAH (MASIH AKTIF!)' : '✅ TIDAK BERUBAH (DINONAKTIFKAN)') . "\n\n";
    
    // 4. Simulasi input bahan baru (tanpa benar-benar menyimpan)
    echo "4️⃣ Simulasi input bahan baru:\n";
    echo "   Jika logika dinonaktifkan dengan benar:\n";
    echo "   - Input bahan baku baru dengan stok 100 @ Rp 5.000 = Rp 500.000\n";
    echo "   - Saldo awal COA TIDAK akan bertambah Rp 500.000\n";
    echo "   - Log akan menunjukkan 'Skipping COA saldo awal update'\n\n";
    
    // 5. Kesimpulan
    echo "🎯 KESIMPULAN:\n";
    echo str_repeat("-", 40) . "\n";
    
    $allDisabled = true;
    
    if ($bahanBaku && (!$bahanBaku->exclude_from_coa || !$bahanBaku->coa_recording_disabled)) {
        echo "❌ Bahan Baku masih memiliki flag aktif\n";
        $allDisabled = false;
    }
    
    if ($bahanPendukung && (!$bahanPendukung->exclude_from_coa || !$bahanPendukung->coa_recording_disabled)) {
        echo "❌ Bahan Pendukung masih memiliki flag aktif\n";
        $allDisabled = false;
    }
    
    if ($bahanBakuBerubah || $bahanPendukungBerubah) {
        echo "❌ Saldo awal COA masih berubah saat postSaldoAwalPersediaan\n";
        $allDisabled = false;
    }
    
    if ($allDisabled) {
        echo "✅ SEMUA LOGIKA SALDO AWAL COA SUDAH DINONAKTIFKAN DENGAN BENAR!\n";
        echo "✅ Bahan baku dan bahan pendukung tidak akan mengupdate saldo awal COA\n";
        echo "✅ Sistem siap digunakan tanpa pencatatan nominal bahan ke COA\n";
    } else {
        echo "⚠️ MASIH ADA LOGIKA YANG BELUM DINONAKTIFKAN\n";
        echo "⚠️ Periksa kembali konfigurasi dan flag exclusion\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}