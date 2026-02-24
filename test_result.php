<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate the controller logic
$tipe = 'material';
$from = '2026-02-01';
$to = '2026-02-03';
$itemId = 2; // Ayam Kampung

// Get item
$item = \App\Models\BahanBaku::find($itemId);

// Calculate initial stock before date range
$saldoAwalQty = 0.0;
$saldoAwalNilai = 0.0;

$before = \App\Models\StockMovement::where('item_type', $tipe)
    ->where('item_id', $itemId)
    ->whereDate('tanggal', '<', $from)
    ->orderBy('tanggal', 'asc')
    ->get();
    
foreach ($before as $m) {
    if ($m->direction === 'in') {
        $saldoAwalQty += (float)$m->qty;
        $saldoAwalNilai += (float)($m->total_cost ?? 0);
    } else {
        $saldoAwalQty -= (float)$m->qty;
        $saldoAwalNilai -= (float)($m->total_cost ?? 0);
    }
}

echo "Initial stock before $from: Qty=$saldoAwalQty, Nilai=$saldoAwalNilai\n";

// Get movements in date range
$movements = \App\Models\StockMovement::where('item_type', $tipe)
    ->where('item_id', $itemId)
    ->whereDate('tanggal', '>=', $from)
    ->whereDate('tanggal', '<=', $to)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

echo "\nMovements in date range:\n";
foreach ($movements as $m) {
    echo "Date: {$m->tanggal}, Direction: {$m->direction}, Qty: {$m->qty}, Total Cost: " . ($m->total_cost ?? 0) . "\n";
}

// Build daily stock card
$dailyStock = [];

if ($movements->count() > 0) {
    // Get date range
    $startDate = \Carbon\Carbon::parse($from);
    $endDate = \Carbon\Carbon::parse($to);
    
    // Initialize running totals
    $runningQty = $saldoAwalQty;
    $runningNilai = $saldoAwalNilai;
    
    // Group movements by date
    $movementsByDate = [];
    foreach ($movements as $m) {
        $date = is_string($m->tanggal) ? $m->tanggal : $m->tanggal->format('Y-m-d');
        if (!isset($movementsByDate[$date])) {
            $movementsByDate[$date] = [
                'in_qty' => 0,
                'in_nilai' => 0,
                'out_qty' => 0,
                'out_nilai' => 0
            ];
        }
        
        if ($m->direction === 'in') {
            $movementsByDate[$date]['in_qty'] += (float)$m->qty;
            $movementsByDate[$date]['in_nilai'] += (float)($m->total_cost ?? 0);
        } else {
            $movementsByDate[$date]['out_qty'] += (float)$m->qty;
            $movementsByDate[$date]['out_nilai'] += (float)($m->total_cost ?? 0);
        }
    }
    
    // Build daily stock card for each day in range
    $currentDate = $startDate->copy();
    while ($currentDate <= $endDate) {
        $dateStr = $currentDate->format('Y-m-d');
        
        // Get movements for this date
        $dayMovements = $movementsByDate[$dateStr] ?? [
            'in_qty' => 0,
            'in_nilai' => 0,
            'out_qty' => 0,
            'out_nilai' => 0
        ];
        
        // Calculate daily changes
        $dailyInQty = $dayMovements['in_qty'];
        $dailyInNilai = $dayMovements['in_nilai'];
        $dailyOutQty = $dayMovements['out_qty'];
        $dailyOutNilai = $dayMovements['out_nilai'];
        
        // Update running totals
        $runningQty += $dailyInQty - $dailyOutQty;
        $runningNilai += $dailyInNilai - $dailyOutNilai;
        
        // Add to daily stock
        $dailyStock[] = [
            'tanggal' => $dateStr,
            'saldo_awal_qty' => $runningQty - $dailyInQty + $dailyOutQty,
            'saldo_awal_nilai' => $runningNilai - $dailyInNilai + $dailyOutNilai,
            'pembelian_qty' => $dailyInQty,
            'pembelian_nilai' => $dailyInNilai,
            'produksi_qty' => $dailyOutQty,
            'produksi_nilai' => $dailyOutNilai,
            'saldo_akhir_qty' => $runningQty,
            'saldo_akhir_nilai' => $runningNilai,
        ];
        
        // Move to next day
        $currentDate->addDay();
    }
}

echo "\n=== Daily Stock Card Results ===\n";
foreach ($dailyStock as $day) {
    $potongConversion = $item->sub_satuan_1_nilai ?? 6; // 6 Potong per Ekor
    
    $saldoAwalPotong = $day['saldo_awal_qty'] * $potongConversion;
    $pembelianPotong = $day['pembelian_qty'] * $potongConversion;
    $produksiPotong = $day['produksi_qty'] * $potongConversion;
    $saldoAkhirPotong = $day['saldo_akhir_qty'] * $potongConversion;
    
    $saldoAwalHarga = $saldoAwalPotong > 0 ? $day['saldo_awal_nilai'] / $saldoAwalPotong : 0;
    $pembelianHarga = $pembelianPotong > 0 ? $day['pembelian_nilai'] / $pembelianPotong : 0;
    $produksiHarga = $produksiPotong > 0 ? $day['produksi_nilai'] / $produksiPotong : 0;
    $saldoAkhirHarga = $saldoAkhirPotong > 0 ? $day['saldo_akhir_nilai'] / $saldoAkhirPotong : 0;
    
    echo "Date: " . \Carbon\Carbon::parse($day['tanggal'])->format('d/m/Y') . "\n";
    echo "  Stok Awal: " . number_format($saldoAwalPotong, 0) . " Potong @ Rp " . number_format($saldoAwalHarga, 2) . " = Rp " . number_format($day['saldo_awal_nilai'], 0) . "\n";
    echo "  Pembelian: " . number_format($pembelianPotong, 0) . " Potong @ Rp " . number_format($pembelianHarga, 2) . " = Rp " . number_format($day['pembelian_nilai'], 0) . "\n";
    echo "  Produksi: " . number_format($produksiPotong, 0) . " Potong @ Rp " . number_format($produksiHarga, 2) . " = Rp " . number_format($day['produksi_nilai'], 0) . "\n";
    echo "  Total: " . number_format($saldoAkhirPotong, 0) . " Potong @ Rp " . number_format($saldoAkhirHarga, 2) . " = Rp " . number_format($day['saldo_akhir_nilai'], 0) . "\n";
    echo "\n";
}
