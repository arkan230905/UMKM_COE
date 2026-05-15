<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coa;
use App\Models\JurnalUmum;
use Illuminate\Support\Facades\DB;

// Login as user
$userId = 1; // Ganti dengan user_id yang sesuai
auth()->loginUsingId($userId);

echo "=== DEBUG NERACA SALDO VS BUKU BESAR ===\n\n";

// Test untuk akun 210 - Utang Usaha
$kodeAkun = '210';
$bulan = 5; // Mei
$tahun = 2026;

$startDate = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$endDate = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

echo "Periode: $startDate s/d $endDate\n";
echo "Akun: $kodeAkun\n\n";

// Ambil COA
$coa = Coa::where('kode_akun', $kodeAkun)
    ->where('user_id', $userId)
    ->first();

if (!$coa) {
    echo "COA tidak ditemukan!\n";
    exit;
}

echo "=== DATA COA ===\n";
echo "ID: {$coa->id}\n";
echo "Kode Akun: {$coa->kode_akun}\n";
echo "Nama Akun: {$coa->nama_akun}\n";
echo "Tipe Akun: {$coa->tipe_akun}\n";
echo "Saldo Normal: {$coa->saldo_normal}\n";
echo "Saldo Awal (dari tabel coas): " . number_format($coa->saldo_awal, 0, ',', '.') . "\n\n";

// Cek apakah ini akun persediaan
$bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
$bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];

$isPersediaan = in_array($kodeAkun, $bahanBakuCoas) || in_array($kodeAkun, $bahanPendukungCoas);
echo "Apakah akun persediaan? " . ($isPersediaan ? "YA" : "TIDAK") . "\n\n";

// Ambil saldo awal (logika sama dengan Buku Besar)
if ($isPersediaan) {
    // Untuk akun persediaan, saldo awal = 0 (karena getInventorySaldoAwal di-disable)
    $saldoAwal = 0;
    echo "Saldo Awal (inventory disabled): Rp 0\n";
} else {
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    echo "Saldo Awal: Rp " . number_format($saldoAwal, 0, ',', '.') . "\n";
}

// Ambil transaksi periode dari jurnal_umum
echo "\n=== TRANSAKSI PERIODE (jurnal_umum) ===\n";
$journalLines = DB::table('jurnal_umum as ju')
    ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
    ->where('ju.user_id', $userId)
    ->where('coas.kode_akun', $kodeAkun)
    ->whereMonth('ju.tanggal', $bulan)
    ->whereYear('ju.tanggal', $tahun)
    ->select([
        'ju.id',
        'ju.tanggal',
        'ju.keterangan',
        'ju.debit',
        'ju.kredit',
        'coas.kode_akun',
        'coas.nama_akun'
    ])
    ->orderBy('ju.tanggal', 'asc')
    ->orderBy('ju.id', 'asc')
    ->get();

echo "Total transaksi: " . $journalLines->count() . "\n\n";

$runningBalance = $saldoAwal;
foreach ($journalLines as $line) {
    echo "Tanggal: {$line->tanggal}\n";
    echo "Keterangan: {$line->keterangan}\n";
    echo "Debit: Rp " . number_format($line->debit, 0, ',', '.') . "\n";
    echo "Kredit: Rp " . number_format($line->kredit, 0, ',', '.') . "\n";
    
    // Hitung running balance (formula Buku Besar)
    $runningBalance = $runningBalance + $line->debit - $line->kredit;
    echo "Saldo: Rp " . number_format($runningBalance, 0, ',', '.') . "\n";
    echo "---\n";
}

// Hitung total
$totalDebit = $journalLines->sum('debit');
$totalKredit = $journalLines->sum('kredit');

echo "\n=== PERHITUNGAN BUKU BESAR ===\n";
echo "Saldo Awal: Rp " . number_format($saldoAwal, 0, ',', '.') . "\n";
echo "Total Debit Periode: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit Periode: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Formula: Saldo Akhir = Saldo Awal + Total Debit - Total Kredit\n";
$saldoAkhirBukuBesar = $saldoAwal + $totalDebit - $totalKredit;
echo "Saldo Akhir Buku Besar: Rp " . number_format($saldoAkhirBukuBesar, 0, ',', '.') . "\n\n";

// Sekarang cek apa yang dihitung oleh TrialBalanceService
echo "=== PERHITUNGAN NERACA SALDO (TrialBalanceService) ===\n";

// Simulasi logika TrialBalanceService
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$neracaSaldoData = $trialBalanceService->calculateTrialBalance($startDate, $endDate);

// Cari akun 1153 di hasil neraca saldo
$accountData = collect($neracaSaldoData['accounts'])->firstWhere('kode_akun', $kodeAkun);

if ($accountData) {
    echo "Data ditemukan di Neraca Saldo:\n";
    echo "Kode Akun: {$accountData['kode_akun']}\n";
    echo "Nama Akun: {$accountData['nama_akun']}\n";
    echo "Saldo Awal: Rp " . number_format($accountData['saldo_awal'], 0, ',', '.') . "\n";
    echo "Mutasi Debit: Rp " . number_format($accountData['mutasi_debit'], 0, ',', '.') . "\n";
    echo "Mutasi Kredit: Rp " . number_format($accountData['mutasi_kredit'], 0, ',', '.') . "\n";
    echo "Saldo Akhir: Rp " . number_format($accountData['saldo_akhir'], 0, ',', '.') . "\n";
    echo "Tampil di Debit: Rp " . number_format($accountData['debit'], 0, ',', '.') . "\n";
    echo "Tampil di Kredit: Rp " . number_format($accountData['kredit'], 0, ',', '.') . "\n";
    echo "Source: {$accountData['source']}\n";
    echo "Is Debit Normal: " . ($accountData['is_debit_normal'] ? 'YA' : 'TIDAK') . "\n\n";
} else {
    echo "Akun $kodeAkun TIDAK DITEMUKAN di Neraca Saldo!\n\n";
}

// Bandingkan
echo "=== PERBANDINGAN ===\n";
echo "Saldo Akhir Buku Besar: Rp " . number_format($saldoAkhirBukuBesar, 0, ',', '.') . "\n";
if ($accountData) {
    echo "Saldo Akhir Neraca Saldo: Rp " . number_format($accountData['saldo_akhir'], 0, ',', '.') . "\n";
    
    if (abs($saldoAkhirBukuBesar - $accountData['saldo_akhir']) < 0.01) {
        echo "✅ SAMA - Perhitungan sudah konsisten!\n";
    } else {
        echo "❌ BEDA - Masih ada masalah!\n";
        echo "Selisih: Rp " . number_format(abs($saldoAkhirBukuBesar - $accountData['saldo_akhir']), 0, ',', '.') . "\n";
    }
} else {
    echo "Neraca Saldo: Akun tidak tampil\n";
    echo "❌ MASALAH - Akun tidak muncul di Neraca Saldo\n";
}

echo "\n=== SELESAI ===\n";
