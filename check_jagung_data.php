<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;
use App\Models\Coa;

echo "🔍 MEMERIKSA DATA BAHAN BAKU JAGUNG\n";
echo str_repeat("=", 50) . "\n\n";

$jagung = BahanBaku::where('nama_bahan', 'LIKE', '%jagung%')->first();

if ($jagung) {
    echo "📦 Data Bahan Baku:\n";
    echo "- Nama: {$jagung->nama_bahan}\n";
    echo "- Stok: {$jagung->stok}\n";
    echo "- Saldo Awal: {$jagung->saldo_awal}\n";
    echo "- Harga Satuan: Rp " . number_format($jagung->harga_satuan) . "\n";
    echo "- COA Persediaan ID: {$jagung->coa_persediaan_id}\n";
    echo "- Exclude from COA: " . ($jagung->exclude_from_coa ? 'YES' : 'NO') . "\n";
    echo "- COA Recording Disabled: " . ($jagung->coa_recording_disabled ? 'YES' : 'NO') . "\n\n";
    
    if ($jagung->coa_persediaan_id) {
        $coa = Coa::where('kode_akun', $jagung->coa_persediaan_id)->first();
        if ($coa) {
            echo "💰 Data COA Persediaan:\n";
            echo "- Kode: {$coa->kode_akun}\n";
            echo "- Nama: {$coa->nama_akun}\n";
            echo "- Saldo Awal: Rp " . number_format($coa->saldo_awal) . "\n";
            echo "- Tipe: {$coa->tipe_akun}\n\n";
            
            // Hitung nilai yang seharusnya (jika logika lama masih aktif)
            $nilaiSeharusnya = $jagung->stok * $jagung->harga_satuan;
            echo "🧮 Perhitungan:\n";
            echo "- Stok × Harga = {$jagung->stok} × " . number_format($jagung->harga_satuan) . " = Rp " . number_format($nilaiSeharusnya) . "\n";
            echo "- Saldo Awal COA saat ini: Rp " . number_format($coa->saldo_awal) . "\n";
            
            if ($coa->saldo_awal == $nilaiSeharusnya) {
                echo "❌ MASALAH: Saldo awal COA masih sesuai perhitungan bahan!\n";
                echo "❌ Logika update saldo awal COA masih aktif!\n\n";
                
                // Reset saldo awal COA
                echo "🔧 Mereset saldo awal COA ke nol...\n";
                $coa->saldo_awal = 0;
                $coa->save();
                echo "✅ Saldo awal COA direset ke nol\n";
                
            } elseif ($coa->saldo_awal == 0) {
                echo "✅ BAIK: Saldo awal COA sudah nol (logika berhasil dinonaktifkan)\n";
            } else {
                echo "⚠️ ANEH: Saldo awal COA tidak sesuai perhitungan dan tidak nol\n";
                echo "⚠️ Mungkin ada sisa dari input sebelumnya\n\n";
                
                // Reset saldo awal COA
                echo "🔧 Mereset saldo awal COA ke nol...\n";
                $coa->saldo_awal = 0;
                $coa->save();
                echo "✅ Saldo awal COA direset ke nol\n";
            }
        } else {
            echo "❌ COA Persediaan tidak ditemukan!\n";
        }
    } else {
        echo "⚠️ Bahan baku tidak memiliki COA Persediaan\n";
    }
} else {
    echo "❌ Bahan baku Jagung tidak ditemukan\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 KESIMPULAN:\n";
echo "Jika saldo awal COA masih muncul di interface setelah direset,\n";
echo "kemungkinan ada cache atau logika tampilan yang perlu dibersihkan.\n";