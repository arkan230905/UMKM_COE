<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Debug Balance Sheet Imbalance ===" . PHP_EOL;

// Check what's missing from the balance sheet
echo PHP_EOL . "=== Balance Sheet Analysis ===" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

echo "Period: April 2026" . PHP_EOL;
echo "Expected Balance: Aset = Kewajiban + Ekuitas" . PHP_EOL;
echo "Current: Aset 264.316.987 vs Kewajiban+Ekuitas 163.258.687" . PHP_EOL;
echo "Difference: 101.058.300" . PHP_EOL;

// Check all account types and their balances
echo PHP_EOL . "=== All Account Types and Balances ===" . PHP_EOL;

$allCoas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun')
    ->orderBy('kode_akun')
    ->get();

// Group by account type
$accountTypes = [];
foreach ($allCoas as $coa) {
    $accountTypes[$coa->tipe_akun][] = $coa;
}

foreach ($accountTypes as $type => $accounts) {
    echo $type . " (" . count($accounts) . " accounts):" . PHP_EOL;
    
    foreach ($accounts as $coa) {
        // Get balance for this account
        $totalDebit = DB::table('journal_entries')
            ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
            ->where('journal_entries.tanggal', '>=', $from)
            ->where('journal_entries.tanggal', '<=', $to)
            ->where('coas.kode_akun', $coa->kode_akun)
            ->sum('journal_lines.debit');
            
        $totalKredit = DB::table('journal_entries')
            ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
            ->where('journal_entries.tanggal', '>=', $from)
            ->where('journal_entries.tanggal', '<=', $to)
            ->where('coas.kode_akun', $coa->kode_akun)
            ->sum('journal_lines.credit');
        
        // Get saldo awal
        $saldoAwal = 0;
        if (in_array($coa->tipe_akun, ['Aset'])) {
            // Check if this is an inventory account
            $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
            $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
            
            if (in_array($coa->kode_akun, $bahanBakuCoas) || in_array($coa->kode_akun, $bahanPendukungCoas)) {
                // Use getInventorySaldoAwal logic
                if (in_array($coa->kode_akun, ['1101', '114'])) {
                    $saldoAwal = 0; // Parent accounts
                } elseif (in_array($coa->kode_akun, ['1141', '1142', '1143'])) {
                    $saldoAwal = DB::table('bahan_bakus')
                        ->where('coa_persediaan_id', $coa->kode_akun)
                        ->where('saldo_awal', '>', 0)
                        ->sum(DB::raw('saldo_awal * harga_satuan'));
                } elseif (in_array($coa->kode_akun, ['1152', '1153', '1154', '1155', '1156'])) {
                    $saldoAwal = DB::table('bahan_pendukungs')
                        ->where('coa_persediaan_id', $coa->kode_akun)
                        ->where('saldo_awal', '>', 0)
                        ->sum(DB::raw('saldo_awal * harga_satuan'));
                }
            } else {
                $saldoAwal = (float)($coa->saldo_awal ?? 0);
            }
        } else {
            $saldoAwal = (float)($coa->saldo_awal ?? 0);
        }
        
        // Calculate final balance
        $isDebitNormal = in_array(strtolower($coa->tipe_akun), ['asset', 'aset', 'expense', 'beban', 'biaya']);
        if ($isDebitNormal) {
            $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
        } else {
            $saldoAkhir = $saldoAwal + $totalKredit - $totalDebit;
        }
        
        if ($saldoAkhir != 0) {
            echo "  " . $coa->kode_akun . ": " . $coa->nama_akun . " = Rp " . number_format($saldoAkhir, 0) . PHP_EOL;
        }
    }
    echo PHP_EOL;
}

// Check specifically for liability accounts
echo PHP_EOL . "=== Liability Accounts Check ===" . PHP_EOL;

$liabilityAccounts = DB::table('coas')
    ->where('tipe_akun', 'Kewajiban')
    ->orderBy('kode_akun')
    ->get();

echo "Liability Accounts Found: " . $liabilityAccounts->count() . PHP_EOL;
foreach ($liabilityAccounts as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . PHP_EOL;
}

// Check if there are any liability accounts with balances
echo PHP_EOL . "=== Liability Balances ===" . PHP_EOL;

$totalLiabilities = 0;
foreach ($liabilityAccounts as $coa) {
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.debit');
        
    $totalKredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.credit');
    
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    $saldoAkhir = $saldoAwal + $totalKredit - $totalDebit; // Liability accounts are credit normal
    
    if ($saldoAkhir != 0) {
        echo $coa->kode_akun . ": " . $coa->nama_akun . " = Rp " . number_format($saldoAkhir, 0) . PHP_EOL;
        $totalLiabilities += $saldoAkhir;
    }
}

echo "Total Liabilities: Rp " . number_format($totalLiabilities, 0) . PHP_EOL;
