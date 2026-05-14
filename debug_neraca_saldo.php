<?php
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Set user context (assuming user 1)
Auth::loginUsingId(1);

$from = '2026-05-01';
$to = '2026-05-31';

echo "=== TOTAL JURNAL UMUM (Mei 2026) ===\n";
$result = DB::table('jurnal_umum')
    ->where('user_id', Auth::id())
    ->whereBetween('tanggal', [$from, $to])
    ->select(
        DB::raw('SUM(debit) as total_debit'),
        DB::raw('SUM(kredit) as total_kredit'),
        DB::raw('COUNT(*) as jumlah_baris')
    )
    ->first();

echo "Total Debit: Rp " . number_format($result->total_debit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($result->total_kredit, 0, ',', '.') . "\n";
echo "Jumlah Baris: " . $result->jumlah_baris . "\n";
echo "Selisih: Rp " . number_format(abs($result->total_debit - $result->total_kredit), 0, ',', '.') . "\n";

// Cek top 10 COA dengan kredit terbesar
echo "\n=== TOP 10 COA DENGAN KREDIT TERBESAR ===\n";
$topCredit = DB::table('jurnal_umum as ju')
    ->join('coas', 'coas.id', '=', 'ju.coa_id')
    ->where('ju.user_id', Auth::id())
    ->whereBetween('ju.tanggal', [$from, $to])
    ->select(
        'coas.kode_akun',
        'coas.nama_akun',
        DB::raw('SUM(ju.debit) as total_debit'),
        DB::raw('SUM(ju.kredit) as total_kredit'),
        DB::raw('COUNT(*) as jumlah_baris')
    )
    ->groupBy('coas.kode_akun', 'coas.nama_akun')
    ->orderBy('total_kredit', 'DESC')
    ->limit(10)
    ->get();

foreach ($topCredit as $row) {
    echo $row->kode_akun . " | " . substr($row->nama_akun, 0, 30) . " | Debit: " . number_format($row->total_debit, 0, ',', '.') . " | Kredit: " . number_format($row->total_kredit, 0, ',', '.') . " | Baris: " . $row->jumlah_baris . "\n";
}

// Cek apakah ada COA dengan saldo_awal yang sangat besar
echo "\n=== TOP 10 COA DENGAN SALDO_AWAL TERBESAR ===\n";
$topSaldoAwal = DB::table('coas')
    ->where('user_id', Auth::id())
    ->orderBy('saldo_awal', 'DESC')
    ->limit(10)
    ->select('kode_akun', 'nama_akun', 'saldo_awal')
    ->get();

foreach ($topSaldoAwal as $row) {
    echo $row->kode_akun . " | " . substr($row->nama_akun, 0, 30) . " | Saldo Awal: Rp " . number_format($row->saldo_awal, 0, ',', '.') . "\n";
}

echo "\n=== TOTAL SALDO_AWAL SEMUA COA ===\n";
$totalSaldoAwal = DB::table('coas')
    ->where('user_id', Auth::id())
    ->sum('saldo_awal');

echo "Total Saldo Awal: Rp " . number_format($totalSaldoAwal, 0, ',', '.') . "\n";
