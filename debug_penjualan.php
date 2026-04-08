<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG PENJUALAN SJ-20260408-002 ===\n";

// Cari penjualan berdasarkan nomor
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260408-002')->first();

if ($penjualan) {
    echo "PENJUALAN DITEMUKAN:\n";
    echo "ID: " . $penjualan->id . "\n";
    echo "Nomor: " . $penjualan->nomor_penjualan . "\n";
    echo "Tanggal: " . $penjualan->tanggal . "\n";
    echo "Payment Method: " . ($penjualan->payment_method ?? 'NULL') . "\n";
    echo "Total: " . $penjualan->total . "\n";
    echo "Created: " . $penjualan->created_at . "\n";
    echo "Updated: " . $penjualan->updated_at . "\n";
    
    // Cek detail penjualan
    echo "\nDETAIL PENJUALAN:\n";
    $details = $penjualan->details;
    foreach ($details as $detail) {
        echo "- Produk ID: " . $detail->produk_id . ", Qty: " . $detail->jumlah . ", Harga: " . $detail->harga_satuan . "\n";
    }
    
    // Cek jurnal entries
    echo "\nJURNAL ENTRIES:\n";
    $journals = \App\Models\JournalEntry::where('ref_type', 'sale')
        ->where('ref_id', $penjualan->id)
        ->get();
    
    foreach ($journals as $journal) {
        echo "Journal ID: " . $journal->id . ", Description: " . $journal->description . "\n";
        $lines = $journal->lines;
        foreach ($lines as $line) {
            $coa = $line->coa;
            echo "  - COA: " . $coa->nama_akun . " (" . $coa->kode_akun . ") - Debit: " . $line->debit . ", Credit: " . $line->credit . "\n";
        }
    }
    
} else {
    echo "PENJUALAN TIDAK DITEMUKAN!\n";
    
    // Cari semua penjualan di tanggal tersebut
    echo "\nPENJUALAN DI TANGGAL 2026-04-08:\n";
    $penjualans = \App\Models\Penjualan::whereDate('tanggal', '2026-04-08')->get();
    foreach ($penjualans as $p) {
        echo "- ID: " . $p->id . ", Nomor: " . $p->nomor_penjualan . ", Payment: " . ($p->payment_method ?? 'NULL') . ", Total: " . $p->total . "\n";
    }
}
