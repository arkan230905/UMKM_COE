<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Purchase Journal Format (User Requirements)...\n\n";

// Create a test pembelian with the format user wants
echo "📋 Creating Test Pembelian with User Format:\n";
echo "   - Pers. Bahan Baku Jagung: Rp 500.000\n";
echo "   - PPN 11%: Rp 55.000\n";
echo "   - Kas: Rp 555.000\n\n";

// Check if we have the right COA for Jagung
$coaJagung = \App\Models\Coa::where('nama_akun', 'like', '%Jagung%')->first();
if ($coaJagung) {
    echo "✅ Found Jagung COA: {$coaJagung->kode_akun} - {$coaJagung->nama_akun}\n";
} else {
    echo "⚠️  No Jagung COA found, using Pers. Bahan Baku (114)\n";
    $coaJagung = \App\Models\Coa::where('kode_akun', '114')->first();
}

// Check PPN Masukan COA
$ppnCoa = \App\Models\Coa::where('kode_akun', '127')->first();
if ($ppnCoa) {
    echo "✅ Found PPN Masukan COA: {$ppnCoa->kode_akun} - {$ppnCoa->nama_akun}\n";
} else {
    echo "❌ PPN Masukan COA not found\n";
    exit;
}

// Check Kas COA
$kasCoa = \App\Models\Coa::where('kode_akun', '112')->first();
if ($kasCoa) {
    echo "✅ Found Kas COA: {$kasCoa->kode_akun} - {$kasCoa->nama_akun}\n";
} else {
    echo "❌ Kas COA not found\n";
    exit;
}

// Simulate the journal entries that should be created
echo "\n📊 Expected Journal Entries (User Format):\n";

$hargaBahan = 500000;
$ppnNominal = 55000; // 11% of 500000
$totalPembayaran = 555000;

echo "   DEBIT {$coaJagung->kode_akun} {$coaJagung->nama_akun}: Rp " . number_format($hargaBahan, 0, ',', '.') . "\n";
echo "   DEBIT {$ppnCoa->kode_akun} {$ppnCoa->nama_akun}: Rp " . number_format($ppnNominal, 0, ',', '.') . "\n";
echo "   CREDIT {$kasCoa->kode_akun} {$kasCoa->nama_akun}: Rp " . number_format($totalPembayaran, 0, ',', '.') . "\n";

// Check balance
$totalDebit = $hargaBahan + $ppnNominal;
$totalCredit = $totalPembayaran;

echo "\n📊 Balance Check:\n";
echo "   Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "   Total Credit: Rp " . number_format($totalCredit, 0, ',', '.') . "\n";
echo "   Balanced: " . ($totalDebit == $totalCredit ? '✅ YES' : '❌ NO') . "\n";

// Test with actual PembelianJournalService
echo "\n🔧 Testing PembelianJournalService with Real Data...\n";

// Get existing pembelian to test
$pembelian = \App\Models\Pembelian::latest()->first();
if ($pembelian) {
    echo "Testing with existing pembelian: {$pembelian->nomor_pembelian}\n";
    
    try {
        $service = new \App\Services\PembelianJournalService();
        
        // Delete existing journals
        $service->deleteExistingJournal($pembelian->id);
        
        // Create new journal
        $result = $service->createJournalFromPembelian($pembelian);
        
        if ($result) {
            echo "✅ Journal created successfully!\n";
            
            // Show the actual journal entries
            $journals = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
                ->where('referensi', $pembelian->nomor_pembelian)
                ->orderBy('id')
                ->get();
            
            echo "\n📊 Actual Journal Entries Created:\n";
            foreach ($journals as $journal) {
                $coa = \App\Models\Coa::find($journal->coa_id);
                $debitCredit = $journal->debit > 0 ? 'DEBIT' : 'CREDIT';
                $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
                
                echo "   {$debitCredit} {$coa->kode_akun} {$coa->nama_akun}: Rp " . number_format($amount, 0, ',', '.') . "\n";
            }
            
            // Check if format matches user requirements
            echo "\n🎯 Format Comparison:\n";
            echo "   User wants: Pers. Bahan Baku Jagung, PPN Masukan, Kas\n";
            echo "   System creates: ";
            
            $hasBahanBaku = false;
            $hasPPN = false;
            $hasKas = false;
            
            foreach ($journals as $journal) {
                $coa = \App\Models\Coa::find($journal->coa_id);
                if ($journal->debit > 0) {
                    if (stripos($coa->nama_akun, 'Bahan') !== false || stripos($coa->nama_akun, 'Pers.') !== false) {
                        $hasBahanBaku = true;
                    }
                    if (stripos($coa->nama_akun, 'PPN') !== false) {
                        $hasPPN = true;
                    }
                }
                if ($journal->kredit > 0) {
                    if (stripos($coa->nama_akun, 'Kas') !== false) {
                        $hasKas = true;
                    }
                }
            }
            
            echo ($hasBahanBaku ? '✅' : '❌') . " Persediaan, ";
            echo ($hasPPN ? '✅' : '❌') . " PPN Masukan, ";
            echo ($hasKas ? '✅' : '❌') . " Kas\n";
            
        } else {
            echo "❌ Journal creation failed\n";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n🎯 Test completed!\n";
