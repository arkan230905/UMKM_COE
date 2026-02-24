<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Final Kartu Stok ===\n";

// Simulate controller logic
$tipe = 'material';
$from = '2026-01-31';
$to = '2026-02-03';
$itemId = 2;

// Get item
$item = \App\Models\BahanBaku::find($itemId);

// Calculate initial stock
$saldoAwalQty = 0.0;
$saldoAwalNilai = 0.0;

$before = \App\Models\StockMovement::where('item_type', $tipe)
    ->where('item_id', $itemId)
    ->whereDate('tanggal', '<', $from)
    ->get();

if ($before->isEmpty() && $item->stok > 0) {
    $saldoAwalQty = (float)($item->stok ?? 0);
    $saldoAwalNilai = $saldoAwalQty * (float)($item->harga_satuan ?? 0);
}

echo "Saldo awal per $from: $saldoAwalQty Ekor = Rp " . number_format($saldoAwalNilai, 0) . "\n";

// Get movements
$movements = \App\Models\StockMovement::where('item_type', $tipe)
    ->where('item_id', $itemId)
    ->whereDate('tanggal', '>=', $from)
    ->whereDate('tanggal', '<=', $to)
    ->orderBy('tanggal', 'asc')
    ->get();

// Build daily stock
$dailyStock = [];
$startDate = \Carbon\Carbon::parse($from);
$endDate = \Carbon\Carbon::parse($to);
$runningQty = $saldoAwalQty;
$runningNilai = $saldoAwalNilai;

// Group by date
$movementsByDate = [];
foreach ($movements as $m) {
    $date = is_string($m->tanggal) ? $m->tanggal : $m->tanggal->format('Y-m-d');
    if (!isset($movementsByDate[$date])) {
        $movementsByDate[$date] = ['in_qty' => 0, 'in_nilai' => 0, 'out_qty' => 0, 'out_nilai' => 0];
    }
    
    if ($m->direction === 'in') {
        $movementsByDate[$date]['in_qty'] += (float)$m->qty;
        $movementsByDate[$date]['in_nilai'] += (float)($m->total_cost ?? 0);
    }
}

// Build daily stock card
$currentDate = $startDate->copy();
while ($currentDate <= $endDate) {
    $dateStr = $currentDate->format('Y-m-d');
    $dayMovements = $movementsByDate[$dateStr] ?? ['in_qty' => 0, 'in_nilai' => 0, 'out_qty' => 0, 'out_nilai' => 0];
    
    $dailyInQty = $dayMovements['in_qty'];
    $dailyInNilai = $dayMovements['in_nilai'];
    
    $runningQty += $dailyInQty;
    $runningNilai += $dailyInNilai;
    
    $dailyStock[] = [
        'tanggal' => $dateStr,
        'saldo_awal_qty' => $runningQty - $dailyInQty,
        'saldo_awal_nilai' => $runningNilai - $dailyInNilai,
        'pembelian_qty' => $dailyInQty,
        'pembelian_nilai' => $dailyInNilai,
        'saldo_akhir_qty' => $runningQty,
        'saldo_akhir_nilai' => $runningNilai,
    ];
    
    $currentDate->addDay();
}

// Display results in Potong
echo "\n=== Kartu Stok Ayam Kampung (Satuan Potong) ===\n";
$potongPerEkor = $item->sub_satuan_1_nilai ?? 6;

foreach ($dailyStock as $day) {
    $saldoAwalPotong = $day['saldo_awal_qty'] * $potongPerEkor;
    $pembelianPotong = $day['pembelian_qty'] * $potongPerEkor;
    $saldoAkhirPotong = $day['saldo_akhir_qty'] * $potongPerEkor;
    
    $saldoAwalHarga = $saldoAwalPotong > 0 ? $day['saldo_awal_nilai'] / $saldoAwalPotong : 0;
    $pembelianHarga = $pembelianPotong > 0 ? $day['pembelian_nilai'] / $pembelianPotong : 0;
    $saldoAkhirHarga = $saldoAkhirPotong > 0 ? $day['saldo_akhir_nilai'] / $saldoAkhirPotong : 0;
    
    echo "\nTanggal: " . \Carbon\Carbon::parse($day['tanggal'])->format('d/m/Y') . "\n";
    echo "Stok Awal: " . number_format($saldoAwalPotong, 0) . " Potong @ Rp " . number_format($saldoAwalHarga, 2) . " = Rp " . number_format($day['saldo_awal_nilai'], 0) . "\n";
    echo "Pembelian: " . number_format($pembelianPotong, 0) . " Potong @ Rp " . number_format($pembelianHarga, 2) . " = Rp " . number_format($day['pembelian_nilai'], 0) . "\n";
    echo "Total: " . number_format($saldoAkhirPotong, 0) . " Potong @ Rp " . number_format($saldoAkhirHarga, 2) . " = Rp " . number_format($day['saldo_akhir_nilai'], 0) . "\n";
}

echo "\nKonversi: 1 Ekor = $potongPerEkor Potong\n";
