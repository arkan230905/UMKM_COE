<?php
/**
 * Debug AkuntansiController bukuBesar method
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUG BUKU BESAR CONTROLLER ===\n\n";

// Simulasi request seperti di UI
$accountCode = '1141';
$month = '04'; // April
$year = '2026';

echo "Filter yang digunakan:\n";
echo "Account Code: {$accountCode}\n";
echo "Month: {$month}\n";
echo "Year: {$year}\n\n";

// 1. CEK COA
$coa = \App\Models\Coa::where('kode_akun', $accountCode)->first();
echo "COA ditemukan: {$coa->nama_akun}\n";
echo "Saldo Awal COA: Rp " . number_format($coa->saldo_awal, 0, ',', '.') . "\n\n";

// 2. CEK SALDO AWAL INVENTORY
$bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
$bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];

$saldoAwal = 0;
if (in_array($accountCode, $bahanBakuCoas) || in_array($accountCode, $bahanPendukungCoas)) {
    // Gunakan logika getInventorySaldoAwal
    if (in_array($accountCode, $bahanBakuCoas)) {
        if (!in_array($accountCode, ['1101', '114'])) {
            $saldoAwal = DB::table('bahan_bakus')
                ->where('coa_persediaan_id', $accountCode)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        }
    }
} else {
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
}

echo "Saldo Awal (inventory): Rp " . number_format($saldoAwal, 0, ',', '.') . "\n\n";

// 3. CEK QUERY JOURNAL LINES
echo "=== QUERY JOURNAL LINES ===\n";

$query = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
    ->select([
        'je.*',
        'jl.id as line_id',
        'jl.debit',
        'jl.credit',
        'jl.memo as line_memo',
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun'
    ])
    ->where(function($q) {
        $q->where('jl.debit', '>', 0)
          ->orWhere('jl.credit', '>', 0);
    })
    ->where('coas.kode_akun', $accountCode)
    ->orderBy('je.tanggal','asc')
    ->orderBy('je.id','asc')
    ->orderBy('jl.id','asc');

// Filter bulan dan tahun jika ada
if ($month && $year) {
    $query->whereMonth('je.tanggal', $month)
           ->whereYear('je.tanggal', $year);
    echo "Filter diterapkan: Month = {$month}, Year = {$year}\n";
} else {
    echo "Tidak ada filter bulan/tahun\n";
}

$journalLines = $query->get();

echo "Jumlah journal lines ditemukan: " . $journalLines->count() . "\n\n";

foreach ($journalLines as $line) {
    echo "Jurnal ID: {$line->id} ({$line->tanggal})\n";
    echo "Memo: {$line->memo}\n";
    if ($line->debit > 0) {
        echo "DEBIT: Rp " . number_format($line->debit, 0, ',', '.') . "\n";
    }
    if ($line->credit > 0) {
        echo "KREDIT: Rp " . number_format($line->credit, 0, ',', '.') . "\n";
    }
    echo "\n";
}

// 4. HITUNG TOTAL
$totalDebit = $journalLines->sum('debit');
$totalKredit = $journalLines->sum('credit');
$saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;

echo "=== HASIL PERHITUNGAN ===\n";
echo "Saldo Awal: Rp " . number_format($saldoAwal, 0, ',', '.') . "\n";
echo "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Saldo Akhir: Rp " . number_format($saldoAkhir, 0, ',', '.') . "\n\n";

// 5. CEK TANPA FILTER BULAN/TAHUN
echo "=== CEK TANPA FILTER BULAN/TAHUN ===\n";

$queryAll = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
    ->where(function($q) {
        $q->where('jl.debit', '>', 0)
          ->orWhere('jl.credit', '>', 0);
    })
    ->where('coas.kode_akun', $accountCode)
    ->selectRaw('
        COALESCE(SUM(jl.debit), 0) as total_debit,
        COALESCE(SUM(jl.credit), 0) as total_kredit
    ')
    ->first();

$saldoAkhirAll = $saldoAwal + $queryAll->total_debit - $queryAll->total_kredit;

echo "Total Debit (semua): Rp " . number_format($queryAll->total_debit, 0, ',', '.') . "\n";
echo "Total Kredit (semua): Rp " . number_format($queryAll->total_kredit, 0, ',', '.') . "\n";
echo "Saldo Akhir (semua): Rp " . number_format($saldoAkhirAll, 0, ',', '.') . "\n\n";

echo "=== KESIMPULAN ===\n";
if ($totalDebit == 0 && $totalKredit == 0) {
    echo "❌ Tidak ada transaksi ditemukan dengan filter bulan/tahun\n";
    echo "Kemungkinan penyebab:\n";
    echo "1. Filter bulan/tahun terlalu ketat\n";
    echo "2. Transaksi ada di bulan/tahun lain\n";
    echo "3. UI menampilkan saldo awal saja karena tidak ada mutasi\n";
} else {
    echo "✅ Transaksi ditemukan dan saldo akhir dihitung dengan benar\n";
}

if (abs($saldoAkhir - 1230769) < 1) {
    echo "✅ Hasil sesuai dengan yang ditampilkan di UI\n";
} else {
    echo "❌ Hasil berbeda dengan yang ditampilkan di UI\n";
}