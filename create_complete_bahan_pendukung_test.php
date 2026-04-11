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
    echo "=== CREATING COMPLETE BAHAN PENDUKUNG JOURNAL TEST ===\n\n";
    
    // Get vendor
    $vendor = Vendor::first();
    echo "Using vendor: {$vendor->nama_vendor}\n";
    
    // Create purchase with credit payment (Hutang Usaha)
    $pembelian = Pembelian::create([
        'nomor_pembelian' => 'PB-BP-FINAL-' . date('YmdHis'),
        'tanggal' => date('Y-m-d'),
        'vendor_id' => $vendor->id,
        'subtotal' => 0,
        'ppn_persen' => 11,
        'ppn_nominal' => 0,
        'total_harga' => 0,
        'payment_method' => 'credit', // Hutang Usaha
        'bank_id' => null,
        'keterangan' => 'Final test for bahan pendukung journal'
    ]);
    
    echo "Created purchase: {$pembelian->nomor_pembelian}\n\n";
    
    $subtotal = 0;
    
    // Add Minyak Goreng
    $minyakGoreng = BahanPendukung::where('nama_bahan', 'like', '%minyak%')->first();
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
        
        echo "✅ Added: {$minyakGoreng->nama_bahan} - {$jumlah} x Rp " . number_format($harga) . " = Rp " . number_format($total) . "\n";
    }
    
    // Add Bawang Putih Bubuk (or similar)
    $bawangPutih = BahanPendukung::where('nama_bahan', 'like', '%tepung%')->first();
    if ($bawangPutih) {
        $jumlah = 5;
        $harga = 12000;
        $total = $jumlah * $harga;
        $subtotal += $total;
        
        PembelianDetail::create([
            'pembelian_id' => $pembelian->id,
            'bahan_pendukung_id' => $bawangPutih->id,
            'jumlah' => $jumlah,
            'harga_satuan' => $harga,
            'total_harga' => $total
        ]);
        
        echo "✅ Added: {$bawangPutih->nama_bahan} - {$jumlah} x Rp " . number_format($harga) . " = Rp " . number_format($total) . "\n";
    }
    
    // Calculate totals
    $ppnNominal = $subtotal * 0.11;
    $totalHarga = $subtotal + $ppnNominal;
    
    // Update purchase
    $pembelian->update([
        'subtotal' => $subtotal,
        'ppn_nominal' => $ppnNominal,
        'total_harga' => $totalHarga
    ]);
    
    echo "\n📊 Purchase Summary:\n";
    echo "Subtotal: Rp " . number_format($subtotal) . "\n";
    echo "PPN (11%): Rp " . number_format($ppnNominal) . "\n";
    echo "Total: Rp " . number_format($totalHarga) . "\n";
    echo "Payment: Credit (Hutang Usaha)\n\n";
    
    // Create journal
    $journalService = new PembelianJournalService();
    $journal = $journalService->createJournalFromPembelian($pembelian);
    
    if ($journal) {
        echo "✅ JOURNAL CREATED SUCCESSFULLY!\n";
        echo "Journal ID: {$journal->id}\n";
        echo "Date: {$journal->tanggal}\n";
        echo "Reference: {$journal->ref_type} #{$journal->ref_id}\n";
        echo "Memo: {$journal->memo}\n\n";
        
        echo "📋 JOURNAL FORMAT (AS REQUESTED):\n";
        echo "=====================================\n";
        
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($journal->lines as $line) {
            if ($line->debit > 0) {
                echo "Pers. Bahan Pendukung {$line->coa->nama_akun}\n";
                $totalDebit += $line->debit;
            } elseif ($line->credit > 0 && strpos($line->coa->nama_akun, 'PPN') !== false) {
                echo "PPN Masukan\n";
                $totalDebit += $line->debit;
            }
        }
        
        // Add PPN line if exists
        $ppnLine = $journal->lines->where('coa.nama_akun', 'like', '%PPN%')->first();
        if ($ppnLine && $ppnLine->debit > 0) {
            echo "PPN Masukan\n";
        }
        
        // Add credit line
        $creditLine = $journal->lines->where('credit', '>', 0)->first();
        if ($creditLine) {
            echo "                        {$creditLine->coa->nama_akun}\n";
        }
        
        echo "=====================================\n\n";
        
        echo "📝 DETAILED JOURNAL LINES:\n";
        foreach ($journal->lines as $line) {
            $amount = number_format($line->debit ?: $line->credit);
            $type = $line->debit > 0 ? 'Debit' : 'Credit';
            $indent = $line->credit > 0 ? '    ' : '';
            
            echo "{$indent}{$line->coa->nama_akun} ({$line->coa->kode_akun}) - {$type}: Rp {$amount}\n";
        }
        
        $totalDebit = $journal->lines->sum('debit');
        $totalCredit = $journal->lines->sum('credit');
        
        echo "\n💰 TOTALS:\n";
        echo "Total Debit: Rp " . number_format($totalDebit) . "\n";
        echo "Total Credit: Rp " . number_format($totalCredit) . "\n";
        echo "Balance: " . ($totalDebit == $totalCredit ? "✅ BALANCED" : "❌ NOT BALANCED") . "\n";
        
    } else {
        echo "❌ Failed to create journal\n";
    }
    
    echo "\n🎯 Test completed! Purchase ID: {$pembelian->id}\n";
    echo "You can now view this journal in the system.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}