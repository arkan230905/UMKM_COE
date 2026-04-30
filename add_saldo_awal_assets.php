<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MENAMBAHKAN SALDO AWAL ASET ===\n\n";

// 1. Hitung total persediaan bahan baku
echo "1. Menghitung Persediaan Bahan Baku...\n";
$bahanBakus = \App\Models\BahanBaku::all();
$totalBahanBaku = 0;

foreach ($bahanBakus as $bb) {
    $nilai = $bb->stok * $bb->harga_satuan;
    $totalBahanBaku += $nilai;
    echo "   - {$bb->nama_bahan}: Rp " . number_format($nilai, 0, ',', '.') . "\n";
}
echo "   TOTAL: Rp " . number_format($totalBahanBaku, 0, ',', '.') . "\n\n";

// 2. Hitung total persediaan bahan pendukung
echo "2. Menghitung Persediaan Bahan Pendukung...\n";
$bahanPendukungs = \App\Models\BahanPendukung::all();
$totalBahanPendukung = 0;

foreach ($bahanPendukungs as $bp) {
    $nilai = $bp->stok * $bp->harga_satuan;
    $totalBahanPendukung += $nilai;
    echo "   - {$bp->nama_bahan}: Rp " . number_format($nilai, 0, ',', '.') . "\n";
}
echo "   TOTAL: Rp " . number_format($totalBahanPendukung, 0, ',', '.') . "\n\n";

// 3. Hitung total aset tetap per kategori
echo "3. Menghitung Aset Tetap...\n";
$asets = \App\Models\Aset::with('kategori.jenisAset')->get();

$asetPerKategori = [];
foreach ($asets as $aset) {
    $nilaiPerolehan = $aset->harga_perolehan + ($aset->biaya_perolehan ?? 0);
    $jenisNama = $aset->kategori && $aset->kategori->jenisAset 
        ? $aset->kategori->jenisAset->nama 
        : 'Lainnya';
    
    if (!isset($asetPerKategori[$jenisNama])) {
        $asetPerKategori[$jenisNama] = 0;
    }
    $asetPerKategori[$jenisNama] += $nilaiPerolehan;
    
    echo "   - {$aset->nama_aset} ({$jenisNama}): Rp " . number_format($nilaiPerolehan, 0, ',', '.') . "\n";
}

$totalAset = array_sum($asetPerKategori);
echo "\n   TOTAL PER KATEGORI:\n";
foreach ($asetPerKategori as $jenis => $nilai) {
    echo "   - {$jenis}: Rp " . number_format($nilai, 0, ',', '.') . "\n";
}
echo "   TOTAL: Rp " . number_format($totalAset, 0, ',', '.') . "\n\n";

echo str_repeat('=', 60) . "\n\n";

// 4. Cari COA yang sesuai
echo "4. Mencari COA yang sesuai...\n\n";

// COA Persediaan Bahan Baku
$coaBahanBaku = \App\Models\Coa::where('kode_akun', '1131')
    ->where('nama_akun', 'LIKE', '%Persediaan Bahan Baku%')
    ->first();

if (!$coaBahanBaku) {
    $coaBahanBaku = \App\Models\Coa::where('nama_akun', 'LIKE', '%Persediaan Bahan Baku%')->first();
}

if ($coaBahanBaku) {
    echo "   ✅ COA Persediaan Bahan Baku: {$coaBahanBaku->kode_akun} - {$coaBahanBaku->nama_akun} (ID: {$coaBahanBaku->id})\n";
} else {
    echo "   ❌ COA Persediaan Bahan Baku tidak ditemukan\n";
}

// COA Persediaan Bahan Pendukung
$coaBahanPendukung = \App\Models\Coa::where('nama_akun', 'LIKE', '%Persediaan Bahan Pendukung%')
    ->orWhere('nama_akun', 'LIKE', '%Persediaan Bahan Pembantu%')
    ->first();

if (!$coaBahanPendukung) {
    // Jika tidak ada, gunakan COA Persediaan umum
    $coaBahanPendukung = \App\Models\Coa::where('kode_akun', '113')
        ->where('nama_akun', 'Persediaan')
        ->first();
}

if ($coaBahanPendukung) {
    echo "   ✅ COA Persediaan Bahan Pendukung: {$coaBahanPendukung->kode_akun} - {$coaBahanPendukung->nama_akun} (ID: {$coaBahanPendukung->id})\n";
} else {
    echo "   ❌ COA Persediaan Bahan Pendukung tidak ditemukan\n";
}

