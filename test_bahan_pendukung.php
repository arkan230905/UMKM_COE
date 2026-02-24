<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Bahan Pendukung (Air) ===\n";

// Simulate controller logic
$tipe = 'bahan_pendukung';
$from = '2026-02-01';
$to = '2026-02-03';
$itemId = 14; // Air

// Get item
$item = \App\Models\BahanPendukung::find($itemId);

// Calculate initial stock
$saldoAwalQty = 0.0;
$saldoAwalNilai = 0.0;

$before = \App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $itemId)
    ->whereDate('tanggal', '<', $from)
    ->get();

if ($before->isEmpty() && $item->stok > 0) {
    $saldoAwalQty = (float)($item->stok ?? 0);
    $saldoAwalNilai = $saldoAwalQty * (float)($item->harga_satuan ?? 0);
}

// Get movements
$movements = \App\Models\StockMovement::where('item_type', 'support')
    ->where('item_id', $itemId)
    ->whereDate('tanggal', '>=', $from)
    ->whereDate('tanggal', '<=', $to)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

// Build daily stock - corrected logic
$dailyStock = [];
$runningQty = $saldoAwalQty;
$runningNilai = $saldoAwalNilai;

// Group movements by month
$monthlyMovements = [];
foreach ($movements as $m) {
    $dateStr = is_string($m->tanggal) ? $m->tanggal : $m->tanggal->format('Y-m-d');
    $monthKey = substr($dateStr, 0, 7);
    if (!isset($monthlyMovements[$monthKey])) {
        $monthlyMovements[$monthKey] = [];
    }
    $monthlyMovements[$monthKey][] = $m;
}

// Sort months
ksort($monthlyMovements);

// Process each month
foreach ($monthlyMovements as $monthKey => $monthMovements) {
    // Add opening balance row for this month
    if ($runningQty > 0 || $runningNilai > 0) {
        $firstDate = $monthKey . '-01';
        $dailyStock[] = [
            'tanggal' => $firstDate,
            'saldo_awal_qty' => $runningQty,
            'saldo_awal_nilai' => $runningNilai,
            'pembelian_qty' => 0,
            'pembelian_nilai' => 0,
            'produksi_qty' => 0,
            'produksi_nilai' => 0,
            'saldo_akhir_qty' => $runningQty,
            'saldo_akhir_nilai' => $runningNilai,
            'ref_type' => 'opening_balance',
            'ref_id' => '',
            'is_opening_balance' => true
        ];
    }
    
    // Process individual movements in this month
    foreach ($monthMovements as $m) {
        $dateStr = is_string($m->tanggal) ? $m->tanggal : $m->tanggal->format('Y-m-d');
        
        // No opening balance for individual transactions
        $saldoAwalQty = 0;
        $saldoAwalNilai = 0;
        
        // Process individual movement
        if ($m->direction === 'in') {
            if ($m->ref_type === 'adjustment') {
                // Adjustment goes to pembelian column (it's adding stock)
                $dailyInQty = (float)$m->qty;
                $dailyInNilai = (float)($m->total_cost ?? 0);
                $dailyOutQty = 0;
                $dailyOutNilai = 0;
            } else {
                // Regular purchase goes to pembelian column
                $dailyInQty = (float)$m->qty;
                $dailyInNilai = (float)($m->total_cost ?? 0);
                $dailyOutQty = 0;
                $dailyOutNilai = 0;
            }
        } else {
            $dailyInQty = 0;
            $dailyInNilai = 0;
            $dailyOutQty = (float)$m->qty;
            $dailyOutNilai = (float)($m->total_cost ?? 0);
        }
        
        // Update running totals
        $runningQty += $dailyInQty - $dailyOutQty;
        $runningNilai += $dailyInNilai - $dailyOutNilai;
        
        $dailyStock[] = [
            'tanggal' => $dateStr,
            'saldo_awal_qty' => $saldoAwalQty,
            'saldo_awal_nilai' => $saldoAwalNilai,
            'pembelian_qty' => $dailyInQty,
            'pembelian_nilai' => $dailyInNilai,
            'produksi_qty' => $dailyOutQty,
            'produksi_nilai' => $dailyOutNilai,
            'saldo_akhir_qty' => $runningQty,
            'saldo_akhir_nilai' => $runningNilai,
            'ref_type' => $m->ref_type,
            'ref_id' => $m->ref_id,
            'is_opening_balance' => false
        ];
    }
}

// Display results
echo "\n=== Kartu Stok Air ===\n";

foreach ($dailyStock as $index => $day) {
    $saldoAwalQtyDisplay = $day['saldo_awal_qty'];
    $pembelianQtyDisplay = $day['pembelian_qty'];
    $produksiQtyDisplay = $day['produksi_qty'];
    $saldoAkhirQtyDisplay = $day['saldo_akhir_qty'];
    
    $saldoAwalHarga = $saldoAwalQtyDisplay > 0 ? $day['saldo_awal_nilai'] / $saldoAwalQtyDisplay : 0;
    $pembelianHarga = $pembelianQtyDisplay > 0 ? $day['pembelian_nilai'] / $pembelianQtyDisplay : 0;
    $produksiHarga = $produksiQtyDisplay > 0 ? $day['produksi_nilai'] / $produksiQtyDisplay : 0;
    $saldoAkhirHarga = $saldoAkhirQtyDisplay > 0 ? $day['saldo_akhir_nilai'] / $saldoAkhirQtyDisplay : 0;
    
    echo "Tanggal: " . \Carbon\Carbon::parse($day['tanggal'])->format('d/m/Y');
    if ($day['is_opening_balance']) {
        echo " [SALDO AWAL BULAN]";
    } else {
        echo "[" . ucfirst($day['ref_type']) . " #" . $day['ref_id'] . "]";
    }
    echo "\n";
    
    echo "Stok Awal: " . number_format($saldoAwalQtyDisplay, 0, ',', '.') . " Liter @ Rp " . number_format($saldoAwalHarga, 2, ',', '.') . " = Rp " . number_format($day['saldo_awal_nilai'], 0, ',', '.') . "\n";
    echo "Pembelian: " . number_format($pembelianQtyDisplay, 0, ',', '.') . " Liter @ Rp " . number_format($pembelianHarga, 2, ',', '.') . " = Rp " . number_format($day['pembelian_nilai'], 0, ',', '.') . "\n";
    echo "Produksi: " . number_format($produksiQtyDisplay, 0, ',', '.') . " Liter @ Rp " . number_format($produksiHarga, 2, ',', '.') . " = Rp " . number_format($day['produksi_nilai'], 0, ',', '.') . "\n";
    echo "Total: " . number_format($saldoAkhirQtyDisplay, 0, ',', '.') . " Liter @ Rp " . number_format($saldoAkhirHarga, 2, ',', '.') . " = Rp " . number_format($day['saldo_akhir_nilai'], 0, ',', '.') . "\n";
    echo "\n";
}

echo "Total baris: " . count($dailyStock) . "\n";
