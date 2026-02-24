<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo "=== DEBUG AYAM KAMPUNG ===\n";

// Get item data
$item = \App\Models\BahanBaku::find(2);
echo "Item: " . $item->nama_bahan . "\n";
echo "Stok: " . $item->stok . "\n";
echo "Satuan ID: " . $item->satuan_id . "\n";
echo "Satuan: " . $item->satuan->nama . "\n";
echo "Sub Satuan 1 ID: " . ($item->sub_satuan_1_id ?? 'null') . "\n";
echo "Sub Satuan 1 Konversi: " . ($item->sub_satuan_1_konversi ?? 'null') . "\n";
echo "Sub Satuan 1 Nilai: " . ($item->sub_satuan_1_nilai ?? 'null') . "\n";

// Get sub satuan details
if ($item->sub_satuan_1_id) {
    $sub1 = \App\Models\Satuan::find($item->sub_satuan_1_id);
    echo "Sub Satuan 1 Name: " . $sub1->nama . "\n";
}

echo "\n=== STOCK MOVEMENTS ===\n";
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

foreach ($movements as $m) {
    echo "Tanggal: " . $m->tanggal . " | Direction: " . $m->direction . " | Qty: " . $m->qty . " | Satuan: " . $m->satuan . " | Ref: " . $m->ref_type . "#" . $m->ref_id . "\n";
}

echo "\n=== CALCULATION ===\n";
$saldoAwal = (float)($item->stok ?? 0);
echo "Saldo Awal dari Master: " . $saldoAwal . "\n";

// Calculate movements before today
$today = date('Y-m-d');
$before = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->whereDate('tanggal', '<', $today)
    ->get();

$saldoAwalQty = $saldoAwal;
$saldoAwalNilai = $saldoAwalQty * (float)($item->harga_satuan ?? 0);

echo "Movements before today (" . $today . "):\n";
foreach ($before as $m) {
    if ($m->direction === 'in') {
        $saldoAwalQty += (float)$m->qty;
        $saldoAwalNilai += (float)($m->total_cost ?? 0);
        echo "  IN: +" . $m->qty . " " . $m->satuan . " -> Saldo: " . $saldoAwalQty . "\n";
    } else {
        $saldoAwalQty -= (float)$m->qty;
        $saldoAwalNilai -= (float)($m->total_cost ?? 0);
        echo "  OUT: -" . $m->qty . " " . $m->satuan . " -> Saldo: " . $saldoAwalQty . "\n";
    }
}

echo "Final Saldo Awal: " . $saldoAwalQty . "\n";
echo "Final Saldo Nilai: " . $saldoAwalNilai . "\n";
?>
