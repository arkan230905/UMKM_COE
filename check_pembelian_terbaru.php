<?php
/**
 * Script untuk cek pembelian terbaru
 * 
 * Jalankan: php check_pembelian_terbaru.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pembelian;

echo "=== CEK PEMBELIAN TERBARU ===\n\n";

// Get 10 pembelian terbaru
$pembelians = Pembelian::with('vendor')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "Total Pembelian: " . Pembelian::count() . "\n\n";

echo "10 Pembelian Terbaru:\n";
echo str_repeat("=", 80) . "\n";

foreach ($pembelians as $pembelian) {
    echo "ID: {$pembelian->id}\n";
    echo "Nomor: {$pembelian->nomor_pembelian}\n";
    echo "Tanggal: {$pembelian->tanggal}\n";
    echo "Vendor: " . ($pembelian->vendor ? $pembelian->vendor->nama_vendor : 'N/A') . "\n";
    echo "Total: Rp " . number_format($pembelian->total, 0, ',', '.') . "\n";
    echo "Status: {$pembelian->status}\n";
    echo "Payment Method: {$pembelian->payment_method}\n";
    echo "Created At: {$pembelian->created_at}\n";
    echo "Updated At: {$pembelian->updated_at}\n";
    echo str_repeat("-", 80) . "\n";
}

// Cek pembelian dengan oldest() seperti di controller
echo "\n=== QUERY DENGAN oldest() (seperti di controller) ===\n\n";
$pembeliansOldest = Pembelian::with('vendor')
    ->oldest()
    ->limit(5)
    ->get();

echo "5 Pembelian Pertama (oldest):\n";
foreach ($pembeliansOldest as $pembelian) {
    echo "- ID: {$pembelian->id}, Nomor: {$pembelian->nomor_pembelian}, Tanggal: {$pembelian->tanggal}\n";
}

// Cek pembelian dengan latest() 
echo "\n5 Pembelian Terakhir (latest):\n";
$pembeliansLatest = Pembelian::with('vendor')
    ->latest()
    ->limit(5)
    ->get();

foreach ($pembeliansLatest as $pembelian) {
    echo "- ID: {$pembelian->id}, Nomor: {$pembelian->nomor_pembelian}, Tanggal: {$pembelian->tanggal}\n";
}

echo "\n=== SELESAI ===\n";
