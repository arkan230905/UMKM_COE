<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Monthly Logic - Saldo Awal Bulanan ===\n";

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

// Build daily stock - only show dates with transactions
$dailyStock = [];
$runningQty = $saldoAwalQty;
$runningNilai = $saldoAwalNilai;

// Group movements by date
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

// Sort dates
ksort($movementsByDate);

$previousMonth = null;
$currentMonth = null;

// Build daily stock card only for dates with movements
foreach ($movementsByDate as $dateStr => $dayMovements) {
    $dailyInQty = $dayMovements['in_qty'];
    $dailyInNilai = $dayMovements['in_nilai'];
    
    // Check if this is first day of a new month
    $dateCarbon = \Carbon\Carbon::parse($dateStr);
    $currentMonth = $dateCarbon->format('Y-m');
    
    if ($previousMonth === null || $currentMonth !== $previousMonth) {
        // First transaction of the month - show opening balance
        $displaySaldoAwalQty = $runningQty;
        $displaySaldoAwalNilai = $runningNilai;
        $previousMonth = $currentMonth;
        echo "First transaction of month $currentMonth\n";
    } else {
        // Regular transaction - no opening balance
        $displaySaldoAwalQty = 0;
        $displaySaldoAwalNilai = 0;
        echo "Regular transaction in month $currentMonth\n";
    }
    
    // Update running totals
    $runningQty += $dailyInQty;
    $runningNilai += $dailyInNilai;
    
    $dailyStock[] = [
        'tanggal' => $dateStr,
        'saldo_awal_qty' => $displaySaldoAwalQty,
        'saldo_awal_nilai' => $displaySaldoAwalNilai,
        'pembelian_qty' => $dailyInQty,
        'pembelian_nilai' => $dailyInNilai,
        'saldo_akhir_qty' => $runningQty,
        'saldo_akhir_nilai' => $runningNilai,
    ];
}

// Display results in Potong
echo "\n=== Kartu Stok Ayam Kampung (Satuan Potong) ===\n";
echo "Saldo awal di awal bulan:\n\n";

$potongPerEkor = $item->sub_satuan_1_nilai ?? 6;

foreach ($dailyStock as $index => $day) {
    $saldoAwalPotong = $day['saldo_awal_qty'] * $potongPerEkor;
    $pembelianPotong = $day['pembelian_qty'] * $potongPerEkor;
    $saldoAkhirPotong = $day['saldo_akhir_qty'] * $potongPerEkor;
    
    $saldoAwalHarga = $saldoAwalPotong > 0 ? $day['saldo_awal_nilai'] / $saldoAwalPotong : 0;
    $pembelianHarga = $pembelianPotong > 0 ? $day['pembelian_nilai'] / $pembelianPotong : 0;
    $saldoAkhirHarga = $saldoAkhirPotong > 0 ? $day['saldo_akhir_nilai'] / $saldoAkhirPotong : 0;
    
    echo "Tanggal: " . \Carbon\Carbon::parse($day['tanggal'])->format('d/m/Y') . "\n";
    echo "Stok Awal: " . number_format($saldoAwalPotong, 0) . " Potong @ Rp " . number_format($saldoAwalHarga, 2) . " = Rp " . number_format($day['saldo_awal_nilai'], 0) . "\n";
    echo "Pembelian: " . number_format($pembelianPotong, 0) . " Potong @ Rp " . number_format($pembelianHarga, 2) . " = Rp " . number_format($day['pembelian_nilai'], 0) . "\n";
    echo "Total: " . number_format($saldoAkhirPotong, 0) . " Potong @ Rp " . number_format($saldoAkhirHarga, 2) . " = Rp " . number_format($day['saldo_akhir_nilai'], 0) . "\n";
    echo "\n";
}

echo "Total baris: " . count($dailyStock) . "\n";
