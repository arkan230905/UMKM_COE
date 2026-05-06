<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG HPP JURNAL PENJUALAN ===\n\n";

// Check total penjualan
$totalPenjualan = \App\Models\Penjualan::count();
echo "Total Penjualan: {$totalPenjualan}\n";

// Check total journal entries for sales
$totalJournalEntries = \App\Models\JournalEntry::where('ref_type', 'sale')->count();
echo "Total Journal Entries (sales): {$totalJournalEntries}\n";

// Check total journal lines with HPP account (51)
$coaHpp = \App\Models\Coa::where('kode_akun', '51')->first();
if ($coaHpp) {
    $totalHppLines = \App\Models\JournalLine::where('coa_id', $coaHpp->id)->count();
    echo "Total Journal Lines with HPP (51): {$totalHppLines}\n";
} else {
    echo "COA HPP (51) not found!\n";
}

// Check some sample penjualan data
$samplePenjualan = \App\Models\Penjualan::with(['details.produk'])->take(3)->get();
echo "\n=== SAMPLE PENJUALAN ===\n";
foreach ($samplePenjualan as $penjualan) {
    echo "Penjualan #{$penjualan->id} - Total: {$penjualan->total}\n";
    if ($penjualan->details->count() > 0) {
        foreach ($penjualan->details as $detail) {
            $hpp = $detail->produk ? $detail->produk->getActualHPP($penjualan->tanggal) : 0;
            echo "  - Produk: {$detail->produk->nama_produk} | Qty: {$detail->jumlah} | HPP: {$hpp}\n";
        }
    } else {
        $hpp = $penjualan->produk ? $penjualan->produk->getActualHPP($penjualan->tanggal) : 0;
        echo "  - Produk: {$penjualan->produk->nama_produk} | Qty: {$penjualan->jumlah} | HPP: {$hpp}\n";
    }
}

echo "\n=== DEBUG PEMBAYARAN BEBAN ===\n";

// Check pembayaran beban data
$totalPembayaranBeban = \App\Models\PembayaranBeban::count();
echo "Total Pembayaran Beban: {$totalPembayaranBeban}\n";

// Check pembayaran beban by current user (assuming user_id = 1 for testing)
$totalPembayaranBebanUser1 = \App\Models\PembayaranBeban::where('user_id', 1)->count();
echo "Total Pembayaran Beban (User 1): {$totalPembayaranBebanUser1}\n";

// Show sample pembayaran beban
$samplePembayaran = \App\Models\PembayaranBeban::take(3)->get();
echo "\n=== SAMPLE PEMBAYARAN BEBAN ===\n";
foreach ($samplePembayaran as $pembayaran) {
    echo "Pembayaran #{$pembayaran->id} - User ID: {$pembayaran->user_id} - Jumlah: {$pembayaran->jumlah} - Tanggal: {$pembayaran->tanggal}\n";
}

echo "\n=== DEBUG COMPLETED ===\n";
