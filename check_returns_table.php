<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek Tabel Returns ===\n";

if (\Schema::hasTable('returns')) {
    $columns = \Schema::getColumnListing('returns');
    echo "Kolom di tabel returns:\n";
    foreach ($columns as $col) {
        echo "- $col\n";
    }
    
    // Cek sample data
    $sample = \DB::table('returns')->limit(3)->get();
    foreach ($sample as $row) {
        echo "ID: {$row->id}, Tanggal: " . ($row->tanggal ?? 'N/A') . ", Total: " . ($row->total ?? 'N/A') . "\n";
    }
} else {
    echo "❌ Tabel returns tidak ada\n";
}

echo "\n=== Test Query Returns ===\n";
$startDate = '2026-02-01';
$endDate = '2026-02-28';

// Test query returns
try {
    $returns = \DB::table('returns')
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->where(function($query) {
            $query->where('payment_method', 'transfer');
        })
        ->sum('total');
    
    echo "Returns dengan payment_method transfer: Rp " . number_format($returns, 2) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
