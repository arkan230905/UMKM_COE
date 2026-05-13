<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking jurnal_umum for user_id 20:\n\n";

$journals = DB::table('jurnal_umum')
    ->where('user_id', 20)
    ->select('tipe_referensi', DB::raw('COUNT(*) as count'))
    ->groupBy('tipe_referensi')
    ->get();

foreach($journals as $j) {
    echo $j->tipe_referensi . ': ' . $j->count . " entries\n";
}

echo "\nTotal entries: " . DB::table('jurnal_umum')->where('user_id', 20)->count() . "\n";

echo "\n--- Sample Pembelian Journals ---\n";
$pembelianJournals = DB::table('jurnal_umum')
    ->where('user_id', 20)
    ->where('tipe_referensi', 'pembelian')
    ->limit(5)
    ->get(['tanggal', 'referensi', 'keterangan', 'debit', 'kredit']);

foreach($pembelianJournals as $j) {
    echo "Date: {$j->tanggal}, Ref: {$j->referensi}, Debit: {$j->debit}, Credit: {$j->kredit}\n";
}

echo "\n--- Sample Penjualan Journals ---\n";
$penjualanJournals = DB::table('jurnal_umum')
    ->where('user_id', 20)
    ->where('tipe_referensi', 'sale')
    ->limit(5)
    ->get(['tanggal', 'referensi', 'keterangan', 'debit', 'kredit']);

if ($penjualanJournals->isEmpty()) {
    echo "No penjualan journals found!\n";
} else {
    foreach($penjualanJournals as $j) {
        echo "Date: {$j->tanggal}, Ref: {$j->referensi}, Debit: {$j->debit}, Credit: {$j->kredit}\n";
    }
}

echo "\n--- Recent Penjualan Records ---\n";
$penjualans = DB::table('penjualans')
    ->where('user_id', 20)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['id', 'nomor_penjualan', 'tanggal', 'grand_total']);

foreach($penjualans as $p) {
    echo "ID: {$p->id}, Nomor: {$p->nomor_penjualan}, Total: {$p->grand_total}\n";
}

echo "\n--- Recent Pembelian Records ---\n";
$pembelians = DB::table('pembelians')
    ->where('user_id', 20)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['id', 'nomor_pembelian', 'tanggal', 'total', 'payment_method']);

foreach($pembelians as $p) {
    echo "ID: {$p->id}, Nomor: {$p->nomor_pembelian}, Method: {$p->payment_method}, Total: {$p->total}\n";
}
