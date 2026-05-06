<?php
/**
 * Reverse engineer untuk mencari tahu bagaimana mendapat saldo -277.435
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coa;
use App\Models\JournalLine;
use App\Models\JournalEntry;

echo "=== REVERSE ENGINEER SALDO -277.435 ===\n\n";

$kodeAkun = '1141';
$targetSaldo = -277435;

$coa = Coa::where('kode_akun', $kodeAkun)->first();
echo "Akun: {$coa->kode_akun} - {$coa->nama_akun}\n";
echo "Target saldo: Rp " . number_format($targetSaldo, 0, ',', '.') . "\n\n";

// Dari screenshot, kita tahu ada transaksi:
// 1. Konsumsi Material untuk Produksi: Kredit Rp 853.333
// 2. Pembelian #PR-20260425-0001: Debit Rp 2.400.000

echo "=== ANALISIS KEMUNGKINAN ===\n\n";

echo "1. JIKA SALDO AWAL = 0:\n";
$saldoAwal = 0;
$debit = 2400000;
$kredit = 853333;
$hasil1 = $saldoAwal + $debit - $kredit;
echo "   Saldo = 0 + 2.400.000 - 853.333 = Rp " . number_format($hasil1, 0, ',', '.') . "\n";
echo "   Status: " . ($hasil1 == $targetSaldo ? "✅ MATCH" : "❌ TIDAK MATCH") . "\n\n";

echo "2. JIKA SALDO AWAL = 1.230.769 (dari screenshot):\n";
$saldoAwal = 1230769;
$hasil2 = $saldoAwal + $debit - $kredit;
echo "   Saldo = 1.230.769 + 2.400.000 - 853.333 = Rp " . number_format($hasil2, 0, ',', '.') . "\n";
echo "   Status: " . ($hasil2 == $targetSaldo ? "✅ MATCH" : "❌ TIDAK MATCH") . "\n\n";

echo "3. JIKA URUTAN TRANSAKSI BERBEDA:\n";
echo "   Kredit dulu: 1.230.769 - 853.333 = Rp " . number_format(1230769 - 853333, 0, ',', '.') . "\n";
echo "   Lalu debit: 377.436 + 2.400.000 = Rp " . number_format(377436 + 2400000, 0, ',', '.') . "\n";
echo "   Status: ❌ TIDAK MATCH\n\n";

echo "4. JIKA ADA TRANSAKSI LAIN YANG TIDAK TERLIHAT:\n";
echo "   Untuk mendapat -277.435 dari 1.546.667:\n";
echo "   Perlu kredit tambahan: " . number_format(1546667 + 277435, 0, ',', '.') . "\n";
echo "   = Rp 1.824.102\n\n";

echo "5. JIKA SALDO AWAL BERBEDA:\n";
echo "   Untuk mendapat -277.435:\n";
echo "   Saldo awal yang dibutuhkan = -277.435 - 2.400.000 + 853.333\n";
$saldoAwalDibutuhkan = $targetSaldo - $debit + $kredit;
echo "   = Rp " . number_format($saldoAwalDibutuhkan, 0, ',', '.') . "\n\n";

echo "=== CEK SALDO AWAL DI DATABASE ===\n";
echo "Saldo awal di COA: Rp " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n\n";

echo "=== CEK SEMUA TRANSAKSI DENGAN BERBAGAI FILTER ===\n";

// Cek dengan filter bulan yang berbeda
$months = [
    'April 2026' => ['2026-04-01', '2026-04-30'],
    'Mei 2026' => ['2026-05-01', '2026-05-31'],
    'Sampai April' => ['1900-01-01', '2026-04-30'],
    'Sampai Mei' => ['1900-01-01', '2026-05-31']
];

foreach ($months as $label => $dates) {
    $mutasi = JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
        ->where('journal_lines.coa_id', $coa->id)
        ->whereBetween('journal_entries.tanggal', $dates)
        ->selectRaw('
            COALESCE(SUM(journal_lines.debit), 0) as total_debit,
            COALESCE(SUM(journal_lines.credit), 0) as total_kredit,
            COUNT(*) as jumlah_transaksi
        ')
        ->first();
    
    $saldoAkhir = ($coa->saldo_awal ?? 0) + $mutasi->total_debit - $mutasi->total_kredit;
    
    echo sprintf("%-15s: Debit=%s, Kredit=%s, Saldo=%s (%d trx)", 
        $label,
        number_format($mutasi->total_debit, 0, ',', '.'),
        number_format($mutasi->total_kredit, 0, ',', '.'),
        number_format($saldoAkhir, 0, ',', '.'),
        $mutasi->jumlah_transaksi
    );
    
    if (abs($saldoAkhir - $targetSaldo) < 0.01) {
        echo " ✅ MATCH!";
    }
    echo "\n";
}

echo "\n=== KESIMPULAN ===\n";
echo "Untuk mendapatkan saldo -Rp 277.435, kemungkinan:\n";
echo "1. Ada transaksi lain yang tidak terlihat di query\n";
echo "2. Saldo awal berbeda dengan yang di COA\n";
echo "3. Periode yang digunakan Buku Besar berbeda\n";
echo "4. Ada bug di halaman Buku Besar\n";