<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Final Data Riil ===\n";

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

// Get movements
$movements = \App\Models\StockMovement::where('item_type', $tipe)
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

// Display results in Potong
echo "\n=== Kartu Stok Ayam Kampung (Satuan Potong) ===\n";
echo "Data Riil Sesuai Permintaan:\n\n";

$potongPerEkor = $item->sub_satuan_1_nilai ?? 6;

foreach ($dailyStock as $index => $day) {
    $saldoAwalPotong = $day['saldo_awal_qty'] * $potongPerEkor;
    $pembelianPotong = $day['pembelian_qty'] * $potongPerEkor;
    $produksiPotong = $day['produksi_qty'] * $potongPerEkor;
    $saldoAkhirPotong = $day['saldo_akhir_qty'] * $potongPerEkor;
    
    $saldoAwalHarga = $saldoAwalPotong > 0 ? $day['saldo_awal_nilai'] / $saldoAwalPotong : 0;
    $pembelianHarga = $pembelianPotong > 0 ? $day['pembelian_nilai'] / $pembelianPotong : 0;
    $produksiHarga = $produksiPotong > 0 ? $day['produksi_nilai'] / $produksiPotong : 0;
    $saldoAkhirHarga = $saldoAkhirPotong > 0 ? $day['saldo_akhir_nilai'] / $saldoAkhirPotong : 0;
    
    echo "Tanggal: " . \Carbon\Carbon::parse($day['tanggal'])->format('d/m/Y');
    if ($day['is_opening_balance']) {
        echo " [SALDO AWAL BULAN]";
    } else {
        echo "[" . ucfirst($day['ref_type']) . " #" . $day['ref_id'] . "]";
    }
    echo "\n";
    
    echo "Stok Awal: " . number_format($saldoAwalPotong, 0) . " Potong @ Rp " . number_format($saldoAwalHarga, 2) . " = Rp " . number_format($day['saldo_awal_nilai'], 0) . "\n";
    echo "Pembelian: " . number_format($pembelianPotong, 0) . " Potong @ Rp " . number_format($pembelianHarga, 2) . " = Rp " . number_format($day['pembelian_nilai'], 0) . "\n";
    echo "Produksi: " . number_format($produksiPotong, 0) . " Potong @ Rp " . number_format($produksiHarga, 2) . " = Rp " . number_format($day['produksi_nilai'], 0) . "\n";
    echo "Total: " . number_format($saldoAkhirPotong, 0) . " Potong @ Rp " . number_format($saldoAkhirHarga, 2) . " = Rp " . number_format($day['saldo_akhir_nilai'], 0) . "\n";
    echo "\n";
}

echo "Total baris: " . count($dailyStock) . "\n";
