<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

echo "=== Cleanup Duplicate Sales ===\n";

// Cari penjualan duplikat berdasarkan nomor penjualan yang sama
$duplicates = DB::select("
    SELECT nomor_penjualan, COUNT(*) as count 
    FROM penjualans 
    WHERE DATE(tanggal) = '2026-04-08' 
    GROUP BY nomor_penjualan 
    HAVING COUNT(*) > 1
    ORDER BY nomor_penjualan
");

echo "Found " . count($duplicates) . " duplicate sale numbers:\n";

foreach ($duplicates as $dup) {
    echo "- {$dup->nomor_penjualan}: {$dup->count} records\n";
    
    // Ambil semua penjualan dengan nomor yang sama
    $sales = Penjualan::where('nomor_penjualan', $dup->nomor_penjualan)
        ->orderBy('id')
        ->get();
    
    // Simpan yang pertama, hapus sisanya
    $keepFirst = $sales->first();
    $toDelete = $sales->skip(1);
    
    echo "  Keeping sale ID: {$keepFirst->id}\n";
    
    foreach ($toDelete as $sale) {
        echo "  Deleting sale ID: {$sale->id}\n";
        
        DB::transaction(function () use ($sale) {
            // Hapus journal entries
            $journals = JournalEntry::where('ref_type', 'sale')
                ->where('ref_id', $sale->id)
                ->get();
            
            foreach ($journals as $journal) {
                JournalLine::where('journal_entry_id', $journal->id)->delete();
                $journal->delete();
            }
            
            // Hapus penjualan details
            PenjualanDetail::where('penjualan_id', $sale->id)->delete();
            
            // Hapus stock movements
            StockMovement::where('ref_type', 'sale')
                ->where('ref_id', $sale->id)
                ->delete();
            
            // Hapus penjualan
            $sale->delete();
        });
    }
}

echo "\n=== Cleanup completed ===\n";