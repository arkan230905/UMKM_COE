<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Data Ayam Kampung Saat Ini ===\n";
$ayam = \App\Models\BahanBaku::find(2);
echo "Master Stock: " . ($ayam->stok ?? 0) . " Ekor\n";
echo "Master Harga: Rp " . number_format($ayam->harga_satuan ?? 0, 2) . "\n";

echo "\n=== Semua Stock Movements ===\n";
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->orderBy('tanggal', 'asc')
    ->get();

foreach ($movements as $m) {
    echo "Tanggal: {$m->tanggal}, Direction: {$m->direction}, Qty: {$m->qty} {$m->satuan}, Total: Rp " . number_format($m->total_cost, 0) . "\n";
}

echo "\n=== Konversi Satuan ===\n";
echo "Sub Satuan 1 Nilai: " . ($ayam->sub_satuan_1_nilai ?? 'N/A') . " (Potong per Ekor)\n";
echo "Sub Satuan 1 Konversi: " . ($ayam->sub_satuan_1_konversi ?? 'N/A') . " (Ekor per Potong)\n";

// Test conversion logic
$potongPerEkor = $ayam->sub_satuan_1_nilai ?? 6;
echo "\n1 Ekor = {$potongPerEkor} Potong\n";
echo "1 Potong = " . (1 / $potongPerEkor) . " Ekor\n";

// Check what the controller is calculating
echo "\n=== Perhitungan Controller (Range 31/01 - 03/02) ===\n";
$from = '2026-01-31';
$to = '2026-02-03';

// Calculate initial stock before date range
$saldoAwalQty = 0.0;
$saldoAwalNilai = 0.0;

$before = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->whereDate('tanggal', '<', $from)
    ->orderBy('tanggal', 'asc')
    ->get();

echo "Movements before $from:\n";
foreach ($before as $m) {
    echo "- {$m->tanggal}: {$m->direction} {$m->qty} {$m->satuan}\n";
    if ($m->direction === 'in') {
        $saldoAwalQty += (float)$m->qty;
        $saldoAwalNilai += (float)($m->total_cost ?? 0);
    } else {
        $saldoAwalQty -= (float)$m->qty;
        $saldoAwalNilai -= (float)($m->total_cost ?? 0);
    }
}

// If no movements before, use master data
if ($before->isEmpty() && $ayam->stok > 0) {
    $saldoAwalQty = (float)($ayam->stok ?? 0);
    $saldoAwalNilai = $saldoAwalQty * (float)($ayam->harga_satuan ?? 0);
    echo "Using master data as initial stock: $saldoAwalQty Ekor = Rp " . number_format($saldoAwalNilai, 0) . "\n";
} else {
    echo "Calculated initial stock: $saldoAwalQty Ekor = Rp " . number_format($saldoAwalNilai, 0) . "\n";
}

// Get movements in range
$range = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->whereDate('tanggal', '>=', $from)
    ->whereDate('tanggal', '<=', $to)
    ->orderBy('tanggal', 'asc')
    ->get();

echo "\nMovements in range $from to $to:\n";
foreach ($range as $m) {
    echo "- {$m->tanggal}: {$m->direction} {$m->qty} {$m->satuan} = Rp " . number_format($m->total_cost, 0) . "\n";
}
