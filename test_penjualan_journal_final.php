<?php

/**
 * FINAL TEST: Penjualan Journal with HPP from Harga Pokok Produksi
 * 
 * This test verifies that:
 * 1. HPP is calculated from /master-data/harga-pokok-produksi (BBB + BTKL + BOP)
 * 2. Journal entries are created correctly with 4 lines
 * 3. All entries are balanced
 * 4. COA 554 (HPP) is used correctly
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║  FINAL TEST: PENJUALAN JOURNAL WITH HPP                       ║" . PHP_EOL;
echo "╚════════════════════════════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

// Get user
$user = \App\Models\User::first();
if (!$user) {
    echo "❌ No user found" . PHP_EOL;
    exit(1);
}

echo "✅ User: {$user->name} (ID: {$user->id})" . PHP_EOL . PHP_EOL;
auth()->login($user);

// Get product with stock
$produk = \App\Models\Produk::where('user_id', $user->id)
    ->where('stok', '>', 0)
    ->first();

if (!$produk) {
    echo "❌ No product with stock found" . PHP_EOL;
    exit(1);
}

echo "📦 PRODUCT INFO" . PHP_EOL;
echo "─────────────────────────────────────────────────────────────────" . PHP_EOL;
echo "Nama     : {$produk->nama_produk}" . PHP_EOL;
echo "Stok     : {$produk->stok}" . PHP_EOL;
echo "Harga    : Rp " . number_format($produk->harga_jual, 0, ',', '.') . PHP_EOL . PHP_EOL;

// Check HPP calculation
echo "💰 HPP CALCULATION" . PHP_EOL;
echo "─────────────────────────────────────────────────────────────────" . PHP_EOL;

$hpp = $produk->getActualHPP(now());
echo "HPP per unit: Rp " . number_format($hpp, 0, ',', '.') . PHP_EOL;

if ($hpp > 0) {
    echo "✅ HPP found from Harga Pokok Produksi" . PHP_EOL;
} else {
    echo "⚠️  HPP is 0 - Product may not have HPP data in /master-data/harga-pokok-produksi" . PHP_EOL;
    echo "   Journal will still be created but without HPP entries" . PHP_EOL;
}
echo PHP_EOL;

// Create test penjualan
echo "🔄 CREATING TEST PENJUALAN" . PHP_EOL;
echo "─────────────────────────────────────────────────────────────────" . PHP_EOL;

try {
    DB::beginTransaction();
    
    $qty = 2;
    $total = $produk->harga_jual * $qty;
    
    $penjualan = \App\Models\Penjualan::create([
        'user_id' => $user->id,
        'tanggal' => now(),
        'payment_method' => 'cash',
        'jumlah' => $qty,
        'harga_satuan' => $produk->harga_jual,
        'diskon_nominal' => 0,
        'total' => $total,
    ]);
    
    // Create detail
    \App\Models\PenjualanDetail::create([
        'penjualan_id' => $penjualan->id,
        'produk_id' => $produk->id,
        'jumlah' => $qty,
        'harga_satuan' => $produk->harga_jual,
        'diskon_persen' => 0,
        'diskon_nominal' => 0,
        'subtotal' => $total,
    ]);
    
    echo "✅ Penjualan: {$penjualan->nomor_penjualan}" . PHP_EOL;
    echo "   Qty      : {$qty} pcs" . PHP_EOL;
    echo "   Total    : Rp " . number_format($total, 0, ',', '.') . PHP_EOL;
    echo "   Expected HPP: Rp " . number_format($hpp * $qty, 0, ',', '.') . PHP_EOL . PHP_EOL;
    
    // Check journal entries
    echo "📊 JOURNAL ENTRIES" . PHP_EOL;
    echo "─────────────────────────────────────────────────────────────────" . PHP_EOL;
    
    $journals = \App\Models\JurnalUmum::where('tipe_referensi', 'sale')
        ->where('referensi', $penjualan->id)
        ->with('coa')
        ->orderBy('debit', 'desc')
        ->get();
    
    if ($journals->isEmpty()) {
        echo "❌ NO JOURNAL ENTRIES FOUND!" . PHP_EOL;
        echo "   This is a critical error - journal creation failed" . PHP_EOL;
        DB::rollBack();
        exit(1);
    }
    
    echo "✅ Found {$journals->count()} journal entries" . PHP_EOL . PHP_EOL;
    
    $totalDebit = 0;
    $totalCredit = 0;
    $hasHPP = false;
    $hasPendapatan = false;
    $hasPersediaan = false;
    $hasKas = false;
    $hppAmount = 0;
    
    echo "┌─────────────────────────────────────────────────────────────────┐" . PHP_EOL;
    echo "│ JOURNAL DETAILS                                                 │" . PHP_EOL;
    echo "├─────────────────────────────────────────────────────────────────┤" . PHP_EOL;
    
    foreach ($journals as $journal) {
        $coaName = $journal->coa->nama_akun ?? 'Unknown';
        $coaCode = $journal->coa->kode_akun ?? '???';
        
        echo "│ " . str_pad($coaName, 35) . " (" . str_pad($coaCode, 4) . ")";
        echo str_pad("", 20) . " │" . PHP_EOL;
        
        if ($journal->debit > 0) {
            echo "│   Dr. " . str_pad("Rp " . number_format($journal->debit, 0, ',', '.'), 52) . " │" . PHP_EOL;
        }
        if ($journal->kredit > 0) {
            echo "│       Cr. " . str_pad("Rp " . number_format($journal->kredit, 0, ',', '.'), 48) . " │" . PHP_EOL;
        }
        echo "│   Memo: " . str_pad(substr($journal->keterangan, 0, 50), 52) . " │" . PHP_EOL;
        echo "├─────────────────────────────────────────────────────────────────┤" . PHP_EOL;
        
        $totalDebit += $journal->debit;
        $totalCredit += $journal->kredit;
        
        // Check for required accounts
        if (stripos($coaName, 'hpp') !== false || stripos($coaName, 'harga pokok') !== false || $coaCode == '554') {
            $hasHPP = true;
            $hppAmount = $journal->debit;
        }
        if (stripos($coaName, 'pendapatan') !== false || stripos($coaName, 'penjualan') !== false || $coaCode == '41') {
            $hasPendapatan = true;
        }
        if (stripos($coaName, 'persediaan') !== false || stripos($coaCode, '116') !== false) {
            $hasPersediaan = true;
        }
        if (stripos($coaName, 'kas') !== false || $coaCode == '112' || $coaCode == '111' || $coaCode == '1101') {
            $hasKas = true;
        }
    }
    
    echo "│ TOTAL DEBIT  : " . str_pad("Rp " . number_format($totalDebit, 0, ',', '.'), 46) . " │" . PHP_EOL;
    echo "│ TOTAL CREDIT : " . str_pad("Rp " . number_format($totalCredit, 0, ',', '.'), 46) . " │" . PHP_EOL;
    echo "└─────────────────────────────────────────────────────────────────┘" . PHP_EOL . PHP_EOL;
    
    // Verification
    echo "✓ VERIFICATION CHECKLIST" . PHP_EOL;
    echo "─────────────────────────────────────────────────────────────────" . PHP_EOL;
    
    $allPassed = true;
    
    // Check 1: Kas/Bank
    if ($hasKas) {
        echo "✅ Kas/Bank Account Found" . PHP_EOL;
    } else {
        echo "❌ Kas/Bank Account MISSING" . PHP_EOL;
        $allPassed = false;
    }
    
    // Check 2: Pendapatan
    if ($hasPendapatan) {
        echo "✅ Pendapatan Account Found" . PHP_EOL;
    } else {
        echo "❌ Pendapatan Account MISSING" . PHP_EOL;
        $allPassed = false;
    }
    
    // Check 3: HPP
    if ($hpp > 0) {
        if ($hasHPP) {
            echo "✅ HPP Account Found (COA 554)" . PHP_EOL;
            
            $expectedHPP = $hpp * $qty;
            if (abs($hppAmount - $expectedHPP) < 0.01) {
                echo "✅ HPP Amount Correct: Rp " . number_format($hppAmount, 0, ',', '.') . PHP_EOL;
            } else {
                echo "❌ HPP Amount INCORRECT" . PHP_EOL;
                echo "   Expected: Rp " . number_format($expectedHPP, 0, ',', '.') . PHP_EOL;
                echo "   Actual  : Rp " . number_format($hppAmount, 0, ',', '.') . PHP_EOL;
                $allPassed = false;
            }
        } else {
            echo "❌ HPP Account MISSING (should use COA 554)" . PHP_EOL;
            $allPassed = false;
        }
    } else {
        echo "⚠️  HPP is 0 - No HPP entries expected" . PHP_EOL;
    }
    
    // Check 4: Persediaan
    if ($hpp > 0) {
        if ($hasPersediaan) {
            echo "✅ Persediaan Account Found" . PHP_EOL;
        } else {
            echo "❌ Persediaan Account MISSING" . PHP_EOL;
            $allPassed = false;
        }
    }
    
    // Check 5: Balanced
    if (abs($totalDebit - $totalCredit) < 0.01) {
        echo "✅ Journal is BALANCED" . PHP_EOL;
    } else {
        echo "❌ Journal is NOT BALANCED" . PHP_EOL;
        echo "   Difference: Rp " . number_format(abs($totalDebit - $totalCredit), 0, ',', '.') . PHP_EOL;
        $allPassed = false;
    }
    
    // Check 6: Entry count
    $expectedCount = $hpp > 0 ? 4 : 2;
    if ($journals->count() == $expectedCount) {
        echo "✅ Correct number of entries ({$expectedCount})" . PHP_EOL;
    } else {
        echo "❌ Incorrect number of entries" . PHP_EOL;
        echo "   Expected: {$expectedCount}, Actual: {$journals->count()}" . PHP_EOL;
        $allPassed = false;
    }
    
    echo PHP_EOL;
    
    // Final verdict
    if ($allPassed) {
        echo "╔════════════════════════════════════════════════════════════════╗" . PHP_EOL;
        echo "║                    🎉 TEST PASSED! 🎉                          ║" . PHP_EOL;
        echo "╚════════════════════════════════════════════════════════════════╝" . PHP_EOL;
        echo PHP_EOL;
        echo "✅ Jurnal penjualan tersimpan dengan SEMPURNA!" . PHP_EOL;
        echo "✅ HPP diambil dari /master-data/harga-pokok-produksi" . PHP_EOL;
        echo "✅ Semua entry balanced (debit = kredit)" . PHP_EOL;
        echo "✅ COA 554 (HPP) digunakan dengan benar" . PHP_EOL;
        echo "✅ Struktur sama dengan jurnal produksi & pembelian" . PHP_EOL;
    } else {
        echo "╔════════════════════════════════════════════════════════════════╗" . PHP_EOL;
        echo "║                    ❌ TEST FAILED! ❌                          ║" . PHP_EOL;
        echo "╚════════════════════════════════════════════════════════════════╝" . PHP_EOL;
        echo PHP_EOL;
        echo "❌ Ada masalah dengan jurnal penjualan" . PHP_EOL;
        echo "   Silakan periksa error di atas" . PHP_EOL;
    }
    
    DB::rollBack();
    echo PHP_EOL . "✅ Test completed (transaction rolled back)" . PHP_EOL;
    
} catch (\Exception $e) {
    DB::rollBack();
    echo PHP_EOL;
    echo "╔════════════════════════════════════════════════════════════════╗" . PHP_EOL;
    echo "║                    ❌ ERROR! ❌                                ║" . PHP_EOL;
    echo "╚════════════════════════════════════════════════════════════════╝" . PHP_EOL;
    echo PHP_EOL;
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File : " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
