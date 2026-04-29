<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Pembelian Journal API...\n\n";

// Get the latest pembelian
$pembelian = \App\Models\Pembelian::latest()->first();
if (!$pembelian) {
    echo "❌ No pembelian found\n";
    exit;
}

echo "📋 Testing with Pembelian:\n";
echo "   ID: {$pembelian->id}\n";
echo "   Nomor: {$pembelian->nomor_pembelian}\n";
echo "   Total: Rp " . number_format($pembelian->total_harga, 0, ',', '.') . "\n\n";

// Test the API endpoint logic directly
echo "🔧 Testing API Logic:\n";

// Get journal entries for this purchase
$journalEntries = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
    ->where('referensi', $pembelian->nomor_pembelian)
    ->with('coa')
    ->orderBy('id', 'asc')
    ->get();

echo "   Found {$journalEntries->count()} journal entries\n\n";

if ($journalEntries->count() > 0) {
    echo "📊 Journal Entries:\n";
    foreach ($journalEntries as $entry) {
        $debitCredit = $entry->debit > 0 ? 'DEBIT' : 'CREDIT';
        $amount = $entry->debit > 0 ? $entry->debit : $entry->kredit;
        
        echo "   {$debitCredit} {$entry->coa->kode_akun} {$entry->coa->nama_akun}: Rp " . number_format($amount, 0, ',', '.') . "\n";
        echo "     Tanggal: {$entry->tanggal}\n";
        echo "     Keterangan: {$entry->keterangan}\n\n";
    }
    
    // Calculate totals
    $totalDebit = $journalEntries->sum('debit');
    $totalCredit = $journalEntries->sum('kredit');
    
    echo "📈 Summary:\n";
    echo "   Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
    echo "   Total Credit: Rp " . number_format($totalCredit, 0, ',', '.') . "\n";
    echo "   Balanced: " . ($totalDebit == $totalCredit ? '✅ YES' : '❌ NO') . "\n";
} else {
    echo "⚠️  No journal entries found for this pembelian\n";
}

echo "\n🎯 API Test completed!\n";
