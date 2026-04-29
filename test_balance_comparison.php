<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Comparing Neraca Saldo vs Laporan Posisi Keuangan...\n\n";

// Simulate user login
\Illuminate\Support\Facades\Auth::loginUsingId(1);

$tanggalAwal = '2026-04-01';
$tanggalAkhir = '2026-04-30';

echo "Period: {$tanggalAwal} to {$tanggalAkhir}\n\n";

// Get neraca saldo data (working correctly)
echo "=== NERACA SALDO (WORKING CORRECTLY) ===\n";
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$neracaSaldoData = $trialBalanceService->calculateTrialBalance($tanggalAwal, $tanggalAkhir);

echo "Total Debit: " . number_format($neracaSaldoData['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit: " . number_format($neracaSaldoData['total_kredit'], 0, ',', '.') . "\n";
echo "Balance: " . ($neracaSaldoData['is_balanced'] ? "BALANCED" : "NOT BALANCED") . "\n\n";

echo "Account Details (Neraca Saldo):\n";
echo "=================================\n";

$neracaAset = 0;
$neracaKewajiban = 0;
$neracaEkuitas = 0;

foreach ($neracaSaldoData['accounts'] as $account) {
    echo "COA: {$account['kode_akun']} - {$account['nama_akun']}\n";
    echo "  Tipe: {$account['tipe_akun']}\n";
    echo "  Debit: " . number_format($account['debit'], 0, ',', '.') . "\n";
    echo "  Kredit: " . number_format($account['kredit'], 0, ',', '.') . "\n";
    
    // Calculate saldo for balance sheet
    $saldo = 0;
    if ($account['debit'] > 0) {
        $saldo = $account['debit'];
    } elseif ($account['kredit'] > 0) {
        $saldo = $account['kredit']; // Positive for balance sheet
    }
    
    $tipe = $account['tipe_akun'];
    
    if (in_array($tipe, ['Asset', 'Aset'])) {
        $neracaAset += $saldo;
        echo "  Saldo (Aset): " . number_format($saldo, 0, ',', '.') . "\n";
    } elseif (in_array($tipe, ['Liability', 'Kewajiban'])) {
        $neracaKewajiban += $saldo;
        echo "  Saldo (Kewajiban): " . number_format($saldo, 0, ',', '.') . "\n";
    } elseif (in_array($tipe, ['Equity', 'Modal'])) {
        $neracaEkuitas += $saldo;
        echo "  Saldo (Ekuitas): " . number_format($saldo, 0, ',', '.') . "\n";
    } else {
        echo "  Saldo (Other): " . number_format($saldo, 0, ',', '.') . "\n";
    }
    echo "\n";
}

echo "Neraca Saldo Summary:\n";
echo "====================\n";
echo "Total Aset: " . number_format($neracaAset, 0, ',', '.') . "\n";
echo "Total Kewajiban: " . number_format($neracaKewajiban, 0, ',', '.') . "\n";
echo "Total Ekuitas: " . number_format($neracaEkuitas, 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: " . number_format($neracaKewajiban + $neracaEkuitas, 0, ',', '.') . "\n";
echo "Selisih: " . number_format($neracaAset - ($neracaKewajiban + $neracaEkuitas), 0, ',', '.') . "\n";
echo "Status: " . (abs($neracaAset - ($neracaKewajiban + $neracaEkuitas)) < 0.01 ? "BALANCED" : "NOT BALANCED") . "\n\n";

// Get laporan posisi keuangan data (has issues)
echo "=== LAPORAN POSISI KEUANGAN (HAS ISSUES) ===\n";
$neracaService = app(\App\Services\NeracaService::class);
$laporanKeuanganData = $neracaService->generateLaporanPosisiKeuangan($tanggalAwal, $tanggalAkhir);

echo "Total Aset: " . number_format($laporanKeuanganData['aset']['total_aset'], 0, ',', '.') . "\n";
echo "Total Kewajiban: " . number_format($laporanKeuanganData['kewajiban']['total'], 0, ',', '.') . "\n";
echo "Total Ekuitas: " . number_format($laporanKeuanganData['ekuitas']['total'], 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: " . number_format($laporanKeuanganData['kewajiban']['total'] + $laporanKeuanganData['ekuitas']['total'], 0, ',', '.') . "\n";
echo "Selisih: " . number_format($laporanKeuanganData['selisih'], 0, ',', '.') . "\n";
echo "Status: " . ($laporanKeuanganData['is_balanced'] ? "BALANCED" : "NOT BALANCED") . "\n\n";

echo "Detailed Comparison:\n";
echo "===================\n";

echo "ASET:\n";
echo "Neraca Saldo: " . number_format($neracaAset, 0, ',', '.') . "\n";
echo "Laporan Keuangan: " . number_format($laporanKeuanganData['aset']['total_aset'], 0, ',', '.') . "\n";
echo "Selisih: " . number_format($neracaAset - $laporanKeuanganData['aset']['total_aset'], 0, ',', '.') . "\n\n";

echo "KEWAJIBAN:\n";
echo "Neraca Saldo: " . number_format($neracaKewajiban, 0, ',', '.') . "\n";
echo "Laporan Keuangan: " . number_format($laporanKeuanganData['kewajiban']['total'], 0, ',', '.') . "\n";
echo "Selisih: " . number_format($neracaKewajiban - $laporanKeuanganData['kewajiban']['total'], 0, ',', '.') . "\n\n";

echo "EKUITAS:\n";
echo "Neraca Saldo: " . number_format($neracaEkuitas, 0, ',', '.') . "\n";
echo "Laporan Keuangan: " . number_format($laporanKeuanganData['ekuitas']['total'], 0, ',', '.') . "\n";
echo "Selisih: " . number_format($neracaEkuitas - $laporanKeuanganData['ekuitas']['total'], 0, ',', '.') . "\n\n";

// Check specific accounts that might be causing issues
echo "Problematic Accounts Analysis:\n";
echo "============================\n";

foreach ($neracaSaldoData['accounts'] as $account) {
    $saldo = 0;
    if ($account['debit'] > 0) {
        $saldo = $account['debit'];
    } elseif ($account['kredit'] > 0) {
        $saldo = $account['kredit'];
    }
    
    // Check if this account is treated differently in NeracaService
    $tipe = $account['tipe_akun'];
    $kode = $account['kode_akun'];
    
    echo "COA {$kode} ({$account['nama_akun']}):\n";
    echo "  Tipe: {$tipe}\n";
    echo "  Neraca Saldo: " . number_format($saldo, 0, ',', '.') . "\n";
    
    // Check if this account appears in laporan keuangan
    $foundInLaporan = false;
    if (in_array($tipe, ['Asset', 'Aset'])) {
        foreach ($laporanKeuanganData['aset']['lancar'] as $aset) {
            if ($aset['kode_akun'] == $kode) {
                echo "  Laporan Keuangan: " . number_format($aset['saldo'], 0, ',', '.') . "\n";
                $foundInLaporan = true;
                break;
            }
        }
        if (!$foundInLaporan) {
            echo "  Laporan Keuangan: NOT FOUND in aset lancar\n";
        }
    }
    echo "\n";
}

echo "Balance comparison test completed!\n";
