<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pembelian;

echo "=== CEK PEMBELIAN (INCLUDING TRASHED) ===\n\n";

// Get all including soft deleted
$allPembelians = Pembelian::withTrashed()->get();
$activePembelians = Pembelian::all();
$trashedPembelians = Pembelian::onlyTrashed()->get();

echo "Total Pembelian (All): " . $allPembelians->count() . "\n";
echo "Total Pembelian (Active): " . $activePembelians->count() . "\n";
echo "Total Pembelian (Trashed): " . $trashedPembelians->count() . "\n\n";

if ($allPembelians->count() > 0) {
    echo "Semua Pembelian (Including Trashed):\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($allPembelians as $pembelian) {
        $status = $pembelian->trashed() ? '🗑️ DELETED' : '✅ ACTIVE';
        echo "{$status} - ID: {$pembelian->id}, Nomor: {$pembelian->nomor_pembelian}, ";
        echo "Tanggal: {$pembelian->tanggal}, Total: Rp " . number_format($pembelian->total, 0, ',', '.') . "\n";
        echo "   Created: {$pembelian->created_at}, Deleted: " . ($pembelian->deleted_at ?? 'NULL') . "\n";
        echo str_repeat("-", 80) . "\n";
    }
}

// Cek raw database
echo "\n=== CEK RAW DATABASE ===\n";
$rawData = DB::table('pembelians')->get();
echo "Total rows in pembelians table: " . $rawData->count() . "\n\n";

if ($rawData->count() > 0) {
    foreach ($rawData as $row) {
        echo "ID: {$row->id}, Nomor: {$row->nomor_pembelian}, ";
        echo "Deleted At: " . ($row->deleted_at ?? 'NULL') . "\n";
    }
}

echo "\n=== SELESAI ===\n";
