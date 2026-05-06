<?php

/**
 * Test Script: Verify Penjualan Journal Entries with HPP
 * 
 * This script tests that penjualan journal entries are created correctly
 * including HPP (Harga Pokok Penjualan) entries.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST PENJUALAN JOURNAL ENTRIES WITH HPP ===" . PHP_EOL . PHP_EOL;

// Get a test user
$user = \App\Models\User::first();
if (!$user) {
    echo "❌ No user found. Please create a user first." . PHP_EOL;
    exit(1);
}

echo "✅ Using user: {$user->name} (ID: {$user->id})" . PHP_EOL . PHP_EOL;

// Set auth user
auth()->login($user);

// Get a product with HPP
$produk = \App\Models\Produk::where('user_id', $user->id)
    ->where('stok', '>', 0)
    ->first();

if (!$produk) {
    echo "❌ No product found with stock. Please create a product first." . PHP_EOL;
    exit(1);
}

echo "✅ Using product: {$produk->nama_produk}" . PHP_EOL;
echo "   - Stok: {$produk->stok}" . PHP_EOL;
echo "   - Harga Jual: Rp " . number_format($produk->harga_jual, 0, ',', '.') . PHP_EOL;

// Get HPP
$hpp = $produk->getActualHPP(now());
echo "   - HPP: Rp " . number_format($hpp, 0, ',', '.') . PHP_EOL . PHP_EOL;

// Create a test penjualan
echo "🔄 Creating test penjualan..." . PHP_EOL;

try {
    DB::beginTransaction();
    
    $penjualan = \App\Models\Penjualan::create([
        'user_id' => $user->id,
        'tanggal' => now(),
        'payment_method' => 'cash',
        'jumlah' => 2,
        'harga_satuan' => $produk->harga_jual,
        'diskon_nominal' => 0,
        'total' => $produk->harga_jual * 2,
    ]);
    
    // Create detail
    \App\Models\PenjualanDetail::create([
        'penjualan_id' => $penjualan->id,
        'produk_id' => $produk->id,
        'jumlah' => 2,
        'harga_satuan' => $produk->harga_jual,
        'diskon_persen' => 0,
        'diskon_nominal' => 0,
        'subtotal' => $produk->harga_jual * 2,
    ]);
    
    echo "✅ Penjualan created: {$penjualan->nomor_penjualan}" . PHP_EOL;
    echo "   - Total: Rp " . number_format($penjualan->total, 0, ',', '.') . PHP_EOL . PHP_EOL;
    
    // Check journal entries
    echo "🔍 Checking journal entries..." . PHP_EOL . PHP_EOL;
    
    $journals = \App\Models\JurnalUmum::where('tipe_referensi', 'sale')
        ->where('referensi', $penjualan->id)
        ->with('coa')
        ->orderBy('debit', 'desc')
        ->get();
    
    if ($journals->isEmpty()) {
        echo "❌ NO JOURNAL ENTRIES FOUND!" . PHP_EOL;
        echo "   This means the journal creation failed." . PHP_EOL;
        DB::rollBack();
        exit(1);
    }
    
    echo "✅ Found {$journals->count()} journal entries:" . PHP_EOL . PHP_EOL;
    
    $totalDebit = 0;
    $totalCredit = 0;
    $hasHPP = false;
    $hasPendapatan = false;
    $hasPersediaan = false;
    $hasKas = false;
    
    foreach ($journals as $journal) {
        $coaName = $journal->coa->nama_akun ?? 'Unknown';
        $coaCode = $journal->coa->kode_akun ?? 'Unknown';
        
        echo "📝 {$coaName} ({$coaCode})" . PHP_EOL;
        echo "   Debit : Rp " . number_format($journal->debit, 0, ',', '.') . PHP_EOL;
        echo "   Credit: Rp " . number_format($journal->kredit, 0, ',', '.') . PHP_EOL;
        echo "   Memo  : {$journal->keterangan}" . PHP_EOL . PHP_EOL;
        
        $totalDebit += $journal->debit;
        $totalCredit += $journal->kredit;
        
        // Check for required accounts
        if (stripos($coaName, 'hpp') !== false || stripos($coaName, 'harga pokok') !== false || $coaCode == '560') {
            $hasHPP = true;
        }
        if (stripos($coaName, 'pendapatan') !== false || $coaCode == '41') {
            $hasPendapatan = true;
        }
        if (stripos($coaName, 'persediaan') !== false || stripos($coaCode, '116') !== false) {
            $hasPersediaan = true;
        }
        if (stripos($coaName, 'kas') !== false || $coaCode == '112' || $coaCode == '111') {
            $hasKas = true;
        }
    }
    
    echo "=== SUMMARY ===" . PHP_EOL;
    echo "Total Debit : Rp " . number_format($totalDebit, 0, ',', '.') . PHP_EOL;
    echo "Total Credit: Rp " . number_format($totalCredit, 0, ',', '.') . PHP_EOL;
    echo "Balanced    : " . (abs($totalDebit - $totalCredit) < 0.01 ? "✅ YES" : "❌ NO") . PHP_EOL . PHP_EOL;
    
    echo "=== REQUIRED ACCOUNTS CHECK ===" . PHP_EOL;
    echo "Kas/Bank Account     : " . ($hasKas ? "✅ Found" : "❌ Missing") . PHP_EOL;
    echo "Pendapatan Account   : " . ($hasPendapatan ? "✅ Found" : "❌ Missing") . PHP_EOL;
    echo "HPP Account          : " . ($hasHPP ? "✅ Found" : "❌ Missing") . PHP_EOL;
    echo "Persediaan Account   : " . ($hasPersediaan ? "✅ Found" : "❌ Missing") . PHP_EOL . PHP_EOL;
    
    // Verify HPP calculation
    $expectedHPP = $hpp * 2; // 2 units
    $actualHPPEntry = $journals->where('coa.kode_akun', '560')->first();
    
    if ($actualHPPEntry) {
        echo "=== HPP VERIFICATION ===" . PHP_EOL;
        echo "Expected HPP: Rp " . number_format($expectedHPP, 0, ',', '.') . " (Rp " . number_format($hpp, 0, ',', '.') . " x 2)" . PHP_EOL;
        echo "Actual HPP  : Rp " . number_format($actualHPPEntry->debit, 0, ',', '.') . PHP_EOL;
        echo "Match       : " . (abs($expectedHPP - $actualHPPEntry->debit) < 0.01 ? "✅ YES" : "❌ NO") . PHP_EOL . PHP_EOL;
    }
    
    // Final verdict
    if ($hasKas && $hasPendapatan && $hasHPP && $hasPersediaan && abs($totalDebit - $totalCredit) < 0.01) {
        echo "🎉 SUCCESS! All journal entries are correct!" . PHP_EOL;
        echo "   ✅ Kas/Bank entry created" . PHP_EOL;
        echo "   ✅ Pendapatan entry created" . PHP_EOL;
        echo "   ✅ HPP entry created" . PHP_EOL;
        echo "   ✅ Persediaan entry created" . PHP_EOL;
        echo "   ✅ Journal is balanced" . PHP_EOL . PHP_EOL;
        
        echo "📊 JOURNAL STRUCTURE:" . PHP_EOL;
        echo "   Dr. Kas/Bank         Rp " . number_format($penjualan->total, 0, ',', '.') . PHP_EOL;
        echo "      Cr. Pendapatan                Rp " . number_format($penjualan->total, 0, ',', '.') . PHP_EOL;
        echo "   Dr. HPP              Rp " . number_format($expectedHPP, 0, ',', '.') . PHP_EOL;
        echo "      Cr. Persediaan                Rp " . number_format($expectedHPP, 0, ',', '.') . PHP_EOL;
    } else {
        echo "❌ FAILED! Some journal entries are missing or incorrect!" . PHP_EOL;
        if (!$hasKas) echo "   ❌ Kas/Bank entry missing" . PHP_EOL;
        if (!$hasPendapatan) echo "   ❌ Pendapatan entry missing" . PHP_EOL;
        if (!$hasHPP) echo "   ❌ HPP entry missing" . PHP_EOL;
        if (!$hasPersediaan) echo "   ❌ Persediaan entry missing" . PHP_EOL;
        if (abs($totalDebit - $totalCredit) >= 0.01) echo "   ❌ Journal not balanced" . PHP_EOL;
    }
    
    DB::rollBack();
    echo PHP_EOL . "✅ Test completed (transaction rolled back)" . PHP_EOL;
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}