// COA Aset Tetap per kategori
$coaAsetTetap = [];
foreach ($asetPerKategori as $jenis => $nilai) {
    $coa = \App\Models\Coa::where('nama_akun', 'LIKE', "%{$jenis}%")
        ->where('tipe_akun', 'Asset')
        ->whereNotNull('kode_akun')
        ->first();
    
    if ($coa) {
        $coaAsetTetap[$jenis] = $coa;
        echo "   ✅ COA {$jenis}: {$coa->kode_akun} - {$coa->nama_akun} (ID: {$coa->id})\n";
    } else {
        echo "   ⚠️  COA {$jenis} tidak ditemukan, akan dicari manual\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n\n";

// 5. Update saldo awal
echo "5. Update Saldo Awal COA...\n\n";

$updates = [];

// Update Persediaan Bahan Baku
if ($coaBahanBaku && $totalBahanBaku > 0) {
    $saldoLama = $coaBahanBaku->saldo_awal;
    $coaBahanBaku->saldo_awal = $totalBahanBaku;
    $coaBahanBaku->save();
    
    echo "   ✅ {$coaBahanBaku->kode_akun} - {$coaBahanBaku->nama_akun}\n";
    echo "      Saldo Lama: Rp " . number_format($saldoLama, 0, ',', '.') . "\n";
    echo "      Saldo Baru: Rp " . number_format($totalBahanBaku, 0, ',', '.') . "\n\n";
    
    $updates[] = [
        'coa' => $coaBahanBaku->kode_akun . ' - ' . $coaBahanBaku->nama_akun,
        'lama' => $saldoLama,
        'baru' => $totalBahanBaku
    ];
}

// Update Persediaan Bahan Pendukung
if ($coaBahanPendukung && $totalBahanPendukung > 0) {
    $saldoLama = $coaBahanPendukung->saldo_awal;
    $coaBahanPendukung->saldo_awal = $totalBahanPendukung;
    $coaBahanPendukung->save();
    
    echo "   ✅ {$coaBahanPendukung->kode_akun} - {$coaBahanPendukung->nama_akun}\n";
    echo "      Saldo Lama: Rp " . number_format($saldoLama, 0, ',', '.') . "\n";
    echo "      Saldo Baru: Rp " . number_format($totalBahanPendukung, 0, ',', '.') . "\n\n";
    
    $updates[] = [
        'coa' => $coaBahanPendukung->kode_akun . ' - ' . $coaBahanPendukung->nama_akun,
        'lama' => $saldoLama,
        'baru' => $totalBahanPendukung
    ];
}

// Update Aset Tetap per kategori
foreach ($coaAsetTetap as $jenis => $coa) {
    $nilai = $asetPerKategori[$jenis];
    $saldoLama = $coa->saldo_awal;
    $coa->saldo_awal = $nilai;
    $coa->save();
    
    echo "   ✅ {$coa->kode_akun} - {$coa->nama_akun}\n";
    echo "      Saldo Lama: Rp " . number_format($saldoLama, 0, ',', '.') . "\n";
    echo "      Saldo Baru: Rp " . number_format($nilai, 0, ',', '.') . "\n\n";
    
    $updates[] = [
        'coa' => $coa->kode_akun . ' - ' . $coa->nama_akun,
        'lama' => $saldoLama,
        'baru' => $nilai
    ];
}

echo str_repeat('=', 60) . "\n\n";

// 6. Cek balance setelah update
echo "6. Cek Balance Setelah Update...\n\n";

$coas = \App\Models\Coa::all();
$totalSaldoAwalDebit = 0;
$totalSaldoAwalKredit = 0;

foreach ($coas as $coa) {
    $saldoAwal = (float) $coa->saldo_awal;
    
    if ($saldoAwal != 0) {
        $isDebitNormal = in_array($coa->tipe_akun, ['Asset', 'Expense']);
        
        if ($isDebitNormal) {
            if ($saldoAwal > 0) {
                $totalSaldoAwalDebit += $saldoAwal;
            } else {
                $totalSaldoAwalKredit += abs($saldoAwal);
            }
        } else {
            if ($saldoAwal > 0) {
                $totalSaldoAwalKredit += $saldoAwal;
            } else {
                $totalSaldoAwalDebit += abs($saldoAwal);
            }
        }
    }
}

echo "Total Saldo Awal Debit: Rp " . number_format($totalSaldoAwalDebit, 0, ',', '.') . "\n";
echo "Total Saldo Awal Kredit: Rp " . number_format($totalSaldoAwalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($totalSaldoAwalDebit - $totalSaldoAwalKredit), 0, ',', '.') . "\n\n";

if ($totalSaldoAwalDebit == $totalSaldoAwalKredit) {
    echo "✅ SALDO AWAL SUDAH BALANCE!\n";
} else {
    echo "⚠️  SALDO AWAL MASIH BELUM BALANCE\n";
    $selisih = $totalSaldoAwalKredit - $totalSaldoAwalDebit;
    if ($selisih > 0) {
        echo "   Kredit lebih besar Rp " . number_format($selisih, 0, ',', '.') . "\n";
        echo "   Kemungkinan:\n";
        echo "   - Modal terlalu besar\n";
        echo "   - Masih ada aset lain yang belum dicatat (Persediaan Barang Jadi, dll)\n";
        echo "   - Ada kewajiban/hutang yang belum dicatat\n";
    } else {
        echo "   Debit lebih besar Rp " . number_format(abs($selisih), 0, ',', '.') . "\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n\n";

// 7. Ringkasan
echo "RINGKASAN UPDATE:\n\n";
foreach ($updates as $update) {
    echo "✅ {$update['coa']}\n";
    echo "   Rp " . number_format($update['lama'], 0, ',', '.') . " → Rp " . number_format($update['baru'], 0, ',', '.') . "\n\n";
}

echo "Total Aset Ditambahkan: Rp " . number_format($totalBahanBaku + $totalBahanPendukung + $totalAset, 0, ',', '.') . "\n";
echo "\nSilakan refresh halaman Neraca Saldo untuk melihat perubahan.\n";
