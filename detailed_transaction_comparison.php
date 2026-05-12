<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DETAILED TRANSACTION COMPARISON\n";
echo "================================\n\n";

// Get all journals for April 2026
$startDate = '2026-04-01';
$endDate = '2026-04-30';

echo "1. ALL JOURNAL ENTRIES IN DATABASE:\n";
echo "===================================\n";

$allJournals = App\Models\JurnalUmum::whereBetween('tanggal', [$startDate, $endDate])
    ->whereIn('coa_id', [2, 3]) // Kas Bank (2) + Kas (3)
    ->with('coa')
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

foreach ($allJournals as $journal) {
    $type = $journal->debit > 0 ? 'MASUK' : 'KELUAR';
    $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
    echo "  {$journal->referensi} | {$journal->coa->kode_akun} | {$type} | Rp " . number_format($amount, 0, ',', '.') . " | {$journal->tipe_referensi} | {$journal->keterangan}\n";
}

echo "\n2. SIMULATING LAPORAN KAS BANK OUTPUT:\n";
echo "======================================\n";

// Simulate what LaporanKasBankController.getDetailMasuk would return
echo "\n--- TRANSAKSI MASUK ---\n";

$masukJournals = App\Models\JurnalUmum::whereIn('coa_id', [2, 3])
    ->where('debit', '>', 0)
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->orderBy('tanggal', 'desc')
    ->orderBy('id', 'desc')
    ->get();

foreach ($masukJournals as $journal) {
    // Simulate extractRefId logic
    $refId = null;
    if (preg_match('/(\d+)$/', $journal->referensi, $matches)) {
        $refId = (int)$matches[1];
    }
    
    // Simulate getTransactionDetail logic
    $detailInfo = $this->getTransactionDetail($journal->tipe_referensi, $refId);
    
    echo "  Tanggal: " . date('d/m/Y', strtotime($journal->tanggal)) . "\n";
    echo "  Nomor Transaksi: " . ($journal->referensi ?? $detailInfo['nomor_transaksi']) . "\n";
    echo "  Jenis: " . $detailInfo['jenis'] . "\n";
    echo "  Keterangan: " . ($journal->keterangan ?? $detailInfo['keterangan']) . "\n";
    echo "  Nominal: " . number_format($journal->debit, 0, ',', '.') . "\n";
    echo "  ---\n";
}

echo "\n--- TRANSAKSI KELUAR ---\n";

$keluarJournals = App\Models\JurnalUmum::whereIn('coa_id', [2, 3])
    ->where('kredit', '>', 0)
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->orderBy('tanggal', 'desc')
    ->orderBy('id', 'desc')
    ->get();

foreach ($keluarJournals as $journal) {
    // Simulate extractRefId logic
    $refId = null;
    if (preg_match('/(\d+)$/', $journal->referensi, $matches)) {
        $refId = (int)$matches[1];
    }
    
    // Simulate getTransactionDetail logic
    $detailInfo = $this->getTransactionDetail($journal->tipe_referensi, $refId);
    
    echo "  Tanggal: " . date('d/m/Y', strtotime($journal->tanggal)) . "\n";
    echo "  Nomor Transaksi: " . ($journal->referensi ?? $detailInfo['nomor_transaksi']) . "\n";
    echo "  Jenis: " . $detailInfo['jenis'] . "\n";
    echo "  Keterangan: " . ($journal->keterangan ?? $detailInfo['keterangan']) . "\n";
    echo "  Nominal: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    echo "  ---\n";
}

echo "\n3. ACTUAL TRANSACTION DATA COMPARISON:\n";
echo "=====================================\n";

// Check actual penjualan data
echo "PENJUALAN DATA:\n";
$penjualans = App\Models\Penjualan::all();
foreach ($penjualans as $p) {
    echo "  DB: {$p->nomor_penjualan} | Method: {$p->payment_method} | Total: " . ($p->details->sum('subtotal')) . "\n";
}

echo "\nPEMBELIAN DATA:\n";
$pembelians = App\Models\Pembelian::all();
foreach ($pembelians as $p) {
    echo "  DB: {$p->nomor_pembelian} | Method: {$p->payment_method} | Total: {$p->total_harga}\n";
}

echo "\nPEMBAYARAN BEBAN DATA:\n";
$beban = App\Models\PembayaranBeban::all();
foreach ($beban as $b) {
    echo "  DB: PB-{$b->id} | Jumlah: {$b->jumlah}\n";
}

// Helper function
function getTransactionDetail($refType, $refId) {
    $defaultDetail = [
        'nomor_transaksi' => 'N/A',
        'jenis' => 'Transaksi',
        'keterangan' => 'Transaksi umum'
    ];

    try {
        switch ($refType) {
            case 'sale':
            case 'penjualan':
                $sale = \App\Models\Penjualan::find($refId);
                if ($sale) {
                    return [
                        'nomor_transaksi' => $sale->nomor_penjualan ?? "PJ-{$refId}",
                        'jenis' => 'Penjualan',
                        'keterangan' => 'Penjualan ' . ucfirst($sale->payment_method ?? 'cash')
                    ];
                }
                break;
                
            case 'purchase':
            case 'pembelian':
                $purchase = \App\Models\Pembelian::find($refId);
                if ($purchase) {
                    return [
                        'nomor_transaksi' => $purchase->nomor_pembelian ?? "PB-{$refId}",
                        'jenis' => 'Pembelian',
                        'keterangan' => 'Pembelian ' . ucfirst($purchase->payment_method ?? 'cash')
                    ];
                }
                break;
                
            case 'expense_payment':
            case 'expense':
                $expense = \App\Models\ExpensePayment::with('bebanOperasional')->find($refId);
                if ($expense) {
                    $bebanName = $expense->bebanOperasional->nama_beban ?? 'Beban';
                    return [
                        'nomor_transaksi' => "BP-{$refId}",
                        'jenis' => 'Pembayaran Beban',
                        'keterangan' => "Pembayaran {$bebanName}"
                    ];
                }
                break;
                
            case 'pembayaran_beban':
                return [
                    'nomor_transaksi' => "PB-{$refId}",
                    'jenis' => 'Pembayaran Beban',
                    'keterangan' => 'Pembayaran Beban'
                ];
                break;
                
            case 'penggajian':
            case 'payroll':
                $penggajian = \App\Models\Penggajian::find($refId);
                if ($penggajian) {
                    return [
                        'nomor_transaksi' => "GJ-{$refId}",
                        'jenis' => 'Penggajian',
                        'keterangan' => 'Penggajian karyawan'
                    ];
                }
                break;
        }
    } catch (\Exception $e) {
        // Return default
    }

    return $defaultDetail;
}

?>
