<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek Data Riil Laporan Kas & Bank ===\n";

// Simulate LaporanKasBankController logic
$startDate = now()->startOfMonth()->format('Y-m-d');
$endDate = now()->endOfMonth()->format('Y-m-d');

echo "Periode: $startDate s/d $endDate\n\n";

// Ambil HANYA akun Kas dan Bank menggunakan helper
$akunKasBank = \App\Helpers\AccountHelper::getKasBankAccounts();

// Hitung saldo untuk setiap akun
$dataKasBank = [];
$totalKeseluruhan = 0;

foreach ($akunKasBank as $akun) {
    $saldoAwal = 0;
    $transaksiMasuk = 0;
    $transaksiKeluar = 0;
    
    // Cari account_id yang sesuai
    $account = \DB::table('accounts')->where('code', $akun->kode_akun)->first();
    if (!$account) {
        echo "Account tidak ditemukan untuk {$akun->kode_akun}\n";
        continue;
    }
    
    // Get saldo awal dari CoaPeriodBalance
    $periode = \App\Models\CoaPeriod::where('periode', date('Y-m', strtotime($startDate)))->first();
    if ($periode) {
        $saldoPeriode = \DB::table('coa_period_balances')
            ->where('kode_akun', $akun->kode_akun)
            ->where('period_id', $periode->id)
            ->first();
            
        if ($saldoPeriode) {
            $saldoAwal = (float)$saldoPeriode->saldo_akhir;
        }
    }
    
    // Jika tidak ada periode, gunakan saldo awal dari COA
    if ($saldoAwal == 0) {
        $saldoAwal = (float)$akun->saldo_awal;
    }
    
    // Get transaksi masuk (Debit)
    if (\Schema::hasTable('journal_lines')) {
        $debit = \DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.account_id', $account->id)
            ->where('journal_entries.tanggal', '>=', $startDate)
            ->where('journal_entries.tanggal', '<=', $endDate)
            ->sum('debit');
            
        $transaksiMasuk = (float)$debit;
    }
    
    // Get transaksi keluar (Kredit)
    if (\Schema::hasTable('journal_lines')) {
        $kredit = \DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.account_id', $account->id)
            ->where('journal_entries.tanggal', '>=', $startDate)
            ->where('journal_entries.tanggal', '<=', $endDate)
            ->sum('credit');
            
        $transaksiKeluar = (float)$kredit;
    }
    
    // Untuk akun Kas & Bank (Aset), saldo normal adalah Debit
    // Saldo Akhir = Saldo Awal + Debit (Masuk) - Kredit (Keluar)
    $saldoAkhir = $saldoAwal + $transaksiMasuk - $transaksiKeluar;
    
    echo "=== {$akun->nama_akun} ({$akun->kode_akun}) ===\n";
    echo "Saldo Awal: Rp " . number_format($saldoAwal, 2) . "\n";
    echo "Transaksi Masuk: Rp " . number_format($transaksiMasuk, 2) . "\n";
    echo "Transaksi Keluar: Rp " . number_format($transaksiKeluar, 2) . "\n";
    echo "Saldo Akhir: Rp " . number_format($saldoAkhir, 2) . "\n\n";
    
    $totalKeseluruhan += $saldoAkhir;
}

echo "=== TOTAL LAPORAN KAS & BANK ===\n";
echo "Total Saldo Akhir: Rp " . number_format($totalKeseluruhan, 3) . "\n";

echo "\n=== BANDINGKAN DENGAN DASHBOARD ===\n";
echo "Dashboard menampilkan: Rp 100.000.000\n";
echo "Laporan Kas-Bank: Rp " . number_format($totalKeseluruhan, 3) . "\n";

if (abs($totalKeseluruhan - 100000000) < 100) {
    echo "✅ SUDAH SESUAI\n";
} else {
    echo "❌ BELUM SESUAI\n";
    echo "Selisih: Rp " . number_format(abs($totalKeseluruhan - 100000000), 3) . "\n";
}

echo "\n=== REKOMENDASI ===\n";
echo "1. Pastikan logic di DashboardController sama dengan LaporanKasBankController\n";
echo "2. Cek apakah ada perbedaan dalam perhitungan\n";
echo "3. Verifikasi data di CoaPeriodBalance dan journal_lines\n";
