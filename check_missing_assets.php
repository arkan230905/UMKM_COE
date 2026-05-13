<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK ASET YANG BELUM DICATAT DI SALDO AWAL ===\n\n";

// 1. Cek Persediaan Bahan Baku
echo "1. PERSEDIAAN BAHAN BAKU:\n";
$bahanBakus = \App\Models\BahanBaku::all();
$totalBahanBaku = 0;

foreach ($bahanBakus as $bb) {
    $nilai = $bb->stok * $bb->harga_satuan;
    $totalBahanBaku += $nilai;
    echo "   - {$bb->nama_bahan}: {$bb->stok} {$bb->satuan} × Rp " . number_format($bb->harga_satuan, 0, ',', '.') . " = Rp " . number_format($nilai, 0, ',', '.') . "\n";
}
echo "   TOTAL: Rp " . number_format($totalBahanBaku, 0, ',', '.') . "\n\n";

// 2. Cek Persediaan Bahan Pendukung
echo "2. PERSEDIAAN BAHAN PENDUKUNG:\n";
$bahanPendukungs = \App\Models\BahanPendukung::all();
$totalBahanPendukung = 0;

foreach ($bahanPendukungs as $bp) {
    $nilai = $bp->stok * $bp->harga_satuan;
    $totalBahanPendukung += $nilai;
    echo "   - {$bp->nama_bahan}: {$bp->stok} {$bp->satuan} × Rp " . number_format($bp->harga_satuan, 0, ',', '.') . " = Rp " . number_format($nilai, 0, ',', '.') . "\n";
}
echo "   TOTAL: Rp " . number_format($totalBahanPendukung, 0, ',', '.') . "\n\n";

// 3. Cek Aset Tetap
echo "3. ASET TETAP:\n";
$asets = \App\Models\Aset::all();
$totalAset = 0;

foreach ($asets as $aset) {
    $nilaiPerolehan = $aset->harga_perolehan + ($aset->biaya_perolehan ?? 0);
    $totalAset += $nilaiPerolehan;
    echo "   - {$aset->nama_aset}: Rp " . number_format($nilaiPerolehan, 0, ',', '.') . "\n";
}
echo "   TOTAL: Rp " . number_format($totalAset, 0, ',', '.') . "\n\n";

// 4. Total Aset yang seharusnya ada
$totalAsetSeharusnya = $totalBahanBaku + $totalBahanPendukung + $totalAset;

echo str_repeat('=', 60) . "\n\n";
echo "RINGKASAN:\n";
echo "Persediaan Bahan Baku: Rp " . number_format($totalBahanBaku, 0, ',', '.') . "\n";
echo "Persediaan Bahan Pendukung: Rp " . number_format($totalBahanPendukung, 0, ',', '.') . "\n";
echo "Aset Tetap (Nilai Perolehan): Rp " . number_format($totalAset, 0, ',', '.') . "\n";
echo "Kas & Piutang (dari saldo awal): Rp 250.000.000\n";
echo "\nTOTAL ASET SEHARUSNYA: Rp " . number_format($totalAsetSeharusnya + 250000000, 0, ',', '.') . "\n";
echo "MODAL (dari saldo awal): Rp 1.452.450.000\n";
echo "\nSELISIH: Rp " . number_format(1452450000 - ($totalAsetSeharusnya + 250000000), 0, ',', '.') . "\n\n";

// Cek COA Persediaan
echo "\n=== CEK COA PERSEDIAAN ===\n\n";
$coaPersediaan = \App\Models\Coa::where('nama_akun', 'LIKE', '%Persediaan%')
    ->orWhere('nama_akun', 'LIKE', '%Inventory%')
    ->get();

echo "COA Persediaan yang tersedia:\n";
foreach ($coaPersediaan as $coa) {
    echo "  - {$coa->kode_akun} {$coa->nama_akun}\n";
    echo "    Saldo Awal: Rp " . number_format($coa->saldo_awal, 0, ',', '.') . "\n";
    echo "    Tipe: {$coa->tipe_akun}\n\n";
}

echo "\nREKOMENDASI:\n";
echo "Untuk membuat Neraca Saldo balance, perlu menambahkan saldo awal untuk:\n";
if ($totalBahanBaku > 0) {
    echo "1. COA Persediaan Bahan Baku: Rp " . number_format($totalBahanBaku, 0, ',', '.') . "\n";
}
if ($totalBahanPendukung > 0) {
    echo "2. COA Persediaan Bahan Pendukung: Rp " . number_format($totalBahanPendukung, 0, ',', '.') . "\n";
}
if ($totalAset > 0) {
    echo "3. COA Aset Tetap (per kategori): Rp " . number_format($totalAset, 0, ',', '.') . "\n";
}
