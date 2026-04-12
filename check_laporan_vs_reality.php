<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING LAPORAN KAS BANK VS REALITY\n";
echo "====================================\n\n";

// Get all journals for April 2026
$startDate = '2026-04-01';
$endDate = '2026-04-30';

echo "JOURNAL ENTRIES (Database):\n";
echo "============================\n";

$allJournals = App\Models\JurnalUmum::whereBetween('tanggal', [$startDate, $endDate])
    ->whereIn('coa_id', [2, 3]) // Kas Bank (2) + Kas (3)
    ->with('coa')
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

foreach ($allJournals as $journal) {
    $type = $journal->debit > 0 ? 'MASUK' : 'KELUAR';
    $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
    echo "  {$journal->referensi} | {$journal->coa->kode_akun} | {$type} | Rp " . number_format($amount, 0, ',', '.') . " | {$journal->tipe_referensi}\n";
}

echo "\nACTUAL TRANSACTION DATA:\n";
echo "=======================\n";

echo "\nPENJUALAN (Database):\n";
$penjualans = App\Models\Penjualan::with('details')->get();
foreach ($penjualans as $p) {
    $total = $p->details->sum('subtotal');
    echo "  {$p->nomor_penjualan} | {$p->payment_method} | Rp " . number_format($total, 0, ',', '.') . "\n";
}

echo "\nPEMBELIAN (Database):\n";
$pembelians = App\Models\Pembelian::get();
foreach ($pembelians as $p) {
    echo "  {$p->nomor_pembelian} | {$p->payment_method} | Rp " . number_format($p->total_harga, 0, ',', '.') . "\n";
}

echo "\nPEMBAYARAN BEBAN (Database):\n";
$beban = App\Models\PembayaranBeban::get();
foreach ($beban as $b) {
    echo "  PB-{$b->id} | Rp " . number_format($b->jumlah, 0, ',', '.') . "\n";
}

echo "\nISSUE ANALYSIS:\n";
echo "==============\n";

// Check for missing journals
echo "\nMissing Journals:\n";

// Check penjualan journals
echo "\nPenjualan:\n";
foreach ($penjualans as $p) {
    $journalCount = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
        ->where('referensi', $p->nomor_penjualan)
        ->count();
    if ($journalCount == 0) {
        echo "  MISSING: {$p->nomor_penjualan} - No journal found\n";
    } else {
        echo "  OK: {$p->nomor_penjualan} - {$journalCount} journals\n";
    }
}

// Check pembelian journals
echo "\nPembelian:\n";
foreach ($pembelians as $p) {
    $journalCount = App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
        ->where('referensi', $p->nomor_pembelian)
        ->count();
    if ($journalCount == 0) {
        echo "  MISSING: {$p->nomor_pembelian} - No journal found\n";
    } else {
        echo "  OK: {$p->nomor_pembelian} - {$journalCount} journals\n";
    }
}

// Check pembayaran beban journals
echo "\nPembayaran Beban:\n";
foreach ($beban as $b) {
    $journalCount = App\Models\JurnalUmum::where('tipe_referensi', 'pembayaran_beban')
        ->where('referensi', 'PB-' . $b->id)
        ->count();
    if ($journalCount == 0) {
        echo "  MISSING: PB-{$b->id} - No journal found\n";
    } else {
        echo "  OK: PB-{$b->id} - {$journalCount} journals\n";
    }
}

echo "\nSUMMARY:\n";
echo "========\n";
echo "Total Journals: " . $allJournals->count() . "\n";
echo "Total Penjualan: " . $penjualans->count() . "\n";
echo "Total Pembelian: " . $pembelians->count() . "\n";
echo "Total Pembayaran Beban: " . $beban->count() . "\n";

?>
