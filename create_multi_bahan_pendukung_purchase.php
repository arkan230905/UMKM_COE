<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Vendor;
use App\Models\BahanPendukung;
use App\Models\Coa;
use App\Services\PembelianJournalService;

try {
    echo "=== CREATING MULTI BAHAN PENDUKUNG PURCHASE ===\n\n";
    
    // Get vendor for bahan pendukung
    $vendor = Vendor::where('kategori', 'like', '%pendukung%')->first();
    if (!$vendor) {
        $vendor = Vendor::first(); // Use any vendor
    }
    
    echo "Using vendor: {$vendor->nama_vendor}\n";
    
    // Create purchase with credit payment (Hutang Usaha)
    $pembelian = Pembelian::create([
        'nomor_pembelian' => 'PB-TEST-BP-' . date('YmdHis'),
        'tanggal' => date('Y-m-d'),
        'vendor_id' => $vendor->id,
        'subtotal' => 0,
        'ppn_persen' => 11,
        'ppn_nominal' => 0,
        'total_harga' => 0,
        'payment_method' => 'credit', // This will create Hutang Usaha
        'bank_id' => null,
        'keterangan' => 'Test multi bahan pendukung purchase'
    ]);
    
    echo "Created purchase: {$pembelian->nomor_pembelian}\n";
    
    // Get multiple bahan pendukung items
    $bahanPendukungs = BahanPendukung::whereNotNull('coa_persediaan_id')->take(3)->get();
    
    if ($bahanPendukungs->count() < 2) {
        echo "❌ Need at least 2 bahan pendukung items with COA mapping\n";
        exit;
    }
    
    $subtotal = 0;
    
    // Add Minyak Goreng
    $minyakGoreng = $bahanPendukungs->where('nama_bahan', 'like', '%minyak%')->first();
    if ($minyakGoreng) {
        $jumlah = 10;
        $harga = 15000;
        $total = $jumlah * $harga;
        $subtotal += $total;
        
        PembelianDetail::create([
            'pembelian_id' => $pembelian->id,
            'bahan_pendukung_id' => $minyakGoreng->id,
            'jumlah' => $jumlah,
            'harga_satuan' => $harga,
            'total_harga' => $total
        ]);
        
        echo "Added: {$minyakGoreng->nama_bahan} - {$jumlah} x Rp " . number_format($harga) . " = Rp " . number_format($total) . "\n";
    }
    
    // Add another bahan pendukung
    $otherBP = $bahanPendukungs->where('id', '!=', $minyakGoreng->id ?? 0)->first();
    if ($otherBP) {
        $jumlah = 5;
        $harga = 8000;
        $total = $jumlah * $harga;
        $subtotal += $total;
        
        PembelianDetail::create([
            'pembelian_id' => $pembelian->id,
            'bahan_pendukung_id' => $otherBP->id,
            'jumlah' => $jumlah,
            'harga_satuan' => $harga,
            'total_harga' => $total
        ]);
        
        echo "Added: {$otherBP->nama_bahan} - {$jumlah} x Rp " . number_format($harga) . " = Rp " . number_format($total) . "\n";
    }
    
    // Calculate PPN and total
    $ppnNominal = $subtotal * 0.11;
    $totalHarga = $subtotal + $ppnNominal;
    
    // Update purchase totals
    $pembelian->update([
        'subtotal' => $subtotal,
        'ppn_nominal' => $ppnNominal,
        'total_harga' => $totalHarga
    ]);
    
    echo "\nPurchase totals:\n";
    echo "Subtotal: Rp " . number_format($subtotal) . "\n";
    echo "PPN (11%): Rp " . number_format($ppnNominal) . "\n";
    echo "Total: Rp " . number_format($totalHarga) . "\n";
    
    // Create journal
    $journalService = new PembelianJournalService();
    $journal = $journalService->createJournalFromPembelian($pembelian);
    
    if ($journal) {
        echo "\n✅ Journal created successfully!\n";
        echo "Journal ID: {$journal->id}\n";
        echo "Memo: {$journal->memo}\n\n";
        
        echo "Journal Format (as requested):\n";
        foreach ($journal->lines as $line) {
            $amount = number_format($line->debit ?: $line->credit);
            
            // Display in the format you requested
            if ($line->debit > 0) {
                echo "{$line->coa->nama_akun}\n";
            } else {
                echo "                        {$line->coa->nama_akun}\n";
            }
        }
        
        echo "\nDetailed Journal Lines:\n";
        foreach ($journal->lines as $line) {
            $amount = number_format($line->debit ?: $line->credit);
            $type = $line->debit ? 'Debit' : 'Credit';
            
            if ($line->credit > 0) {
                echo "    {$line->coa->nama_akun} - {$type}: Rp {$amount}\n";
            } else {
                echo "{$line->coa->nama_akun} - {$type}: Rp {$amount}\n";
            }
        }
        
    } else {
        echo "\n❌ Failed to create journal\n";
    }
    
    echo "\nTest purchase ID: {$pembelian->id}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}