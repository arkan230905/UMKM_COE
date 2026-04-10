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
    echo "=== CREATING TEST BAHAN PENDUKUNG PURCHASE ===\n\n";
    
    // Get vendor for bahan pendukung
    $vendor = Vendor::where('kategori', 'like', '%pendukung%')->first();
    if (!$vendor) {
        // Create a test vendor for bahan pendukung
        $vendor = Vendor::create([
            'nama_vendor' => 'Supplier Bahan Pendukung',
            'kategori' => 'Bahan Pendukung',
            'alamat' => 'Test Address',
            'no_telp' => '08123456789'
        ]);
        echo "Created test vendor: {$vendor->nama_vendor}\n";
    } else {
        echo "Using existing vendor: {$vendor->nama_vendor}\n";
    }
    
    // Get kas/bank account
    $kasBank = Coa::where('kode_akun', '112')->first(); // Kas
    if (!$kasBank) {
        echo "❌ Kas account (112) not found\n";
        exit;
    }
    
    // Create purchase
    $pembelian = Pembelian::create([
        'nomor_pembelian' => 'PB-TEST-' . date('YmdHis'),
        'tanggal' => date('Y-m-d'),
        'vendor_id' => $vendor->id,
        'subtotal' => 0,
        'ppn_persen' => 11,
        'ppn_nominal' => 0,
        'total_harga' => 0,
        'payment_method' => 'cash',
        'bank_id' => $kasBank->id,
        'keterangan' => 'Test purchase for bahan pendukung journal'
    ]);
    
    echo "Created purchase: {$pembelian->nomor_pembelian}\n";
    
    // Get bahan pendukung items
    $bahanPendukungs = BahanPendukung::whereNotNull('coa_persediaan_id')->take(2)->get();
    
    if ($bahanPendukungs->isEmpty()) {
        echo "❌ No bahan pendukung with COA mapping found\n";
        exit;
    }
    
    $subtotal = 0;
    
    foreach ($bahanPendukungs as $index => $bp) {
        $jumlah = ($index + 1) * 5; // 5, 10, etc.
        $harga = 10000 + ($index * 5000); // 10000, 15000, etc.
        $total = $jumlah * $harga;
        $subtotal += $total;
        
        PembelianDetail::create([
            'pembelian_id' => $pembelian->id,
            'bahan_pendukung_id' => $bp->id,
            'jumlah' => $jumlah,
            'harga_satuan' => $harga,
            'total_harga' => $total
        ]);
        
        echo "Added detail: {$bp->nama_bahan} - {$jumlah} x Rp " . number_format($harga) . " = Rp " . number_format($total) . "\n";
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
    
    // Manually create journal to test
    $journalService = new PembelianJournalService();
    $journal = $journalService->createJournalFromPembelian($pembelian);
    
    if ($journal) {
        echo "\n✅ Journal created successfully!\n";
        echo "Journal ID: {$journal->id}\n";
        echo "Journal Number: {$journal->journal_number}\n";
        
        // Show journal lines
        echo "\nJournal Lines:\n";
        foreach ($journal->lines as $line) {
            $amount = number_format($line->debit ?: $line->credit);
            $type = $line->debit ? 'Debit' : 'Credit';
            $indent = $line->credit ? '    ' : ''; // Indent credit lines
            echo "{$indent}{$line->coa->nama_akun} ({$line->coa->kode_akun}) - {$type}: Rp {$amount}\n";
        }
    } else {
        echo "\n❌ Failed to create journal\n";
    }
    
    echo "\nTest purchase ID: {$pembelian->id}\n";
    echo "You can view this in the journal report.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}