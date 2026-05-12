<?php
/**
 * Script untuk memperbaiki saldo negatif Pers. Barang Jadi
 * Menambahkan jurnal koreksi
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIX PERSEDIAAN BARANG JADI NEGATIF ===" . PHP_EOL . PHP_EOL;

$userId = 1;

// Cek akun 116
$akun116 = DB::table('coas')->where('kode_akun', '116')->where('user_id', $userId)->first();

if (!$akun116) {
    echo "❌ Akun 116 tidak ditemukan!" . PHP_EOL;
    exit;
}

echo "Akun 116: {$akun116->nama_akun}" . PHP_EOL;
echo "Saldo Awal: Rp " . number_format($akun116->saldo_awal, 0, ',', '.') . PHP_EOL;

// Cek mutasi Mei 2026
$mutasi = DB::table('jurnal_umum')
    ->where('coa_id', $akun116->id)
    ->where('user_id', $userId)
    ->whereBetween('tanggal', ['2026-05-01', '2026-05-31'])
    ->selectRaw('SUM(debit) as total_debit, SUM(kredit) as total_kredit')
    ->first();

$saldoAkhir = $akun116->saldo_awal + ($mutasi->total_debit ?? 0) - ($mutasi->total_kredit ?? 0);

echo "Total Debit: Rp " . number_format($mutasi->total_debit ?? 0, 0, ',', '.') . PHP_EOL;
echo "Total Kredit: Rp " . number_format($mutasi->total_kredit ?? 0, 0, ',', '.') . PHP_EOL;
echo "Saldo Akhir: Rp " . number_format($saldoAkhir, 0, ',', '.') . PHP_EOL;
echo PHP_EOL;

if ($saldoAkhir < 0) {
    echo "❌ SALDO NEGATIF TERDETEKSI!" . PHP_EOL;
    echo "Saldo negatif: Rp " . number_format($saldoAkhir, 0, ',', '.') . PHP_EOL;
    echo PHP_EOL;
    
    $nilaiKoreksi = abs($saldoAkhir);
    
    echo "OPSI PERBAIKAN:" . PHP_EOL;
    echo PHP_EOL;
    
    echo "Opsi 1: Tambahkan Jurnal Koreksi" . PHP_EOL;
    echo "----------------------------------------" . PHP_EOL;
    echo "Tanggal: 2026-05-01 (awal bulan)" . PHP_EOL;
    echo "Dr. Pers. Barang Jadi (116)     Rp " . number_format($nilaiKoreksi, 0, ',', '.') . PHP_EOL;
    echo "    Cr. Koreksi Persediaan          Rp " . number_format($nilaiKoreksi, 0, ',', '.') . PHP_EOL;
    echo "Keterangan: Koreksi saldo awal persediaan" . PHP_EOL;
    echo PHP_EOL;
    
    echo "Opsi 2: Update Saldo Awal" . PHP_EOL;
    echo "----------------------------------------" . PHP_EOL;
    echo "UPDATE coas SET saldo_awal = saldo_awal + {$nilaiKoreksi} WHERE kode_akun = '116' AND user_id = {$userId};" . PHP_EOL;
    echo "Saldo awal baru: Rp " . number_format($akun116->saldo_awal + $nilaiKoreksi, 0, ',', '.') . PHP_EOL;
    echo PHP_EOL;
    
    echo "Opsi 3: Cek Jurnal yang Salah" . PHP_EOL;
    echo "----------------------------------------" . PHP_EOL;
    echo "Lihat semua jurnal yang mempengaruhi akun 116:" . PHP_EOL;
    
    $jurnals = DB::table('jurnal_umum')
        ->where('coa_id', $akun116->id)
        ->where('user_id', $userId)
        ->whereBetween('tanggal', ['2026-05-01', '2026-05-31'])
        ->orderBy('tanggal')
        ->get();
    
    foreach ($jurnals as $jurnal) {
        echo sprintf(
            "  %s | %s | Dr: %s | Cr: %s | %s%s",
            $jurnal->tanggal,
            $jurnal->tipe_referensi,
            number_format($jurnal->debit, 0, ',', '.'),
            number_format($jurnal->kredit, 0, ',', '.'),
            $jurnal->keterangan,
            PHP_EOL
        );
    }
    
    echo PHP_EOL;
    echo "REKOMENDASI:" . PHP_EOL;
    echo "Cek apakah ada jurnal HPP (Harga Pokok Penjualan) yang mengkredit" . PHP_EOL;
    echo "Pers. Barang Jadi tanpa ada jurnal produksi selesai sebelumnya." . PHP_EOL;
    
} else {
    echo "✅ Saldo positif, tidak ada masalah!" . PHP_EOL;
}
