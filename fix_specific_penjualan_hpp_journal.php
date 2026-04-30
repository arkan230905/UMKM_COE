<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing specific penjualan HPP journal for SJ-20260430-001...\n";

// Find the specific penjualan
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')
    ->with(['details.produk'])
    ->first();

if (!$penjualan) {
    echo "Penjualan SJ-20260430-001 not found\n";
    exit;
}

echo "\n=== Penjualan Details ===\n";
echo "ID: " . $penjualan->id . "\n";
echo "Nomor: " . $penjualan->nomor_penjualan . "\n";
echo "Tanggal: " . $penjualan->tanggal . "\n";
echo "Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
echo "Total HPP: Rp " . number_format($penjualan->total_hpp, 0, ',', '.') . "\n";
echo "Status: " . $penjualan->status . "\n";

// Check details
echo "\n=== Penjualan Details ===\n";
foreach ($penjualan->details as $detail) {
    echo "Produk: " . $detail->produk->nama_produk . "\n";
    echo "Qty: " . $detail->jumlah . "\n";
    echo "Harga: Rp " . number_format($detail->harga_satuan, 0, ',', '.') . "\n";
    echo "Subtotal: Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n";
    echo "Produk HPP: Rp " . number_format($detail->produk->hpp ?? 0, 0, ',', '.') . "\n";
    
    $detailHpp = ($detail->produk->hpp ?? 0) * $detail->jumlah;
    echo "Detail HPP: Rp " . number_format($detailHpp, 0, ',', '.') . "\n";
    echo "---\n";
}

// Check current journal entries
echo "\n=== Current Journal Entries ===\n";
$jurnalEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();
echo "Total journal entries: " . $jurnalEntries->count() . "\n\n";

foreach ($jurnalEntries as $jurnal) {
    echo "COA: " . $jurnal->coa->nama_akun . " (" . $jurnal->coa->kode_akun . ")\n";
    echo ($jurnal->debit > 0 ? "Debit: Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit: Rp " . number_format($jurnal->kredit, 0, ',', '.'));
    echo "\n";
    echo "Keterangan: " . $jurnal->keterangan . "\n";
    echo "---\n";
}

// Check if HPP entries are missing
echo "\n=== HPP Entry Analysis ===\n";
$hppEntries = $jurnalEntries->filter(function($jurnal) {
    return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
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
    
    // Fix the issue
    echo "\n=== Fixing HPP Journal Entries ===\n";
    
    try {
        // Delete existing journals first
        $journalService = new \App\Services\JournalService();
        $journalService->deleteByRef('sale', $penjualan->id);
        $journalService->deleteByRef('sale_cogs', $penjualan->id);
        
        echo "Deleted existing journal entries\n";
        
        // Recreate journal entries
        \App\Services\JournalService::createJournalFromPenjualan($penjualan);
        
        echo "Recreated journal entries\n";
        
        // Check the new entries
        $newEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();
        echo "New total entries: " . $newEntries->count() . "\n\n";
        
        $newHppEntries = $newEntries->filter(function($jurnal) {
            return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
                   strpos($jurnal->coa->kode_akun, '56') !== false;
        });
        
        $newPersediaanEntries = $newEntries->filter(function($jurnal) {
            return strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
                   strpos($jurnal->coa->kode_akun, '116') !== false;
        });
        
        echo "New HPP entries: " . $newHppEntries->count() . "\n";
        echo "New Persediaan entries: " . $newPersediaanEntries->count() . "\n";
        
        echo "\n=== Complete Journal Entries ===\n";
        foreach ($newEntries as $jurnal) {
            echo "Tanggal: " . \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') . "\n";
            echo "Deskripsi: Penjualan #" . $penjualan->nomor_penjualan . "\n";
            echo "Kode Akun: " . $jurnal->coa->kode_akun . "\n";
            echo "Nama Akun: " . $jurnal->coa->nama_akun . "\n";
            echo "Tipe: " . $jurnal->coa->tipe_akun . "\n";
            echo "Keterangan: " . $jurnal->keterangan . "\n";
            echo ($jurnal->debit > 0 ? "Debit: Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit: Rp " . number_format($jurnal->kredit, 0, ',', '.'));
            echo "\n";
            echo "---\n";
        }
        
        if ($newHppEntries->count() > 0 && $newPersediaanEntries->count() > 0) {
            echo "\nSUCCESS: Complete journal entries with HPP created!\n";
            echo "The penjualan journal now includes HPP entries.\n";
        } else {
            echo "\nISSUE: HPP entries still not created\n";
            echo "Need further investigation.\n";
        }
        
    } catch (Exception $e) {
        echo "Error fixing HPP entries: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "HPP entries are already present\n";
}

echo "\nFix completed for SJ-20260430-001!\n";
