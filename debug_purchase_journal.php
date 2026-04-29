<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging Purchase Journal Balance...\n\n";

$pembelian = \App\Models\Pembelian::latest()->first();
if (!$pembelian) {
    echo "❌ No pembelian found\n";
    exit;
}

echo "📋 Pembelian Details:\n";
echo "   ID: {$pembelian->id}\n";
echo "   Nomor: {$pembelian->nomor_pembelian}\n";
echo "   Subtotal: Rp " . number_format($pembelian->subtotal, 0, ',', '.') . "\n";
echo "   Biaya Kirim: Rp " . number_format($pembelian->biaya_kirim ?? 0, 0, ',', '.') . "\n";
echo "   PPN %: " . ($pembelian->ppn_persen ?? 0) . "%\n";
echo "   PPN Nominal: Rp " . number_format($pembelian->ppn_nominal ?? 0, 0, ',', '.') . "\n";
echo "   Total Harga: Rp " . number_format($pembelian->total_harga, 0, ',', '.') . "\n";
echo "   Payment Method: {$pembelian->payment_method}\n\n";

echo "📦 Pembelian Details:\n";
foreach ($pembelian->details as $detail) {
    $itemName = $detail->bahanBaku->nama_bahan ?? $detail->bahanPendukung->nama_bahan ?? 'Unknown';
    $amount = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
    echo "   - {$itemName}: {$detail->jumlah} x {$detail->satuan} @ Rp " . number_format($detail->harga_satuan, 0, ',', '.') . " = Rp " . number_format($amount, 0, ',', '.') . "\n";
    
    // Check COA for this item
    if ($detail->bahan_baku_id && $detail->bahanBaku) {
        $coaCode = $detail->bahanBaku->coa_persediaan_id;
        echo "     COA Persediaan: {$coaCode}\n";
        $coa = \App\Models\Coa::where('kode_akun', $coaCode)->first();
        if ($coa) {
            echo "     Found: {$coa->kode_akun} - {$coa->nama_akun}\n";
        } else {
            echo "     ❌ COA NOT FOUND\n";
        }
    } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
        $coaCode = $detail->bahanPendukung->coa_persediaan_id;
        echo "     COA Persediaan: {$coaCode}\n";
        $coa = \App\Models\Coa::where('kode_akun', $coaCode)->first();
        if ($coa) {
            echo "     Found: {$coa->kode_akun} - {$coa->nama_akun}\n";
        } else {
            echo "     ❌ COA NOT FOUND\n";
        }
    }
}
echo "\n";

// Test manual journal creation
echo "🔧 Testing Manual Journal Creation...\n";

$subtotalAmount = 0;
$lines = [];

// 1. DEBIT: Persediaan spesifik untuk setiap bahan
foreach ($pembelian->details as $detail) {
    $amount = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
    $subtotalAmount += $amount;
    
    if ($detail->bahan_baku_id && $detail->bahanBaku) {
        $coaCode = $detail->bahanBaku->coa_persediaan_id;
        $coa = \App\Models\Coa::where('kode_akun', $coaCode)->first();
        if ($coa) {
            $lines[] = [
                'coa_id' => $coa->id,
                'debit' => $amount,
                'credit' => 0,
                'memo' => $coa->nama_akun
            ];
            echo "   DEBIT {$coa->kode_akun} {$coa->nama_akun}: Rp " . number_format($amount, 0, ',', '.') . "\n";
        } else {
            echo "   ❌ Missing COA for {$detail->bahanBaku->nama_bahan}\n";
        }
    } elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
        $coaCode = $detail->bahanPendukung->coa_persediaan_id;
        $coa = \App\Models\Coa::where('kode_akun', $coaCode)->first();
        if ($coa) {
            $lines[] = [
                'coa_id' => $coa->id,
                'debit' => $amount,
                'credit' => 0,
                'memo' => $coa->nama_akun
            ];
            echo "   DEBIT {$coa->kode_akun} {$coa->nama_akun}: Rp " . number_format($amount, 0, ',', '.') . "\n";
        } else {
            echo "   ❌ Missing COA for {$detail->bahanPendukung->nama_bahan}\n";
        }
    }
}

// 2. CREDIT: Kas/Bank atau Utang
$totalAmount = $subtotalAmount + (float)($pembelian->ppn_nominal ?? 0) + (float)($pembelian->biaya_kirim ?? 0);
echo "\n   Total Debit: Rp " . number_format($subtotalAmount, 0, ',', '.') . "\n";
echo "   Total Amount: Rp " . number_format($totalAmount, 0, ',', '.') . "\n";

// Get credit account
if ($pembelian->payment_method === 'cash') {
    $coa = \App\Models\Coa::where('kode_akun', '112')->first(); // Kas
} elseif ($pembelian->payment_method === 'transfer') {
    $coa = \App\Models\Coa::where('kode_akun', '111')->first(); // Kas Bank
} else {
    $coa = \App\Models\Coa::where('kode_akun', '210')->first(); // Hutang Usaha
}

if ($coa) {
    $lines[] = [
        'coa_id' => $coa->id,
        'debit' => 0,
        'credit' => $totalAmount,
        'memo' => $coa->nama_akun
    ];
    echo "   CREDIT {$coa->kode_akun} {$coa->nama_akun}: Rp " . number_format($totalAmount, 0, ',', '.') . "\n";
}

// Check balance
$totalDebit = array_sum(array_column($lines, 'debit'));
$totalCredit = array_sum(array_column($lines, 'credit'));

echo "\n📊 Balance Check:\n";
echo "   Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "   Total Credit: Rp " . number_format($totalCredit, 0, ',', '.') . "\n";
echo "   Difference: Rp " . number_format(abs($totalDebit - $totalCredit), 0, ',', '.') . "\n";
echo "   Balanced: " . (abs($totalDebit - $totalCredit) < 0.01 ? '✅ YES' : '❌ NO') . "\n";
