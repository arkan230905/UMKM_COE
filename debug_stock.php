<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get Ayam Kampung data
$ayam = \App\Models\BahanBaku::where('nama_bahan', 'like', '%Ayam Kampung%')->first();
if (!$ayam) {
    echo "Ayam Kampung not found\n";
    exit;
}

echo "=== Ayam Kampung Data ===\n";
echo "ID: {$ayam->id}\n";
echo "Nama: {$ayam->nama_bahan}\n";
echo "Satuan: " . ($ayam->satuan->nama_satuan ?? 'N/A') . "\n";
echo "Sub Satuan 1: " . ($ayam->sub_satuan_1->nama_satuan ?? 'N/A') . " (Konversi: " . ($ayam->sub_satuan_1_konversi ?? 'N/A') . ", Nilai: " . ($ayam->sub_satuan_1_nilai ?? 'N/A') . ")\n";
echo "Sub Satuan 2: " . ($ayam->sub_satuan_2->nama_satuan ?? 'N/A') . " (Konversi: " . ($ayam->sub_satuan_2_konversi ?? 'N/A') . ", Nilai: " . ($ayam->sub_satuan_2_nilai ?? 'N/A') . ")\n";
echo "Sub Satuan 3: " . ($ayam->sub_satuan_3->nama_satuan ?? 'N/A') . " (Konversi: " . ($ayam->sub_satuan_3_konversi ?? 'N/A') . ", Nilai: " . ($ayam->sub_satuan_3_nilai ?? 'N/A') . ")\n";

// Get all stock movements for Ayam Kampung
echo "\n=== All Stock Movements ===\n";
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

foreach ($movements as $m) {
    echo "Date: {$m->tanggal}, Direction: {$m->direction}, Qty: {$m->qty}, Total Cost: " . ($m->total_cost ?? 0) . ", Ref: {$m->ref_type}#{$m->ref_id}\n";
}

// Calculate stock before 01/02/2026
$beforeDate = '2026-02-01';
$saldoAwalQty = 0.0;
$saldoAwalNilai = 0.0;

$before = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->whereDate('tanggal', '<', $beforeDate)
    ->orderBy('tanggal', 'asc')
    ->get();

echo "\n=== Stock Before $beforeDate ===\n";
foreach ($before as $m) {
    echo "Date: {$m->tanggal}, Direction: {$m->direction}, Qty: {$m->qty}, Total Cost: " . ($m->total_cost ?? 0) . "\n";
    if ($m->direction === 'in') {
        $saldoAwalQty += (float)$m->qty;
        $saldoAwalNilai += (float)($m->total_cost ?? 0);
    } else {
        $saldoAwalQty -= (float)$m->qty;
        $saldoAwalNilai -= (float)($m->total_cost ?? 0);
    }
}

echo "\nSaldo Awal at $beforeDate: Qty=$saldoAwalQty, Nilai=$saldoAwalNilai\n";

// Convert to Potong (assuming 1 Ekor = 6 Potong)
$potongConversion = $ayam->sub_satuan_1_nilai ?? 6; // sub units per primary unit
$saldoAwalPotong = $saldoAwalQty * $potongConversion;
$saldoAwalHargaPotong = $saldoAwalPotong > 0 ? $saldoAwalNilai / $saldoAwalPotong : 0;

echo "Saldo Awal in Potong: Qty=$saldoAwalPotong, Harga=" . number_format($saldoAwalHargaPotong, 2) . ", Total=" . number_format($saldoAwalNilai, 0) . "\n";

// Get movements in February 2026
echo "\n=== February 2026 Movements ===\n";
$febMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->whereDate('tanggal', '>=', '2026-02-01')
    ->whereDate('tanggal', '<=', '2026-02-03')
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

foreach ($febMovements as $m) {
    echo "Date: {$m->tanggal}, Direction: {$m->direction}, Qty: {$m->qty}, Total Cost: " . ($m->total_cost ?? 0) . "\n";
}
