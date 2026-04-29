<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Journal Format Fix...\n\n";

// Test API response format
$pembelian = \App\Models\Pembelian::latest()->first();
if (!$pembelian) {
    echo "❌ No pembelian found\n";
    exit;
}

echo "📋 Testing with Pembelian: {$pembelian->nomor_pembelian}\n\n";

// Simulate API response
$journalEntries = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
    ->where('referensi', $pembelian->nomor_pembelian)
    ->with('coa')
    ->orderBy('id', 'asc')
    ->get();

echo "📊 Expected Format (like detail page):\n";
foreach ($journalEntries as $entry) {
    $tanggal = $entry->tanggal ? \Carbon\Carbon::parse($entry->tanggal)->format('d/m/Y') : '-';
    $coaBadge = $entry->coa ? 
        "<span class=\"badge bg-primary\">{$entry->coa->nama_akun}</span><br><small class=\"text-muted\">{$entry->coa->kode_akun}</small>" : 
        '<span class="badge bg-secondary">COA tidak ditemukan</span>';
    $keterangan = $entry->keterangan;
    $debit = $entry->debit > 0 ? 'Rp ' . number_format($entry->debit, 2, ',', '.') : '-';
    $kredit = $entry->kredit > 0 ? 'Rp ' . number_format($entry->kredit, 2, ',', '.') : '-';
    
    echo "🔵 {$tanggal}\n";
    echo "   {$coaBadge}\n";
    echo "   Keterangan: {$keterangan}\n";
    echo "   Debet: {$debit}\n";
    echo "   Kredit: {$kredit}\n\n";
}

// Calculate totals
$totalDebit = $journalEntries->sum('debit');
$totalCredit = $journalEntries->sum('kredit');

echo "💰 Totals:\n";
echo "   Total Debet: Rp " . number_format($totalDebit, 2, ',', '.') . "\n";
echo "   Total Kredit: Rp " . number_format($totalCredit, 2, ',', '.') . "\n";
echo "   Balanced: " . ($totalDebit == $totalCredit ? '✅ YES' : '❌ NO') . "\n";

echo "\n🎯 Format test completed!\n";
