<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo "=== Data Ayam Kampung ===\n";
$item = \App\Models\BahanBaku::find(2);
echo "ID: 2, Nama: " . $item->nama_bahan . "\n";
echo "Stok: " . $item->stok . "\n";
echo "Satuan ID: " . $item->satuan_id . "\n";
echo "Satuan: " . $item->satuan->nama . "\n";
echo "Sub Satuan 1 ID: " . $item->sub_satuan_1_id . "\n";
echo "Sub Satuan 1 Konversi: " . $item->sub_satuan_1_konversi . "\n";
echo "Sub Satuan 1 Nilai: " . $item->sub_satuan_1_nilai . "\n";
echo "Sub Satuan 2 ID: " . $item->sub_satuan_2_id . "\n";
echo "Sub Satuan 2 Konversi: " . $item->sub_satuan_2_konversi . "\n";
echo "Sub Satuan 2 Nilai: " . $item->sub_satuan_2_nilai . "\n";
echo "Sub Satuan 3 ID: " . $item->sub_satuan_3_id . "\n";
echo "Sub Satuan 3 Konversi: " . $item->sub_satuan_3_konversi . "\n";
echo "Sub Satuan 3 Nilai: " . $item->sub_satuan_3_nilai . "\n";

echo "\n=== Stock Movements ===\n";
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 2)
    ->orderBy('tanggal', 'asc')
    ->get();
    
foreach ($movements as $m) {
    echo "Tanggal: " . $m->tanggal . ", Direction: " . $m->direction . ", Qty: " . $m->qty . ", Satuan: " . $m->satuan . "\n";
}
?>
