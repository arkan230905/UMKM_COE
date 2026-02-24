<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get Ayam Kampung data
$ayam = \App\Models\BahanBaku::find(2);

echo "=== Data Ayam Kampung ===\n";
echo "ID: {$ayam->id}\n";
echo "Nama: {$ayam->nama_bahan}\n";
echo "Satuan Utama: " . ($ayam->satuan->nama_satuan ?? 'N/A') . "\n";
echo "Sub Satuan 1 (Potong): " . ($ayam->sub_satuan_1->nama_satuan ?? 'N/A') . "\n";
echo "  - Konversi: " . ($ayam->sub_satuan_1_konversi ?? 'N/A') . " (primary per sub)\n";
echo "  - Nilai: " . ($ayam->sub_satuan_1_nilai ?? 'N/A') . " (sub per primary)\n";

// Get all stock movements
echo "\n=== Semua Stock Movements ===\n";
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

foreach ($movements as $m) {
    echo "Tanggal: {$m->tanggal}, Tipe: {$m->direction}, Qty: {$m->qty} {$m->satuan}, Harga: Rp " . number_format($m->unit_cost, 2) . ", Total: Rp " . number_format($m->total_cost, 0) . "\n";
}

// Check if there are any movements before our test data
echo "\n=== Periksa Data Sebelum 01/02/2026 ===\n";
$before = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->whereDate('tanggal', '<', '2026-02-01')
    ->get();

if ($before->isEmpty()) {
    echo "Tidak ada movement sebelum 01/02/2026\n";
    echo "Ini berarti saldo awal harus 0\n";
} else {
    echo "Ada movement sebelum 01/02/2026:\n";
    foreach ($before as $m) {
        echo "- {$m->tanggal}: {$m->direction} {$m->qty} {$m->satuan}\n";
    }
}

// Check master stock data
echo "\n=== Master Stock Data ===\n";
echo "Stok di master: " . ($ayam->stok ?? 0) . " " . ($ayam->satuan->nama_satuan ?? 'Ekor') . "\n";
echo "Harga satuan: Rp " . number_format($ayam->harga_satuan ?? 0, 2) . "\n";

// Calculate what the stock card SHOULD show
echo "\n=== Perhitungan Manual Kartu Stok ===\n";

// Start from 0 (no movements before 01/02)
$runningQty = 0;
$runningNilai = 0;

// Group by date
$byDate = [];
foreach ($movements as $m) {
    $date = $m->tanggal;
    if (!isset($byDate[$date])) {
        $byDate[$date] = [
            'in_qty' => 0,
            'in_nilai' => 0,
            'out_qty' => 0,
            'out_nilai' => 0
        ];
    }
    
    if ($m->direction === 'in') {
        $byDate[$date]['in_qty'] += $m->qty;
        $byDate[$date]['in_nilai'] += $m->total_cost;
    } else {
        $byDate[$date]['out_qty'] += $m->qty;
        $byDate[$date]['out_nilai'] += $m->total_cost;
    }
}

// Show each day
foreach ($byDate as $date => $data) {
    $saldoAwalQty = $runningQty;
    $saldoAwalNilai = $runningNilai;
    
    $runningQty += $data['in_qty'] - $data['out_qty'];
    $runningNilai += $data['in_nilai'] - $data['out_nilai'];
    
    // Convert to Potong
    $potongConversion = $ayam->sub_satuan_1_nilai ?? 6; // 6 potong per ekor
    
    $saldoAwalPotong = $saldoAwalQty * $potongConversion;
    $pembelianPotong = $data['in_qty'] * $potongConversion;
    $saldoAkhirPotong = $runningQty * $potongConversion;
    
    $saldoAwalHarga = $saldoAwalPotong > 0 ? $saldoAwalNilai / $saldoAwalPotong : 0;
    $pembelianHarga = $pembelianPotong > 0 ? $data['in_nilai'] / $pembelianPotong : 0;
    $saldoAkhirHarga = $saldoAkhirPotong > 0 ? $runningNilai / $saldoAkhirPotong : 0;
    
    echo "\nTanggal: " . \Carbon\Carbon::parse($date)->format('d/m/Y') . "\n";
    echo "Saldo Awal: " . number_format($saldoAwalPotong, 0) . " Potong @ Rp " . number_format($saldoAwalHarga, 2) . " = Rp " . number_format($saldoAwalNilai, 0) . "\n";
    echo "Pembelian: " . number_format($pembelianPotong, 0) . " Potong @ Rp " . number_format($pembelianHarga, 2) . " = Rp " . number_format($data['in_nilai'], 0) . "\n";
    echo "Saldo Akhir: " . number_format($saldoAkhirPotong, 0) . " Potong @ Rp " . number_format($saldoAkhirHarga, 2) . " = Rp " . number_format($runningNilai, 0) . "\n";
}
