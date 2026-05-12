<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DATA PEMBELIAN TEST ===" . PHP_EOL;

// Ambil semua pembelian
$pembelians = \App\Models\Pembelian::orderBy('id', 'desc')->get();

echo "Total pembelian: " . $pembelians->count() . PHP_EOL . PHP_EOL;

echo "DAFTAR PEMBELIAN:" . PHP_EOL;
echo "=================" . PHP_EOL;

foreach ($pembelians as $pembelian) {
    echo "ID: {$pembelian->id}" . PHP_EOL;
    echo "Nomor: {$pembelian->nomor_pembelian}" . PHP_EOL;
    echo "Tanggal: {$pembelian->tanggal}" . PHP_EOL;
    echo "Vendor: " . ($pembelian->vendor ? $pembelian->vendor->nama_vendor : 'Unknown') . PHP_EOL;
    echo "Total: Rp " . number_format($pembelian->total_harga, 2, ',', '.') . PHP_EOL;
    echo "Payment: {$pembelian->payment_method}" . PHP_EOL;
    echo "Details: " . ($pembelian->details ? $pembelian->details->count() : 0) . " items" . PHP_EOL;
    
    // Cek journal entries
    $journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
        ->where('ref_id', $pembelian->id)
        ->get();
    
    echo "Journal: " . $journalEntries->count() . " entries" . PHP_EOL;
    
    // Identifikasi test data
    $isTest = false;
    if (strpos(strtolower($pembelian->nomor_pembelian), 'test') !== false) {
        $isTest = true;
    }
    if (strpos(strtolower($pembelian->nomor_pembelian), 'dummy') !== false) {
        $isTest = true;
    }
    if ($pembelian->vendor && strpos(strtolower($pembelian->vendor->nama_vendor), 'test') !== false) {
        $isTest = true;
    }
    
    echo "Status: " . ($isTest ? "TEST DATA" : "REAL DATA") . PHP_EOL;
    echo PHP_EOL . "----------------------------------------" . PHP_EOL;
}

echo PHP_EOL . "✅ Selesai!" . PHP_EOL;
