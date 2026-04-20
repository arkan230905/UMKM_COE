<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== CHECKING CURRENT ISSUES ===\n\n";

// Test 1: Check COA dropdown
echo "=== TEST 1: COA DROPDOWN ===\n";

$coas_all = \App\Models\Coa::all();
echo "Total COA records (all): " . $coas_all->count() . "\n";

$coas_grouped = \App\Models\Coa::select('kode_akun', 'nama_akun', 'tipe_akun')
    ->groupBy('kode_akun', 'nama_akun', 'tipe_akun')
    ->orderBy('kode_akun')
    ->get();
echo "Total COA records (grouped): " . $coas_grouped->count() . "\n";

echo "\nFirst 10 grouped COAs:\n";
foreach ($coas_grouped->take(10) as $coa) {
    echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
}

// Test 2: Check Financial Position Report balance
echo "\n=== TEST 2: FINANCIAL POSITION BALANCE ===\n";

$bulan = date('m');
$tahun = date('Y');
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

// Use exact same query as controller
$neracaSaldoData = DB::select("
    SELECT 
        coa_summary.kode_akun,
        coa_summary.nama_akun,
        coa_summary.tipe_akun,
        coa_summary.saldo_normal,
        coa_summary.saldo_awal,
        
        COALESCE(jl_data.total_debit, 0) as jl_total_debit,
        COALESCE(jl_data.total_kredit, 0) as jl_total_kredit,
        COALESCE(ju_data.total_debit, 0) as ju_total_debit,
        COALESCE(ju_data.total_kredit, 0) as ju_total_kredit
        
    FROM (
        SELECT 
            c.kode_akun,
            MIN(c.nama_akun) as nama_akun,
            MIN(c.tipe_akun) as tipe_akun,
            MIN(c.kategori_akun) as kategori_akun,
            MIN(c.saldo_normal) as saldo_normal,
            SUM(COALESCE(c.saldo_awal, 0)) as saldo_awal
        FROM coas c
        GROUP BY c.kode_akun
    ) coa_summary
    
    LEFT JOIN (
        SELECT 
            c2.kode_akun,
            SUM(jl.debit) as total_debit,
            SUM(jl.credit) as total_kredit
        FROM journal_lines jl
        JOIN journal_entries je ON jl.journal_entry_id = je.id
        JOIN coas c2 ON jl.coa_id = c2.id
        WHERE je.tanggal <= ?
        GROUP BY c2.kode_akun
    ) jl_data ON coa_summary.kode_akun = jl_data.kode_akun
    
    LEFT JOIN (
        SELECT 
            c2.kode_akun,
            SUM(ju.debit) as total_debit,
            SUM(ju.kredit) as total_kredit
        FROM jurnal_umum ju
        JOIN coas c2 ON ju.coa_id = c2.id
        WHERE ju.tanggal <= ?
        GROUP BY c2.kode_akun
    ) ju_data ON coa_summary.kode_akun = ju_data.kode_akun
    
    ORDER BY coa_summary.kode_akun
", [$to, $to]);

$total_assets = 0;
$total_liabilities = 0;
$total_equity = 0;

echo "Accounts with non-zero balances:\n";
foreach ($neracaSaldoData as $coa) {
    $saldoAwal = $coa->saldo_awal;
    $totalDebitSampaiPeriode = $coa->jl_total_debit + $coa->ju_total_debit;
    $totalKreditSampaiPeriode = $coa->jl_total_kredit + $coa->ju_total_kredit;
    
    $saldoNormal = strtolower($coa->saldo_normal ?? '');
    if (empty($saldoNormal)) {
        $isDebitNormal = in_array(strtolower($coa->tipe_akun), ['asset', 'aset', 'expense', 'beban', 'biaya']);
    } else {
        $isDebitNormal = ($saldoNormal === 'debit');
    }
    
    if ($isDebitNormal) {
        $saldoAkhir = $saldoAwal + $totalDebitSampaiPeriode - $totalKreditSampaiPeriode;
    } else {
        $saldoAkhir = $saldoAwal + $totalKreditSampaiPeriode - $totalDebitSampaiPeriode;
    }
    
    if (abs($saldoAkhir) > 0.01) {
        echo "  {$coa->kode_akun} ({$coa->nama_akun}): " . 
             number_format($saldoAkhir, 0, ',', '.') . " ({$coa->tipe_akun})\n";
        
        if ($coa->tipe_akun === 'Asset') {
            $total_assets += $saldoAkhir;
        } elseif ($coa->tipe_akun === 'Liability') {
            $total_liabilities += $saldoAkhir;
        } elseif ($coa->tipe_akun === 'Equity') {
            $total_equity += $saldoAkhir;
        }
    }
}

echo "\nBalance Summary:\n";
echo "Total Assets: " . number_format($total_assets, 0, ',', '.') . "\n";
echo "Total Liabilities: " . number_format($total_liabilities, 0, ',', '.') . "\n";
echo "Total Equity: " . number_format($total_equity, 0, ',', '.') . "\n";
echo "Balance (Assets - Liabilities - Equity): " . number_format($total_assets - $total_liabilities - $total_equity, 0, ',', '.') . "\n";

if (abs($total_assets - $total_liabilities - $total_equity) < 1) {
    echo "✅ BALANCE SHEET IS BALANCED!\n";
} else {
    echo "❌ BALANCE SHEET IS NOT BALANCED!\n";
}

// Check current Saldo Laba Ditahan
$saldo_laba = DB::select("SELECT saldo_awal FROM coas WHERE kode_akun = '312' LIMIT 1");
if (!empty($saldo_laba)) {
    echo "Current Saldo Laba Ditahan: " . number_format($saldo_laba[0]->saldo_awal, 0, ',', '.') . "\n";
}

echo "\n🔍 CURRENT ISSUES CHECK COMPLETED!\n";