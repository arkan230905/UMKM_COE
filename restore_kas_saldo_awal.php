<?php
/**
 * Kembalikan saldo awal Kas dan Kas Bank seperti semula
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coa;
use App\Services\TrialBalanceService;

echo "=== KEMBALIKAN SALDO AWAL KAS DAN KAS BANK ===\n\n";

try {
    // 1. KEMBALIKAN SALDO AWAL KAS BANK (111)
    $kasBank = Coa::where('kode_akun', '111')->first();
    if ($kasBank) {
        $oldSaldo = $kasBank->saldo_awal;
        $kasBank->saldo_awal = 50000000; // Rp 50,000,000 (sesuai kondisi awal)
        $kasBank->save();
        
        echo "✅ Kas Bank (111) - {$kasBank->nama_akun}\n";
        echo "   Dari: Rp " . number_format($oldSaldo, 0, ',', '.') . "\n";
        echo "   Ke: Rp " . number_format($kasBank->saldo_awal, 0, ',', '.') . "\n\n";
    } else {
        echo "❌ Kas Bank (111) tidak ditemukan\n";
    }

    // 2. KEMBALIKAN SALDO AWAL KAS (112)
    $kas = Coa::where('kode_akun', '112')->first();
    if ($kas) {
        $oldSaldo = $kas->saldo_awal;
        $kas->saldo_awal = 25000000; // Rp 25,000,000 (sesuai kondisi awal)
        $kas->save();
        
        echo "✅ Kas (112) - {$kas->nama_akun}\n";
        echo "   Dari: Rp " . number_format($oldSaldo, 0, ',', '.') . "\n";
        echo "   Ke: Rp " . number_format($kas->saldo_awal, 0, ',', '.') . "\n\n";
    } else {
        echo "❌ Kas (112) tidak ditemukan\n";
    }

    echo "✅ Saldo awal Kas dan Kas Bank berhasil dikembalikan\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. CEK HASIL DI NERACA SALDO
echo "=== CEK HASIL DI NERACA SALDO ===\n";

$trialBalanceService = new TrialBalanceService();
$result = $trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

// Tampilkan hanya Kas dan Kas Bank
$kasAccounts = ['111', '112'];
foreach ($kasAccounts as $kodeAkun) {
    $account = collect($result['accounts'])->firstWhere('kode_akun', $kodeAkun);
    if ($account) {
        echo "Akun {$kodeAkun}: {$account['nama_akun']}\n";
        echo "  Saldo Awal: Rp " . number_format($account['saldo_awal'], 0, ',', '.') . "\n";
        echo "  Saldo Akhir: Rp " . number_format($account['saldo_akhir'], 0, ',', '.') . "\n";
        echo "  Display -> Debit: Rp " . number_format($account['debit'], 0, ',', '.') . 
             " | Kredit: Rp " . number_format($account['kredit'], 0, ',', '.') . "\n\n";
    }
}

echo "=== TOTAL NERACA SALDO ===\n";
echo "Total Debit: Rp " . number_format($result['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($result['total_kredit'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($result['difference'], 0, ',', '.') . "\n";
echo "Status: " . ($result['is_balanced'] ? '✅ SEIMBANG' : '❌ TIDAK SEIMBANG') . "\n\n";

echo "=== SELESAI ===\n";
echo "✅ Hanya saldo awal Kas dan Kas Bank yang diubah\n";
echo "✅ Akun lain tidak berubah\n";
echo "✅ Sekarang Kas dan Kas Bank sudah ada saldo awalnya\n";
echo "✅ Refresh halaman Buku Besar untuk melihat perubahan\n";