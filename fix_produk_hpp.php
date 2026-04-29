<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing Product HPP Data...\n\n";

// Find Jasuke product
$jasuke = \App\Models\Produk::where('nama_produk', 'Jasuke')->first();

if ($jasuke) {
    echo "Found Jasuke product:\n";
    echo "Current HPP: " . number_format($jasuke->hpp ?? 0, 0, ',', '.') . "\n";
    echo "Current Harga Pokok: " . number_format($jasuke->harga_pokok ?? 0, 0, ',', '.') . "\n";
    echo "Current Harga BOM: " . number_format($jasuke->harga_bom ?? 0, 0, ',', '.') . "\n";
    
    // Update HPP to use harga_bom value
    if ($jasuke->harga_bom > 0) {
        $jasuke->hpp = $jasuke->harga_bom;
        $jasuke->save();
        
        echo "\nUpdated HPP to: " . number_format($jasuke->hpp, 0, ',', '.') . "\n";
    } else {
        echo "No harga_bom value to use as HPP\n";
    }
} else {
    echo "Jasuke product not found\n";
}

echo "\n=== TESTING HPP JOURNAL CREATION ===\n";

// Get the sale
$sale = \App\Models\Penjualan::find(4);
if ($sale) {
    echo "Testing HPP journal for Sale ID: {$sale->id}\n";
    
    // Calculate expected HPP
    if ($sale->details && $sale->details->count() > 0) {
        foreach ($sale->details as $detail) {
            $produk = $detail->produk;
            if ($produk) {
                $hppPerUnit = (float)($produk->hpp ?? $produk->harga_pokok ?? $produk->harga_bom ?? 0);
                $totalHPP = round($hppPerUnit * $detail->jumlah);
                
                echo "Product: {$produk->nama_produk}\n";
                echo "Qty: {$detail->jumlah}\n";
                echo "HPP per unit: " . number_format($hppPerUnit, 0, ',', '.') . "\n";
                echo "Expected Total HPP: " . number_format($totalHPP, 0, ',', '.') . "\n";
            }
        }
    }
    
    // Create journal entries
    try {
        \App\Services\JournalService::createJournalFromPenjualan($sale);
        echo "\nHPP journal creation attempted successfully\n";
        
        // Check if HPP journals were created
        $hppJournals = \Illuminate\Support\Facades\DB::table('jurnal_umum')
            ->leftJoin('coas', 'coas.id', '=', 'jurnal_umum.coa_id')
            ->where('jurnal_umum.referensi', 'sale#' . $sale->id)
            ->where(function($q) {
                $q->where('coas.nama_akun', 'like', '%HPP%')
                  ->orWhere('coas.nama_akun', 'like', '%Barang Jadi%');
            })
            ->select('jurnal_umum.*', 'coas.nama_akun', 'coas.kode_akun')
            ->get();
        
        echo "HPP Journal Entries After Creation: {$hppJournals->count()}\n";
        foreach ($hppJournals as $journal) {
            echo "  {$journal->kode_akun} - {$journal->nama_akun}\n";
            echo "    Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
            echo "    Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
            echo "    Keterangan: {$journal->keterangan}\n";
        }
        
        // Show all journal entries for this sale
        echo "\nAll Journal Entries for Sale #{$sale->id}:\n";
        $allJournals = \Illuminate\Support\Facades\DB::table('jurnal_umum')
            ->leftJoin('coas', 'coas.id', '=', 'jurnal_umum.coa_id')
            ->where('jurnal_umum.referensi', 'sale#' . $sale->id)
            ->select('jurnal_umum.*', 'coas.nama_akun', 'coas.kode_akun')
            ->orderBy('jurnal_umum.id')
            ->get();
        
        foreach ($allJournals as $journal) {
            echo "  {$journal->kode_akun} - {$journal->nama_akun}\n";
            echo "    Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
            echo "    Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
            echo "    Keterangan: {$journal->keterangan}\n";
            echo "\n";
        }
        
    } catch (Exception $e) {
        echo "Error creating HPP journal: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "Sale ID 4 not found\n";
}

echo "\nProduct HPP fix completed!\n";
