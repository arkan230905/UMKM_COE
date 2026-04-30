<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ANALISIS SELISIH YANG TERSISA ===\n\n";

// Hitung total saldo awal
$coas = \App\Models\Coa::all();
$totalDebit = 0;
$totalKredit = 0;

$debitAccounts = [];
$kreditAccounts = [];

foreach ($coas as $coa) {
    $saldoAwal = (float) $coa->saldo_awal;
    
    if ($saldoAwal != 0) {
        $isDebitNormal = in_array($coa->tipe_akun, ['Asset', 'Expense']);
        
        if ($isDebitNormal) {
            if ($saldoAwal > 0) {
                $totalDebit += $saldoAwal;
                $debitAccounts[] = [
                    'kode' => $coa->kode_akun,
                    'nama' => $coa->nama_akun,
                    'tipe' => $coa->tipe_akun,
                    'saldo' => $saldoAwal
                ];
            }
        } else {
            if ($saldoAwal > 0) {
                $totalKredit += $saldoAwal;
                $kreditAccounts[] = [
                    'kode' => $coa->kode_akun,
                    'nama' => $coa->nama_akun,
                    'tipe' => $coa->tipe_akun,
                    'saldo' => $saldoAwal
                ];
            }
        }
    }
}

echo "SALDO AWAL SAAT INI:\n\n";

echo "DEBIT (Aset & Beban):\n";
foreach ($debitAccounts as $acc) {
    echo "  {$acc['kode']} - {$acc['nama']}: Rp " . number_format($acc['saldo'], 0, ',', '.') . "\n";
}
echo "  TOTAL DEBIT: Rp " . number_format($totalDebit, 0, ',', '.') . "\n\n";

echo "KREDIT (Kewajiban & Modal):\n";
foreach ($kreditAccounts as $acc) {
    echo "  {$acc['kode']} - {$acc['nama']}: Rp " . number_format($acc['saldo'], 0, ',', '.') . "\n";
}
echo "  TOTAL KREDIT: Rp " . number_format($totalKredit, 0, ',', '.') . "\n\n";

$selisih = $totalKredit - $totalDebit;
echo "SELISIH: Rp " . number_format($selisih, 0, ',', '.') . " (Kredit lebih besar)\n\n";

echo str_repeat('=', 60) . "\n\n";

// Cek apakah ada persediaan barang jadi
echo "CEK PERSEDIAAN BARANG JADI:\n\n";

$barangJadis = \DB::table('barang_jadis')->get();

if ($barangJadis->count() > 0) {
    $totalBarangJadi = 0;
    foreach ($barangJadis as $bj) {
        $stok = $bj->stok ?? 0;
        $harga = $bj->harga_jual ?? 0;
        $nilai = $stok * $harga;
        $totalBarangJadi += $nilai;
        echo "  - {$bj->nama_produk}: {$stok} × Rp " . number_format($harga, 0, ',', '.') . " = Rp " . number_format($nilai, 0, ',', '.') . "\n";
    }
    echo "  TOTAL: Rp " . number_format($totalBarangJadi, 0, ',', '.') . "\n\n";
    
    if ($totalBarangJadi > 0) {
        echo "  ⚠️  Ada persediaan barang jadi yang belum dicatat!\n\n";
    }
} else {
    echo "  Tidak ada data barang jadi\n\n";
}

// Cek apakah ada piutang
echo "CEK PIUTANG:\n\n";
$piutangs = \DB::table('piutangs')->where('status', '!=', 'lunas')->get();

if ($piutangs->count() > 0) {
    $totalPiutang = 0;
    foreach ($piutangs as $p) {
        $sisa = $p->sisa_piutang ?? $p->total_piutang ?? 0;
        $totalPiutang += $sisa;
        echo "  - Piutang #{$p->id}: Rp " . number_format($sisa, 0, ',', '.') . "\n";
    }
    echo "  TOTAL: Rp " . number_format($totalPiutang, 0, ',', '.') . "\n\n";
    
    if ($totalPiutang > 0) {
        echo "  ⚠️  Ada piutang yang belum dicatat di saldo awal!\n\n";
    }
} else {
    echo "  Tidak ada piutang outstanding\n\n";
}

// Cek apakah ada hutang
echo "CEK HUTANG:\n\n";
$hutangs = \DB::table('hutangs')->where('status', '!=', 'lunas')->get();

if ($hutangs->count() > 0) {
    $totalHutang = 0;
    foreach ($hutangs as $h) {
        $sisa = $h->sisa_hutang ?? $h->total_hutang ?? 0;
        $totalHutang += $sisa;
        echo "  - Hutang #{$h->id}: Rp " . number_format($sisa, 0, ',', '.') . "\n";
    }
    echo "  TOTAL: Rp " . number_format($totalHutang, 0, ',', '.') . "\n\n";
    
    if ($totalHutang > 0) {
        echo "  ⚠️  Ada hutang yang belum dicatat di saldo awal!\n\n";
    }
} else {
    echo "  Tidak ada hutang outstanding\n\n";
}

echo str_repeat('=', 60) . "\n\n";

echo "KESIMPULAN:\n\n";

echo "Selisih saat ini: Rp " . number_format($selisih, 0, ',', '.') . "\n\n";

echo "Kemungkinan penyebab:\n";
echo "1. Modal dicatat terlalu besar (Rp 1.452.450.000)\n";
echo "2. Masih ada aset yang belum dicatat\n";
echo "3. Ada kewajiban/hutang yang seharusnya dicatat\n\n";

echo "REKOMENDASI:\n\n";

echo "Opsi 1: KURANGI MODAL\n";
echo "  Kurangi saldo awal Modal sebesar Rp " . number_format($selisih, 0, ',', '.') . "\n";
echo "  Dari: Rp 1.452.450.000\n";
echo "  Menjadi: Rp " . number_format($totalKredit - $selisih, 0, ',', '.') . "\n\n";

echo "Opsi 2: TAMBAH ASET atau TAMBAH HUTANG\n";
echo "  Jika ada aset lain yang belum dicatat, tambahkan\n";
echo "  Jika ada hutang, tambahkan di sisi kredit\n\n";

echo "Opsi 3: CEK ULANG DATA MODAL\n";
echo "  Pastikan modal awal yang dicatat sesuai dengan kondisi riil perusahaan\n";
