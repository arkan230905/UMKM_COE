<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ayam = \App\Models\BahanBaku::find(2);

echo "=== Opsi Saldo Awal ===\n";
echo "1. Saldo Awal dari Master Data:\n";
echo "   - Stok: " . ($ayam->stok ?? 0) . " Ekor\n";
echo "   - Nilai: " . (($ayam->stok ?? 0) * ($ayam->harga_satuan ?? 0)) . " Ekor\n";
echo "   - Dalam Potong: " . (($ayam->stok ?? 0) * 6) . " Potong\n";
echo "   - Harga per Potong: Rp " . number_format(($ayam->harga_satuan ?? 0) / 6, 2) . "\n";

echo "\n2. Saldo Awal dari Historical Movements (sebelum 01/02/2026):\n";
$before = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->whereDate('tanggal', '<', '2026-02-01')
    ->get();

$saldoAwalQty = 0;
$saldoAwalNilai = 0;
foreach ($before as $m) {
    if ($m->direction === 'in') {
        $saldoAwalQty += $m->qty;
        $saldoAwalNilai += $m->total_cost;
    } else {
        $saldoAwalQty -= $m->qty;
        $saldoAwalNilai -= $m->total_cost;
    }
}

echo "   - Stok: $saldoAwalQty Ekor\n";
echo "   - Nilai: Rp " . number_format($saldoAwalNilai, 0) . "\n";
echo "   - Dalam Potong: " . ($saldoAwalQty * 6) . " Potong\n";
echo "   - Harga per Potong: Rp " . number_format($saldoAwalQty > 0 ? $saldoAwalNilai / ($saldoAwalQty * 6) : 0, 2) . "\n";

echo "\n3. Total Semua Movements (termasuk yang di tanggal yang dipilih):\n";
$all = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->get();

$totalQty = 0;
$totalNilai = 0;
foreach ($all as $m) {
    if ($m->direction === 'in') {
        $totalQty += $m->qty;
        $totalNilai += $m->total_cost;
    } else {
        $totalQty -= $m->qty;
        $totalNilai -= $m->total_cost;
    }
}

echo "   - Stok: $totalQty Ekor\n";
echo "   - Nilai: Rp " . number_format($totalNilai, 0) . "\n";
echo "   - Dalam Potong: " . ($totalQty * 6) . " Potong\n";
echo "   - Harga per Potong: Rp " . number_format($totalQty > 0 ? $totalNilai / ($totalQty * 6) : 0, 2) . "\n";

echo "\n=== Rekomendasi ===\n";
echo "Jika Anda ingin saldo awal 180 Potong pada 01/02/2026:\n";
echo "- Buat adjustment stock sebelum 01/02/2026 dengan 30 Ekor (180 Potong) @ Rp 45,000/Ekor\n";
echo "- Atau ubah logika untuk menggunakan stok master sebagai saldo awal\n";

echo "\nJika Anda ingin saldo awal 0 pada 01/02/2026:\n";
echo "- Data sudah benar, tidak perlu perubahan\n";
echo "- Kartu stok akan menunjukkan 0 -> 180 -> 228 -> 288 Potong\n";
