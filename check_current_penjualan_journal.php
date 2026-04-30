<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking current penjualan journal entries...\n";

// Find the most recent penjualan
$penjualan = \App\Models\Penjualan::where('user_id', 1)
    ->orderBy('created_at', 'desc')
    ->first();

if (!$penjualan) {
    echo "No penjualan records found\n";
    exit;
}

echo "\n=== Penjualan Details ===\n";
echo "ID: " . $penjualan->id . "\n";
echo "Nomor: " . ($penjualan->nomor_penjualan ?? 'N/A') . "\n";
echo "Tanggal: " . $penjualan->tanggal . "\n";
echo "Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
echo "Total HPP: Rp " . number_format($penjualan->total_hpp, 0, ',', '.') . "\n";
echo "Status: " . $penjualan->status . "\n";

// Check penjualan details
echo "\n=== Penjualan Details ===\n";
foreach ($penjualan->details as $detail) {
    echo "Produk: " . $detail->produk->nama_produk . "\n";
    echo "Qty: " . $detail->jumlah . "\n";
    echo "Harga: Rp " . number_format($detail->harga_satuan, 0, ',', '.') . "\n";
    echo "Subtotal: Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n";
    echo "HPP Produk: Rp " . number_format($detail->produk->hpp ?? 0, 0, ',', '.') . "\n";
    echo "---\n";
}

// Check journal entries
echo "\n=== Current Journal Entries ===\n";
$jurnalEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();

echo "Total journal entries: " . $jurnalEntries->count() . "\n\n";

foreach ($jurnalEntries as $jurnal) {
    echo "COA: " . $jurnal->coa->nama_akun . " (" . $jurnal->coa->kode_akun . ")\n";
    echo "Tipe: " . $jurnal->coa->tipe_akun . "\n";
    echo ($jurnal->debit > 0 ? "Debit: Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit: Rp " . number_format($jurnal->kredit, 0, ',', '.'));
    echo "\n";
    echo "Keterangan: " . $jurnal->keterangan . "\n";
    echo "---\n";
}

// Check if HPP entries are missing
echo "\n=== HPP Entry Analysis ===\n";
$hppEntries = $jurnalEntries->filter(function($jurnal) {
    return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
           strpos($jurnal->coa->nama_akun, 'HPP') !== false ||
           strpos($jurnal->coa->kode_akun, '56') !== false;
});

$persediaanEntries = $jurnalEntries->filter(function($jurnal) {
    return strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
           strpos($jurnal->coa->kode_akun, '116') !== false;
});

echo "HPP entries found: " . $hppEntries->count() . "\n";
echo "Persediaan entries found: " . $persediaanEntries->count() . "\n";

if ($hppEntries->count() === 0 && $penjualan->total_hpp > 0) {
    echo "\nISSUE: HPP entries are missing!\n";
    echo "Expected HPP amount: Rp " . number_format($penjualan->total_hpp, 0, ',', '.') . "\n";
    
    // Try to create HPP entries manually
    echo "\nAttempting to create missing HPP entries...\n";
    
    try {
        $journalService = new \App\Services\JournalService();
        
        // Delete existing journals first
        $journalService->deleteByRef('sale', $penjualan->id);
        $journalService->deleteByRef('sale_cogs', $penjualan->id);
        
        // Recreate all journal entries
        \App\Services\JournalService::createJournalFromPenjualan($penjualan);
        
        echo "HPP journal recreation attempted\n";
        
        // Check again
        $newEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();
        $newHppEntries = $newEntries->filter(function($jurnal) {
            return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
                   strpos($jurnal->coa->nama_akun, 'HPP') !== false ||
                   strpos($jurnal->coa->kode_akun, '56') !== false;
        });
        
        echo "New HPP entries: " . $newHppEntries->count() . "\n";
        
        if ($newHppEntries->count() > 0) {
            echo "SUCCESS: HPP entries created!\n";
            foreach ($newHppEntries as $hpp) {
                echo "  - " . $hpp->coa->nama_akun . ": " . ($hpp->debit > 0 ? "Debit" : "Kredit") . " Rp " . number_format($hpp->debit + $hpp->kredit, 0, ',', '.') . "\n";
            }
        } else {
            echo "FAILED: HPP entries still not created\n";
        }
        
    } catch (Exception $e) {
        echo "Error creating HPP entries: " . $e->getMessage() . "\n";
    }
} else {
    echo "HPP entries are present\n";
}

echo "\nPenjualan journal check completed!\n";
