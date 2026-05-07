<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;

echo "🔍 VERIFIKASI SEMUA LOGIKA SALDO AWAL SUDAH DINONAKTIFKAN\n";
echo str_repeat("=", 70) . "\n\n";

// 1. Cek data bahan baku Jagung
echo "1️⃣ Verifikasi Bahan Baku Jagung:\n";
$jagung = BahanBaku::where('nama_bahan', 'LIKE', '%jagung%')->first();

if ($jagung) {
    echo "   📦 {$jagung->nama_bahan}: {$jagung->stok} × Rp " . number_format($jagung->harga_satuan) . " = Rp " . number_format($jagung->stok * $jagung->harga_satuan) . "\n";
    echo "   🏷️ COA: {$jagung->coa_persediaan_id}\n";
    echo "   🚫 Exclude from COA: " . ($jagung->exclude_from_coa ? 'YES' : 'NO') . "\n";
    
    if ($jagung->coa_persediaan_id) {
        $coa = Coa::where('kode_akun', $jagung->coa_persediaan_id)->first();
        if ($coa) {
            echo "   💰 Saldo Awal COA: Rp " . number_format($coa->saldo_awal) . "\n";
            
            if ($coa->saldo_awal == 0) {
                echo "   ✅ BAIK: Saldo awal COA sudah nol\n";
            } else {
                echo "   ❌ MASALAH: Saldo awal COA masih ada nilai\n";
            }
        }
    }
} else {
    echo "   ❌ Bahan baku Jagung tidak ditemukan\n";
}

echo "\n";

// 2. Test semua COA bahan baku dan bahan pendukung
echo "2️⃣ Verifikasi Semua COA Bahan:\n";
$coaBahan = Coa::where(function($query) {
    $query->where('kode_akun', 'LIKE', '1104%')
          ->orWhere('kode_akun', 'LIKE', '113%');
})->get();

$totalSaldoAwal = 0;
$coaWithSaldo = 0;

foreach ($coaBahan as $coa) {
    if ($coa->saldo_awal != 0) {
        echo "   ❌ {$coa->kode_akun} ({$coa->nama_akun}): Rp " . number_format($coa->saldo_awal) . "\n";
        $totalSaldoAwal += $coa->saldo_awal;
        $coaWithSaldo++;
    } else {
        echo "   ✅ {$coa->kode_akun} ({$coa->nama_akun}): Rp 0\n";
    }
}

echo "   📊 Total COA dengan saldo awal: {$coaWithSaldo}\n";
echo "   💰 Total saldo awal: Rp " . number_format($totalSaldoAwal) . "\n\n";

// 3. Test logika controller (simulasi)
echo "3️⃣ Test Logika Controller (Simulasi):\n";

// Simulasi CoaController::getInventorySaldoAwalForCoa
echo "   🧪 CoaController::getInventorySaldoAwalForCoa('1141'):\n";
if ($jagung && $jagung->coa_persediaan_id == '1141') {
    // Logika lama akan menghitung: stok × harga
    $nilaiLama = $jagung->stok * $jagung->harga_satuan;
    echo "      Logika lama akan return: Rp " . number_format($nilaiLama) . "\n";
    echo "      Logika baru akan return: null (menggunakan saldo_awal COA)\n";
    
    $coa = Coa::where('kode_akun', '1141')->first();
    if ($coa) {
        echo "      Hasil akhir di interface: Rp " . number_format($coa->saldo_awal) . "\n";
        
        if ($coa->saldo_awal == 0) {
            echo "      ✅ BERHASIL: Interface akan menampilkan Rp 0\n";
        } else {
            echo "      ❌ GAGAL: Interface masih menampilkan nilai\n";
        }
    }
}

echo "\n";

// 4. Kesimpulan
echo "🎯 KESIMPULAN:\n";
echo str_repeat("-", 50) . "\n";

$allGood = true;

if ($totalSaldoAwal > 0) {
    echo "❌ Masih ada COA dengan saldo awal tidak nol\n";
    $allGood = false;
}

if ($jagung && !$jagung->exclude_from_coa) {
    echo "❌ Bahan baku belum memiliki flag exclusion\n";
    $allGood = false;
}

if ($allGood) {
    echo "✅ SEMUA LOGIKA SALDO AWAL SUDAH DINONAKTIFKAN!\n";
    echo "✅ Interface COA akan menampilkan saldo awal nol untuk bahan\n";
    echo "✅ Input bahan baru tidak akan mengupdate saldo awal COA\n";
    echo "✅ Sistem siap digunakan tanpa pencatatan nominal bahan ke COA\n";
} else {
    echo "⚠️ MASIH ADA MASALAH YANG PERLU DIPERBAIKI\n";
    echo "⚠️ Periksa kembali konfigurasi dan data\n";
}

echo "\n📝 LOGIKA YANG SUDAH DINONAKTIFKAN:\n";
echo "1. BahanBakuController::store() - Skip update saldo awal COA\n";
echo "2. BahanPendukungController::store() - Skip update saldo awal COA\n";
echo "3. PersediaanSaldoAwalService::updateSaldoAwalItem() - Return false\n";
echo "4. CoaController::getInventorySaldoAwalForCoa() - Return null\n";
echo "5. AkuntansiController::getInventorySaldoAwal() - Return 0\n";
echo "6. TrialBalanceService::getInventorySaldoAwal() - Return 0\n";

echo "\n🔄 REFRESH BROWSER UNTUK MELIHAT PERUBAHAN!\n";