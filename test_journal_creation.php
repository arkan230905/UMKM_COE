<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST JOURNAL CREATION FOR NEW PRODUCT ===" . PHP_EOL;

// Cek produk Test Product Journal
$produk = \App\Models\Produk::where('nama_produk', 'Test Product Journal')->first();
if (!$produk) {
    echo "Produk Test Product Journal tidak ditemukan" . PHP_EOL;
    exit;
}

echo "Produk: " . $produk->nama_produk . PHP_EOL;
echo "HPP: " . $produk->hpp . PHP_EOL;
echo "coa_persediaan_id: " . ($produk->coa_persediaan_id ?? 'NULL') . PHP_EOL;
echo PHP_EOL;

// Buat penjualan baru untuk testing
echo "Membuat penjualan baru..." . PHP_EOL;

$penjualan = \App\Models\Penjualan::create([
    'nomor_penjualan' => 'SJ-TEST-' . date('YmdHis'),
    'tanggal' => date('Y-m-d'),
    'payment_method' => 'cash',
    'total' => 25000,
    'subtotal_produk' => 25000,
    'grand_total' => 25000,
    'user_id' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Penjualan dibuat: " . $penjualan->nomor_penjualan . PHP_EOL;
echo "ID: " . $penjualan->id . PHP_EOL;

// Buat detail penjualan
$detail = \App\Models\PenjualanDetail::create([
    'penjualan_id' => $penjualan->id,
    'produk_id' => $produk->id,
    'jumlah' => 1,
    'harga_satuan' => 25000,
    'subtotal' => 25000,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Detail penjualan dibuat" . PHP_EOL;

// Test journal creation
echo PHP_EOL . "Testing journal creation..." . PHP_EOL;
try {
    \App\Services\JournalService::createJournalFromPenjualan($penjualan);
    echo "Jurnal berhasil dibuat!" . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

// Cek jurnal yang dibuat
echo PHP_EOL . "=== CEK JOURNAL YANG DIBUAT ===" . PHP_EOL;

// Cek journal_entries
$journalEntry = \Illuminate\Support\Facades\DB::table('journal_entries')
    ->where('ref_type', 'sale')
    ->where('ref_id', $penjualan->id)
    ->first();

if ($journalEntry) {
    echo "Journal Entry ID: " . $journalEntry->id . PHP_EOL;
    
    $lines = \Illuminate\Support\Facades\DB::table('journal_lines')
        ->leftJoin('coas', 'coas.id', '=', 'journal_lines.coa_id')
        ->where('journal_lines.journal_entry_id', $journalEntry->id)
        ->select('journal_lines.*', 'coas.kode_akun', 'coas.nama_akun')
        ->orderBy('journal_lines.id')
        ->get();
    
    echo "Journal Lines (" . $lines->count() . "):" . PHP_EOL;
    foreach ($lines as $line) {
        echo sprintf(
            "  %s %s | Debit: %s | Credit: %s | Memo: %s" . PHP_EOL,
            $line->kode_akun,
            $line->nama_akun,
            number_format($line->debit, 2),
            number_format($line->credit, 2),
            $line->memo
        );
    }
    
    // Cek balance
    $totalDebit = $lines->sum('debit');
    $totalCredit = $lines->sum('credit');
    echo PHP_EOL . "Total Debit: " . number_format($totalDebit, 2) . PHP_EOL;
    echo "Total Credit: " . number_format($totalCredit, 2) . PHP_EOL;
    echo "Balance: " . ($totalDebit == $totalCredit ? "YES" : "NO") . PHP_EOL;
} else {
    echo "Journal Entry tidak ditemukan" . PHP_EOL;
}
