<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Penjualan Service and Journal creation...\n";

// Check if there are any penjualan records
$penjualans = \App\Models\Penjualan::where('user_id', 1)->get();
echo "Total Penjualan records: " . $penjualans->count() . "\n";

if ($penjualans->count() > 0) {
    foreach ($penjualans as $penjualan) {
        echo "\nPenjualan ID: " . $penjualan->id . "\n";
        echo "  Tanggal: " . $penjualan->tanggal . "\n";
        echo "  Customer: " . ($penjualan->pelanggan->nama ?? 'N/A') . "\n";
        echo "  Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
        echo "  Total HPP: Rp " . number_format($penjualan->total_hpp, 0, ',', '.') . "\n";
        echo "  Status: " . $penjualan->status . "\n";
        
        // Check details
        echo "  Details: " . $penjualan->details->count() . " items\n";
        foreach ($penjualan->details as $detail) {
            echo "    - " . $detail->produk->nama_produk . " x " . $detail->qty . " @ Rp " . number_format($detail->harga, 0, ',', '.') . "\n";
        }
    }
} else {
    echo "No penjualan records found. Creating test penjualan...\n";
    
    // Create a test penjualan to check journal creation
    $pelanggan = \App\Models\Pelanggan::where('user_id', 1)->first();
    $produk = \App\Models\Produk::where('user_id', 1)->first();
    
    if ($pelanggan && $produk) {
        echo "Creating test penjualan...\n";
        
        $testPenjualan = \App\Models\Penjualan::create([
            'tanggal' => now()->format('Y-m-d'),
            'pelanggan_id' => $pelanggan->id,
            'user_id' => 1,
            'subtotal' => 500000,
            'total_hpp' => 268600,
            'total' => 555000,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "Test penjualan created with ID: " . $testPenjualan->id . "\n";
        
        // Check if JournalService creates HPP entries
        echo "Testing JournalService for HPP creation...\n";
        
        try {
            $journalService = new \App\Services\JournalService();
            
            // Simulate the journal creation process
            $lines = [
                ['code' => '111', 'debit' => 555000, 'credit' => 0, 'memo' => 'Penjualan Test'],
                ['code' => '41', 'debit' => 0, 'credit' => 500000, 'memo' => 'Penjualan Test'],
                ['code' => '51', 'debit' => 268600, 'credit' => 0, 'memo' => 'HPP Penjualan Test'],
                ['code' => '1171', 'debit' => 0, 'credit' => 268600, 'memo' => 'HPP Penjualan Test'],
            ];
            
            $journal = $journalService->post(
                now()->format('Y-m-d'),
                'penjualan',
                $testPenjualan->id,
                'Test Penjualan dengan HPP',
                $lines,
                1
            );
            
            echo "Journal created successfully with ID: " . $journal->id . "\n";
            
            // Check the created journal entries
            $jurnalEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $testPenjualan->id)->get();
            echo "Journal entries created: " . $jurnalEntries->count() . "\n";
            
            foreach ($jurnalEntries as $jurnal) {
                echo "  - " . $jurnal->coa->nama_akun . " | ";
                echo ($jurnal->debit > 0 ? "Debit: Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit: Rp " . number_format($jurnal->kredit, 0, ',', '.'));
                echo "\n";
            }
            
        } catch (\Exception $e) {
            echo "Error creating journal: " . $e->getMessage() . "\n";
        }
        
        // Clean up test data
        \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $testPenjualan->id)->delete();
        $testPenjualan->delete();
        echo "Test data cleaned up\n";
        
    } else {
        echo "No pelanggan or produk found for testing\n";
    }
}

echo "\nPenjualan Service check completed!\n";
