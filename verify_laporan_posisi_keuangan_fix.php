<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Verify Laporan Posisi Keuangan Fix ===" . PHP_EOL;

// Test the fixed getLaporanPosisiKeuanganData logic
echo PHP_EOL . "Testing Fixed Logic for April 2026:" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

// Get COA data
$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->orderBy('kode_akun')
    ->get();

// Test specific asset accounts
$testAccounts = ['111', '112', '1141', '1142', '1143', '1152', '1153', '1154', '1155', '1156'];

foreach ($testAccounts as $kodeAkun) {
    echo PHP_EOL . "Testing COA " . $kodeAkun . ":" . PHP_EOL;
    
    $coa = $coas->where('kode_akun', $kodeAkun)->first();
    if (!$coa) {
        echo "  COA not found" . PHP_EOL;
        continue;
    }
    
    echo "  " . $coa->nama_akun . PHP_EOL;
    
    // Simulate the fixed logic
    $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
    $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
    
    if (in_array($coa->kode_akun, $bahanBakuCoas) || in_array($coa->kode_akun, $bahanPendukungCoas)) {
        // Simulate getInventorySaldoAwal logic
        $saldoAwal = 0;
        
        // Bahan Baku logic
        if (in_array($coa->kode_akun, ['1101', '114'])) {
            $saldoAwal = 0; // Parent accounts return 0
        } elseif (in_array($coa->kode_akun, ['1141', '1142', '1143'])) {
            $saldoAwal = DB::table('bahan_bakus')
                ->where('coa_persediaan_id', $coa->kode_akun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        }
        
        // Bahan Pendukung logic
        if (in_array($coa->kode_akun, ['115'])) {
            $saldoAwal = 0; // Parent accounts return 0
        } elseif (in_array($coa->kode_akun, ['1152', '1153', '1154', '1155', '1156'])) {
            $saldoAwal = DB::table('bahan_pendukungs')
                ->where('coa_persediaan_id', $coa->kode_akun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        }
        
        echo "  Using getInventorySaldoAwal: Rp " . number_format($saldoAwal, 0) . PHP_EOL;
    } else {
        $saldoAwal = (float)($coa->saldo_awal ?? 0);
        echo "  Using coa.saldo_awal: Rp " . number_format($saldoAwal, 0) . PHP_EOL;
    }
    
    // Get journal entries
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $kodeAkun)
        ->sum('journal_lines.debit');
        
    $totalKredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $kodeAkun)
        ->sum('journal_lines.credit');
    
    echo "  Journal Debit: Rp " . number_format($totalDebit, 0) . PHP_EOL;
    echo "  Journal Credit: Rp " . number_format($totalKredit, 0) . PHP_EOL;
    
    // Calculate final balance
    $isDebitNormal = in_array(strtolower($coa->tipe_akun), ['asset', 'aset', 'expense', 'beban', 'biaya']);
    if ($isDebitNormal) {
        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
    } else {
        $saldoAkhir = $saldoAwal + $totalKredit - $totalDebit;
    }
    
    echo "  Final Balance: Rp " . number_format($saldoAkhir, 0) . PHP_EOL;
}

echo PHP_EOL . "=== Expected Results Summary ===" . PHP_EOL;
echo "Asset Accounts should now show:" . PHP_EOL;
echo "- 111 Kas Bank: ~Rp 93.867.050" . PHP_EOL;
echo "- 112 Kas: ~Rp 72.398.100" . PHP_EOL;
echo "- 1141 Persediaan Ayam Potong: ~Rp 320.000" . PHP_EOL;
echo "- 1142 Persediaan Ayam Kampung: ~Rp -1.200.000" . PHP_EOL;
echo "- 1152 Persediaan Tepung Terigu: ~Rp 19.040.000" . PHP_EOL;
echo "- 1153 Persediaan Tepung Maizena: ~Rp 19.360.000" . PHP_EOL;
echo "- 1154 Persediaan Lada: ~Rp 5.040.000" . PHP_EOL;
echo "- 1155 Persediaan Bubuk Kaldu: ~Rp 15.936.000" . PHP_EOL;
echo "- 1156 Persediaan Bubuk Bawang Putih: ~Rp 1.900.800" . PHP_EOL;

echo PHP_EOL . "=== Fix Summary ===" . PHP_EOL;
echo "Changes made:" . PHP_EOL;
echo "1. Added inventory account detection logic" . PHP_EOL;
echo "2. Call getInventorySaldoAwal for inventory accounts" . PHP_EOL;
echo "3. Use coa.saldo_awal for non-inventory accounts" . PHP_EOL;
echo "4. Same logic as neracaSaldo method for consistency" . PHP_EOL;
