<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COA BAHAN BAKU ===\n";
$bahanBakus = \App\Models\BahanBaku::with('coaPembelian', 'coaPersediaan', 'coaHpp')->get();
foreach ($bahanBakus as $bahan) {
    echo "{$bahan->nama_bahan}:\n";
    echo "  COA Pembelian: " . ($bahan->coa_pembelian_id ? $bahan->coaPembelian->kode_akun . ' - ' . $bahan->coaPembelian->nama_akun : 'Tidak ada') . "\n";
    echo "  COA Persediaan: " . ($bahan->coa_persediaan_id ? $bahan->coaPersediaan->kode_akun . ' - ' . $bahan->coaPersediaan->nama_akun : 'Tidak ada') . "\n";
    echo "  COA HPP: " . ($bahan->coa_hpp_id ? $bahan->coaHpp->kode_akun . ' - ' . $bahan->coaHpp->nama_akun : 'Tidak ada') . "\n\n";
}

echo "=== COA BAHAN PENDUKUNG ===\n";
$bahanPendukungs = \App\Models\BahanPendukung::with('coaPembelian', 'coaPersediaan', 'coaHpp')->get();
foreach ($bahanPendukungs as $bahan) {
    echo "{$bahan->nama_bahan}:\n";
    echo "  COA Pembelian: " . ($bahan->coa_pembelian_id ? $bahan->coaPembelian->kode_akun . ' - ' . $bahan->coaPembelian->nama_akun : 'Tidak ada') . "\n";
    echo "  COA Persediaan: " . ($bahan->coa_persediaan_id ? $bahan->coaPersediaan->kode_akun . ' - ' . $bahan->coaPersediaan->nama_akun : 'Tidak ada') . "\n";
    echo "  COA HPP: " . ($bahan->coa_hpp_id ? $bahan->coaHpp->kode_akun . ' - ' . $bahan->coaHpp->nama_akun : 'Tidak ada') . "\n\n";
}

?>