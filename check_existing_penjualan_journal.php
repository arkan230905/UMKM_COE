<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking existing penjualan and journal entries...\n";

// Get the existing penjualan detail
$detail = \Illuminate\Support\Facades\DB::table('penjualan_details')->first();
echo "Found penjualan detail ID: {$detail->id}\n";

// Get the penjualan
$penjualan = \App\Models\Penjualan::find($detail->penjualan_id);
if ($penjualan) {
    echo "\n=== Penjualan Details ===\n";
    echo "ID: " . $penjualan->id . "\n";
    echo "Tanggal: " . $penjualan->tanggal . "\n";
    echo "Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
    echo "Total HPP: Rp " . number_format($penjualan->total_hpp ?? 0, 0, ',', '.') . "\n";
    echo "Status: " . $penjualan->status . "\n";
    
    // Check journal entries
    echo "\n=== Journal Entries ===\n";
    $jurnalEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();
    echo "Total journal entries: " . $jurnalEntries->count() . "\n\n";
    
    $hasHpp = false;
    $hasPersediaan = false;
    
    foreach ($jurnalEntries as $jurnal) {
        echo "COA: " . $jurnal->coa->nama_akun . " (" . $jurnal->coa->kode_akun . ")\n";
        echo ($jurnal->debit > 0 ? "Debit: Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit: Rp " . number_format($jurnal->kredit, 0, ',', '.'));
        echo "\n";
        echo "Keterangan: " . $jurnal->keterangan . "\n";
        echo "---\n";
        
        if (strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
            strpos($jurnal->coa->kode_akun, '56') !== false) {
            $hasHpp = true;
        }
        
        if (strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
            strpos($jurnal->coa->kode_akun, '116') !== false) {
            $hasPersediaan = true;
        }
    }
    
    echo "\n=== HPP Analysis ===\n";
    echo "HPP entries found: " . ($hasHpp ? "YES" : "NO") . "\n";
    echo "Persediaan entries found: " . ($hasPersediaan ? "YES" : "NO") . "\n";
    
    if (!$hasHpp && $penjualan->total_hpp > 0) {
        echo "\nISSUE: HPP entries are missing!\n";
        echo "Expected HPP amount: Rp " . number_format($penjualan->total_hpp, 0, ',', '.') . "\n";
        
        // Try to recreate journal entries
        echo "\nAttempting to recreate journal entries...\n";
        
        try {
            // Delete existing journals
            $journalService = new \App\Services\JournalService();
            $journalService->deleteByRef('sale', $penjualan->id);
            $journalService->deleteByRef('sale_cogs', $penjualan->id);
            
            // Recreate
            \App\Services\JournalService::createJournalFromPenjualan($penjualan);
            
            echo "Journal recreation completed\n";
            
            // Check again
            $newEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();
            $newHppEntries = $newEntries->filter(function($jurnal) {
                return strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
                       strpos($jurnal->coa->kode_akun, '56') !== false;
            });
            
            echo "New total entries: " . $newEntries->count() . "\n";
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
            echo "Error recreating journals: " . $e->getMessage() . "\n";
        }
    }
    
} else {
    echo "No penjualan found for detail ID {$detail->penjualan_id}\n";
}

echo "\nExisting penjualan journal check completed!\n";
