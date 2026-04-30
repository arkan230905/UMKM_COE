<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Investigating HPP discrepancy for SJ-20260430-001...\n";

// Find the specific penjualan
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')
    ->with(['details.produk'])
    ->first();

if (!$penjualan) {
    echo "Penjualan SJ-20260430-001 not found\n";
    exit;
}

echo "\n=== Database vs Display Analysis ===\n";
echo "Database Total HPP: Rp " . number_format($penjualan->total_hpp ?? 0, 0, ',', '.') . "\n";
echo "Display Total HPP: Rp 268.600 (from user report)\n";
echo "Discrepancy: Rp " . number_format(268600 - ($penjualan->total_hpp ?? 0), 0, ',', '.') . "\n";

// Calculate what HPP should be based on details
echo "\n=== Recalculating HPP from Details ===\n";
$calculatedHpp = 0;
foreach ($penjualan->details as $detail) {
    $produkHpp = $detail->produk->hpp ?? $detail->produk->harga_pokok ?? $detail->produk->harga_bom ?? 0;
    $detailHpp = $produkHpp * $detail->jumlah;
    $calculatedHpp += $detailHpp;
    
    echo "Produk: " . $detail->produk->nama_produk . "\n";
    echo "  Qty: " . $detail->jumlah . "\n";
    echo "  Produk HPP: Rp " . number_format($produkHpp, 0, ',', '.') . "\n";
    echo "  Detail HPP: Rp " . number_format($detailHpp, 0, ',', '.') . "\n";
    echo "---\n";
}

echo "Calculated HPP: Rp " . number_format($calculatedHpp, 0, ',', '.') . "\n";

// Check if calculated HPP matches display
if ($calculatedHpp == 268600) {
    echo "\nMATCH: Calculated HPP matches display value!\n";
    echo "Need to update database total_hpp field\n";
    
    // Update the penjualan with correct HPP
    $penjualan->update([
        'total_hpp' => $calculatedHpp,
        'updated_at' => now(),
    ]);
    
    echo "Updated database total_hpp to Rp " . number_format($calculatedHpp, 0, ',', '.') . "\n";
    
    // Now create the journal entries
    echo "\n=== Creating Complete Journal Entries ===\n";
    
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
        
        echo "=== Complete Journal Entries for SJ-20260430-001 ===\n";
        foreach ($newEntries as $jurnal) {
            echo \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') . "\t";
            echo "Penjualan #" . $penjualan->nomor_penjualan . "\t";
            echo $jurnal->coa->kode_akun . "\t";
            echo $jurnal->coa->nama_akun . "\t";
            echo $jurnal->coa->tipe_akun . "\t";
            echo $jurnal->keterangan . "\t";
            echo ($jurnal->debit > 0 ? "Rp " . number_format($jurnal->debit, 0, ',', '.') : "-") . "\t";
            echo ($jurnal->kredit > 0 ? "Rp " . number_format($jurnal->kredit, 0, ',', '.') : "-") . "\n";
        }
        
        $newHppEntries = $newEntries->filter(function($jurnal) {
            return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
                   strpos($jurnal->coa->kode_akun, '56') !== false;
        });
        
        $newPersediaanEntries = $newEntries->filter(function($jurnal) {
            return strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
                   strpos($jurnal->coa->kode_akun, '116') !== false;
        });
        
        echo "\nHPP entries: " . $newHppEntries->count() . "\n";
        echo "Persediaan entries: " . $newPersediaanEntries->count() . "\n";
        
        if ($newHppEntries->count() > 0 && $newPersediaanEntries->count() > 0) {
            echo "\nSUCCESS: Complete journal entries with HPP created!\n";
            echo "SJ-20260430-001 now has complete journal entries including HPP.\n";
        } else {
            echo "\nISSUE: HPP entries still not created\n";
        }
        
    } catch (Exception $e) {
        echo "Error creating journals: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "\nNO MATCH: Calculated HPP doesn't match display\n";
    echo "Calculated: Rp " . number_format($calculatedHpp, 0, ',', '.') . "\n";
    echo "Display: Rp 268.600\n";
    echo "Need further investigation\n";
}

echo "\nInvestigation completed!\n";
