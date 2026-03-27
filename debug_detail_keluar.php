<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Coa;

echo "=== DEBUG DETAIL KELUAR ===\n";

// Find the Kas di Bank account
$coa = Coa::where('kode_akun', '1102')->first();
if (!$coa) {
    echo "COA 1102 not found!\n";
    exit;
}

echo "COA Found: {$coa->kode_akun} - {$coa->nama_akun}\n";

$startDate = now()->startOfMonth()->format('Y-m-d');
$endDate = now()->endOfMonth()->format('Y-m-d');
echo "Date range: {$startDate} to {$endDate}\n\n";

// Test the pembelian query directly
echo "=== TESTING PEMBELIAN QUERY ===\n";
$pembelianTransaksi = DB::table('pembelians')
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->where(function($query) use ($coa) {
        $query->where(function($subQuery) use ($coa) {
            // Jika akun adalah Bank (mengandung kata 'bank') - check this first
            if ($coa && stripos($coa->nama_akun, 'bank') !== false) {
                echo "  - Checking for transfer payments\n";
                $subQuery->where('payment_method', 'transfer');
            }
            // Jika akun adalah Kas (mengandung kata 'kas' tapi bukan 'bank')
            elseif ($coa && stripos($coa->nama_akun, 'kas') !== false) {
                echo "  - Checking for cash payments\n";
                $subQuery->where('payment_method', 'cash');
            }
        });
    })
    ->orderBy('tanggal', 'desc')
    ->get();

echo "Found " . $pembelianTransaksi->count() . " pembelian transactions:\n";
foreach ($pembelianTransaksi as $p) {
    echo "- Date: {$p->tanggal}, Method: {$p->payment_method}, Total: {$p->total_harga}, Nomor: {$p->nomor_pembelian}\n";
}

// Test the mapping function
$mapped = $pembelianTransaksi->map(function($pembelian) {
    return [
        'tanggal' => date('d/m/Y', strtotime($pembelian->tanggal)),
        'nomor_transaksi' => $pembelian->nomor_pembelian ?? 'PB-' . date('Y', strtotime($pembelian->tanggal)) . '-' . str_pad($pembelian->id, 3, '0', STR_PAD_LEFT),
        'jenis' => 'Pembelian',
        'keterangan' => 'Pembelian ' . ucfirst($pembelian->payment_method),
        'nominal' => (float)$pembelian->total_harga
    ];
});

echo "\nMapped transactions:\n";
foreach ($mapped as $m) {
    echo "- {$m['tanggal']} | {$m['nomor_transaksi']} | {$m['jenis']} | {$m['keterangan']} | Rp " . number_format($m['nominal'], 0, ',', '.') . "\n";
}