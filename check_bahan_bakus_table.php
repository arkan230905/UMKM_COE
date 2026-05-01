<?php
/**
 * Cek tabel bahan_bakus untuk ayam potong
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK TABEL BAHAN_BAKUS ===\n\n";

// 1. CEK SEMUA DATA BAHAN BAKUS
echo "=== SEMUA DATA BAHAN BAKUS ===\n";

$bahanBakus = DB::table('bahan_bakus')->get();

foreach ($bahanBakus as $bahan) {
    echo "ID: {$bahan->id}\n";
    echo "Nama: {$bahan->nama_bahan}\n";
    echo "COA Persediaan ID: {$bahan->coa_persediaan_id}\n";
    echo "Saldo Awal: {$bahan->saldo_awal}\n";
    echo "Harga Satuan: Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . "\n";
    echo "Total Nilai: Rp " . number_format($bahan->saldo_awal * $bahan->harga_satuan, 0, ',', '.') . "\n\n";
}

// 2. CEK KHUSUS UNTUK AYAM POTONG (1141)
echo "=== KHUSUS AYAM POTONG (1141) ===\n";

$ayamPotongBahan = DB::table('bahan_bakus')
    ->where('coa_persediaan_id', '1141')
    ->where('saldo_awal', '>', 0)
    ->get();

$totalSaldoAwalAyamPotong = 0;

foreach ($ayamPotongBahan as $bahan) {
    $nilaiSaldoAwal = $bahan->saldo_awal * $bahan->harga_satuan;
    $totalSaldoAwalAyamPotong += $nilaiSaldoAwal;
    
    echo "Bahan: {$bahan->nama_bahan}\n";
    echo "Qty Saldo Awal: {$bahan->saldo_awal}\n";
    echo "Harga Satuan: Rp " . number_format($bahan->harga_satuan, 0, ',', '.') . "\n";
    echo "Nilai Saldo Awal: Rp " . number_format($nilaiSaldoAwal, 0, ',', '.') . "\n\n";
}

echo "TOTAL SALDO AWAL AYAM POTONG: Rp " . number_format($totalSaldoAwalAyamPotong, 0, ',', '.') . "\n\n";

// 3. SIMULASI PERHITUNGAN BUKU BESAR DENGAN SALDO AWAL DARI BAHAN_BAKUS
echo "=== SIMULASI PERHITUNGAN BUKU BESAR ===\n";

// Ambil mutasi dari journal_lines
$mutasi = DB::table('journal_entries as je')
    ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
    ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
    ->where('coas.kode_akun', '1141')
    ->selectRaw('
        COALESCE(SUM(jl.debit), 0) as total_debit,
        COALESCE(SUM(jl.credit), 0) as total_kredit
    ')
    ->first();

$saldoAkhirDenganInventory = $totalSaldoAwalAyamPotong + $mutasi->total_debit - $mutasi->total_kredit;

echo "Saldo Awal (dari bahan_bakus): Rp " . number_format($totalSaldoAwalAyamPotong, 0, ',', '.') . "\n";
echo "Total Debit: Rp " . number_format($mutasi->total_debit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($mutasi->total_kredit, 0, ',', '.') . "\n";
echo "Saldo Akhir (Buku Besar): Rp " . number_format($saldoAkhirDenganInventory, 0, ',', '.') . "\n\n";

// 4. BANDINGKAN DENGAN YANG DITAMPILKAN DI UI
echo "=== PERBANDINGAN ===\n";
echo "Yang ditampilkan di UI Buku Besar: Rp 1.230.769\n";
echo "Perhitungan dengan saldo awal inventory: Rp " . number_format($saldoAkhirDenganInventory, 0, ',', '.') . "\n";
echo "Perhitungan tanpa saldo awal (TrialBalance): Rp " . number_format($mutasi->total_debit - $mutasi->total_kredit, 0, ',', '.') . "\n\n";

if (abs($saldoAkhirDenganInventory - 1230769) < 1) {
    echo "✅ Perhitungan dengan saldo awal inventory sesuai dengan UI\n";
} else {
    echo "❌ Masih ada perbedaan\n";
}

echo "\n=== KESIMPULAN ===\n";
echo "Perbedaan terjadi karena:\n";
echo "1. AkuntansiController (Buku Besar) menggunakan saldo awal dari tabel bahan_bakus\n";
echo "2. TrialBalanceService menggunakan saldo_awal dari COA (yang sudah direset ke 0)\n";
echo "3. Untuk konsistensi, TrialBalanceService perlu menggunakan logika yang sama\n";