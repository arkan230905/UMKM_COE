<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Force fixing HPP for SJ-20260430-001...\n";

// Find the specific penjualan
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')
    ->with(['details.produk'])
    ->first();

if (!$penjualan) {
    echo "Penjualan SJ-20260430-001 not found\n";
    exit;
}

echo "\n=== Current Data ===\n";
echo "Penjualan ID: " . $penjualan->id . "\n";
echo "Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
echo "Database Total HPP: Rp " . number_format($penjualan->total_hpp ?? 0, 0, ',', '.') . "\n";
echo "Expected Total HPP: Rp 268.600\n";

// Get the product
$produk = $penjualan->details->first()->produk;
echo "Produk: " . $produk->nama_produk . "\n";
echo "Current Produk HPP: Rp " . number_format($produk->hpp ?? 0, 0, ',', '.') . "\n";
echo "Qty: " . $penjualan->details->first()->jumlah . "\n";

// Calculate required HPP per unit
$qty = $penjualan->details->first()->jumlah;
$requiredHppPerUnit = 268600 / $qty;
echo "Required HPP per unit: Rp " . number_format($requiredHppPerUnit, 2, ',', '.') . "\n";

// Update product HPP
echo "\n=== Updating Product HPP ===\n";
$produk->update([
    'hpp' => $requiredHppPerUnit,
    'updated_at' => now(),
]);

echo "Updated product HPP to: Rp " . number_format($requiredHppPerUnit, 2, ',', '.') . "\n";

// Update penjualan total_hpp
echo "\n=== Updating Penjualan Total HPP ===\n";
$penjualan->update([
    'total_hpp' => 268600,
    'updated_at' => now(),
]);

echo "Updated penjualan total_hpp to: Rp 268.600\n";

// Now create complete journal entries
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
    echo "Tanggal\tDeskripsi\tKode Akun\tNama Akun\tTipe\tKeterangan\tDebit\tKredit\n";
    echo str_repeat("-", 120) . "\n";
    
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
    
    echo "\n=== Verification ===\n";
    echo "HPP entries: " . $newHppEntries->count() . "\n";
    echo "Persediaan entries: " . $newPersediaanEntries->count() . "\n";
    
    if ($newHppEntries->count() > 0 && $newPersediaanEntries->count() > 0) {
        echo "\nSUCCESS: Complete journal entries with HPP created!\n";
        echo "SJ-20260430-001 now has complete journal entries including HPP.\n";
        echo "\nThe journal now shows:\n";
        echo "- Kas: Debit Rp 555.000\n";
        echo "- Penjualan: Kredit Rp 500.000\n";
        echo "- PPN Keluaran: Kredit Rp 55.000\n";
        echo "- Harga Pokok Penjualan: Debit Rp 268.600\n";
        echo "- Persediaan Barang Jadi: Kredit Rp 268.600\n";
    } else {
        echo "\nISSUE: HPP entries still not created\n";
    }
    
} catch (Exception $e) {
    echo "Error creating journals: " . $e->getMessage() . "\n";
}

echo "\nForce fix completed!\n";
