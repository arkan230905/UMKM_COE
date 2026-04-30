<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging journal creation failure...\n";

// Get the penjualan
$penjualan = \App\Models\Penjualan::where('nomor_penjualan', 'SJ-20260430-001')
    ->with(['details.produk'])
    ->first();

echo "\n=== Penjualan Data ===\n";
echo "ID: " . $penjualan->id . "\n";
echo "Total: " . $penjualan->total . "\n";
echo "Total HPP: " . $penjualan->total_hpp . "\n";
echo "User ID: " . $penjualan->user_id . "\n";

echo "\n=== Testing JournalService Step by Step ===\n";

try {
    $journalService = new \App\Services\JournalService();
    
    // Test findCoaHpp method
    echo "\n1. Testing findCoaHpp...\n";
    $produk = $penjualan->details->first()->produk;
    
    // Use reflection to test private method
    $reflection = new ReflectionClass($journalService);
    $method = $reflection->getMethod('findCoaHpp');
    $method->setAccessible(true);
    
    $hppCoaCode = $method->invoke($journalService, $produk, $penjualan->user_id);
    echo "HPP COA Code: " . ($hppCoaCode ?? 'NULL') . "\n";
    
    // Test findCoaPersediaan method
    echo "\n2. Testing findCoaPersediaan...\n";
    $persediaanMethod = $reflection->getMethod('findCoaPersediaan');
    $persediaanMethod->setAccessible(true);
    
    $persediaanCoaCode = $persediaanMethod->invoke($journalService, $produk, $penjualan->user_id);
    echo "Persediaan COA Code: " . ($persediaanCoaCode ?? 'NULL') . "\n";
    
    // Test createHPPLinesFromPenjualan method
    echo "\n3. Testing createHPPLinesFromPenjualan...\n";
    $hppLinesMethod = $reflection->getMethod('createHPPLinesFromPenjualan');
    $hppLinesMethod->setAccessible(true);
    
    $hppLines = $hppLinesMethod->invoke($journalService, $penjualan);
    echo "HPP Lines count: " . count($hppLines) . "\n";
    
    foreach ($hppLines as $i => $line) {
        echo "  Line " . ($i + 1) . ": " . $line['code'] . " - " . ($line['debit'] > 0 ? "Debit Rp " . number_format($line['debit'], 0, ',', '.') : "Kredit Rp " . number_format($line['credit'], 0, ',', '.')) . " - " . $line['memo'] . "\n";
    }
    
    // Test the complete createJournalFromPenjualan method
    echo "\n4. Testing createJournalFromPenjualan...\n";
    
    // Delete existing journals first
    $journalService->deleteByRef('sale', $penjualan->id);
    $journalService->deleteByRef('sale_cogs', $penjualan->id);
    
    echo "Deleted existing journals\n";
    
    // Call the method
    \App\Services\JournalService::createJournalFromPenjualan($penjualan);
    echo "createJournalFromPenjualan called\n";
    
    // Check results
    $newEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();
    echo "Journal entries created: " . $newEntries->count() . "\n";
    
    if ($newEntries->count() > 0) {
        echo "\n=== Created Journal Entries ===\n";
        foreach ($newEntries as $jurnal) {
            echo "  - " . $jurnal->coa->nama_akun . " (" . $jurnal->coa->kode_akun . "): ";
            echo ($jurnal->debit > 0 ? "Debit Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit Rp " . number_format($jurnal->kredit, 0, ',', '.'));
            echo "\n";
        }
    } else {
        echo "\nNo journal entries were created!\n";
        
        // Check if JournalEntry was created
        echo "\n5. Checking JournalEntry table...\n";
        $journalEntry = \App\Models\JournalEntry::where('ref_type', 'sale')
            ->where('ref_id', $penjualan->id)
            ->first();
            
        if ($journalEntry) {
            echo "JournalEntry found with ID: " . $journalEntry->id . "\n";
            
            // Check JournalLines
            $journalLines = \App\Models\JournalLine::where('journal_entry_id', $journalEntry->id)->get();
            echo "JournalLines count: " . $journalLines->count() . "\n";
            
            foreach ($journalLines as $line) {
                echo "  - COA ID: " . $line->coa_id . " - ";
                echo ($line->debit > 0 ? "Debit Rp " . number_format($line->debit, 0, ',', '.') : "Kredit Rp " . number_format($line->credit, 0, ',', '.'));
                echo "\n";
            }
        } else {
            echo "No JournalEntry found!\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nDebug completed!\n";
