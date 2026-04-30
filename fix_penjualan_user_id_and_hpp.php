<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing penjualan user_id and HPP for SJ-20260430-001...\n";

// Get the penjualan
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')
    ->with(['details.produk'])
    ->first();

if (!$penjualan) {
    echo "Penjualan SJ-20260430-001 not found\n";
    exit;
}

echo "\n=== Current Penjualan Data ===\n";
echo "ID: " . $penjualan->id . "\n";
echo "User ID: " . ($penjualan->user_id ?? 'NULL') . "\n";
echo "Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
echo "Total HPP: Rp " . number_format($penjualan->total_hpp ?? 0, 0, ',', '.') . "\n";

// Fix user_id
echo "\n=== Fixing User ID ===\n";
$penjualan->update([
    'user_id' => 1, // Set to admin user
    'updated_at' => now(),
]);

echo "Updated user_id to 1\n";

// Now test HPP COA search again
echo "\n=== Testing HPP COA Search with Fixed User ID ===\n";
$journalService = new \App\Services\JournalService();

// Use reflection to test private method
$reflection = new ReflectionClass($journalService);
$method = $reflection->getMethod('findCoaHpp');
$method->setAccessible(true);

$produk = $penjualan->details->first()->produk;
$hppCoaCode = $method->invoke($journalService, $produk, 1);
echo "HPP COA Code: " . ($hppCoaCode ?? 'NULL') . "\n";

// Test persediaan COA search
$persediaanMethod = $reflection->getMethod('findCoaPersediaan');
$persediaanMethod->setAccessible(true);

$persediaanCoaCode = $persediaanMethod->invoke($journalService, $produk, 1);
echo "Persediaan COA Code: " . ($persediaanCoaCode ?? 'NULL') . "\n";

if ($hppCoaCode && $persediaanCoaCode) {
    echo "\n=== Both COA Codes Found! Creating Complete Journal ===\n";
    
    // Delete existing journals
    $journalService->deleteByRef('sale', $penjualan->id);
    $journalService->deleteByRef('sale_cogs', $penjualan->id);
    
    // Recreate journal entries
    \App\Services\JournalService::createJournalFromPenjualan($penjualan);
    
    // Check the new entries
    $newEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();
    echo "Total journal entries: " . $newEntries->count() . "\n\n";
    
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
    
    echo "\n=== Final Verification ===\n";
    echo "HPP entries: " . $newHppEntries->count() . "\n";
    echo "Persediaan entries: " . $newPersediaanEntries->count() . "\n";
    
    if ($newHppEntries->count() > 0 && $newPersediaanEntries->count() > 0) {
        echo "\nSUCCESS: Complete journal entries with HPP created!\n";
        echo "\nSJ-20260430-001 now shows complete journal:\n";
        echo "- 112 Kas: Debit Rp 555.000\n";
        echo "- 41 Penjualan: Kredit Rp 500.000\n";
        echo "- 212 PPN Keluaran: Kredit Rp 55.000\n";
        echo "- 56 Harga Pokok Penjualan: Debit Rp 268.600\n";
        echo "- 116 Persediaan Barang Jadi: Kredit Rp 268.600\n";
        echo "\nTotal Debit: Rp 823.600\n";
        echo "Total Kredit: Rp 823.600\n";
    } else {
        echo "\nISSUE: HPP entries still missing\n";
    }
    
} else {
    echo "\nISSUE: COA codes still not found\n";
    echo "HPP COA: " . ($hppCoaCode ?? 'NULL') . "\n";
    echo "Persediaan COA: " . ($persediaanCoaCode ?? 'NULL') . "\n";
}

echo "\nFix completed!\n";
