<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG TANGGAL LAPORAN ===\n\n";

// Test dengan berbagai format tanggal
$testDates = [
    '2026-02-01',  // Format yyyy-mm-dd (input type="date")
    '01/02/2026',  // Format dd/mm/yyyy
    '02/01/2026',  // Format mm/dd/yyyy
];

foreach ($testDates as $date) {
    echo "Testing date: '{$date}'\n";
    
    try {
        $carbon = \Carbon\Carbon::parse($date);
        echo "  Parsed as: " . $carbon->format('Y-m-d') . "\n";
        echo "  Display: " . $carbon->format('d/m/Y') . "\n";
    } catch (\Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    echo "---\n";
}

// Test query dengan format yyyy-mm-dd
echo "\n=== TEST QUERY DENGAN FORMAT YYYY-MM-DD ===\n";
$dariTanggal = '2026-02-01';
$sampaiTanggal = '2026-02-05';

echo "Query untuk periode: {$dariTanggal} - {$sampaiTanggal}\n";

$movements = \DB::table('stock_movements')
    ->where('item_type', 'support')
    ->whereBetween('tanggal', [$dariTanggal, $sampaiTanggal])
    ->get();

echo "Total movements: " . $movements->count() . "\n";

foreach ($movements as $movement) {
    echo "- {$movement->tanggal} | {$movement->item_type}#{$movement->item_id} | {$movement->direction} | {$movement->qty}\n";
}

// Test query dengan format yang salah
echo "\n=== TEST QUERY DENGAN FORMAT SALAH ===\n";
$dariTanggalSalah = '01/02/2026';
$sampaiTanggalSalah = '05/02/2026';

echo "Query untuk periode: {$dariTanggalSalah} - {$sampaiTanggalSalah}\n";

try {
    $movementsSalah = \DB::table('stock_movements')
        ->where('item_type', 'support')
        ->whereBetween('tanggal', [$dariTanggalSalah, $sampaiTanggalSalah])
        ->get();
    
    echo "Total movements: " . $movementsSalah->count() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== SELESAI ===\n";
