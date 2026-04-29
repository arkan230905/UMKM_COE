<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Purchase Journal with New PPN COA...\n\n";

// Get the latest pembelian
$pembelian = \App\Models\Pembelian::latest()->first();
if (!$pembelian) {
    echo "❌ No pembelian found\n";
    exit;
}

echo "📋 Pembelian Details:\n";
echo "   ID: {$pembelian->id}\n";
echo "   Nomor: {$pembelian->nomor_pembelian}\n";
echo "   Subtotal: Rp " . number_format($pembelian->subtotal, 0, ',', '.') . "\n";
echo "   PPN %: " . ($pembelian->ppn_persen ?? 0) . "%\n";
echo "   PPN Nominal: Rp " . number_format($pembelian->ppn_nominal ?? 0, 0, ',', '.') . "\n";
echo "   Total Harga: Rp " . number_format($pembelian->total_harga, 0, ',', '.') . "\n";
echo "   Payment Method: {$pembelian->payment_method}\n\n";

// Check if PPN COA exists
$ppnCoa = \App\Models\Coa::where('kode_akun', '127')->first();
if ($ppnCoa) {
    echo "✅ PPN Masukan COA found: {$ppnCoa->kode_akun} - {$ppnCoa->nama_akun}\n";
} else {
    echo "❌ PPN Masukan COA not found\n";
    exit;
}

// Test creating journal entries
echo "\n🔧 Testing PembelianJournalService with new PPN COA...\n";
try {
    $service = new \App\Services\PembelianJournalService();
    
    // Delete existing journals first
    $service->deleteExistingJournal($pembelian->id);
    
    $result = $service->createJournalFromPembelian($pembelian);
    
    if ($result) {
        echo "✅ Journal created successfully!\n";
    } else {
        echo "❌ Journal creation returned null\n";
    }
} catch (Exception $e) {
    echo "❌ Error creating journal: " . $e->getMessage() . "\n";
}

// Check journal entries after creation
echo "\n📊 Journal Entries After Creation:\n";
$journals = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
    ->where('referensi', $pembelian->nomor_pembelian)
    ->orderBy('id')
    ->get();

echo "Total entries: " . $journals->count() . "\n";
foreach ($journals as $journal) {
    $coa = \App\Models\Coa::find($journal->coa_id);
    $debitCredit = $journal->debit > 0 ? 'DEBIT' : 'CREDIT';
    $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
    
    echo "   {$debitCredit} {$coa->kode_akun} {$coa->nama_akun}: Rp " . number_format($amount, 0, ',', '.') . "\n";
}

// Check balance
$totalDebit = $journals->sum('debit');
$totalCredit = $journals->sum('kredit');

echo "\n📊 Balance Check:\n";
echo "   Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "   Total Credit: Rp " . number_format($totalCredit, 0, ',', '.') . "\n";
echo "   Balanced: " . (abs($totalDebit - $totalCredit) < 0.01 ? '✅ YES' : '❌ NO') . "\n";

// Show expected format
echo "\n🎯 Expected Format (User Requirements):\n";
echo "   Pers. Bahan Baku Jagung     Rp 500.000 (example)\n";
echo "   PPN Masukan                Rp 55.000 (11% of 500.000)\n";
echo "   Kas                        Rp 555.000 (total payment)\n";

echo "\n🎯 Test completed!\n";
