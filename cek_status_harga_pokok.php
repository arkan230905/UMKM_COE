<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK STATUS HARGA POKOK KETAMPLING ===" . PHP_EOL;

// 1. Cek data produk ketempling
$produk = \App\Models\Produk::find(3);
if (!$produk) {
    echo "Produk ketempling tidak ditemukan!" . PHP_EOL;
    exit;
}

echo "Produk: {$produk->nama_produk}" . PHP_EOL;
echo "Harga BOM: Rp " . number_format($produk->harga_bom ?? 0, 2, ',', '.') . PHP_EOL;
echo "Harga Pokok: Rp " . number_format($produk->harga_pokok ?? 0, 2, ',', '.') . PHP_EOL;
echo "Biaya Bahan: Rp " . number_format($produk->biaya_bahan ?? 0, 2, ',', '.') . PHP_EOL;
echo PHP_EOL;

// 2. Cek BomJobCosting
$bomJobCosting = \App\Models\BomJobCosting::where('produk_id', 3)->first();
if (!$bomJobCosting) {
    echo "❌ BomJobCosting tidak ditemukan!" . PHP_EOL;
    echo "Status: Harga Pokok Produksi Belum Lengkap" . PHP_EOL;
    echo "Kolom kosong: BomJobCosting" . PHP_EOL;
    exit;
}

echo "BomJobCosting ID: {$bomJobCosting->id}" . PHP_EOL;
echo "Total BBB: Rp " . number_format($bomJobCosting->total_bbb, 2, ',', '.') . PHP_EOL;
echo "Total BTKL: Rp " . number_format($bomJobCosting->total_btkl, 2, ',', '.') . PHP_EOL;
echo "Total BOP: Rp " . number_format($bomJobCosting->total_bop, 2, ',', '.') . PHP_EOL;
echo "Total HPP: Rp " . number_format($bomJobCosting->total_hpp, 2, ',', '.') . PHP_EOL;
echo PHP_EOL;

// 3. Cek detail masing-masing komponen
echo "CEK DETAIL KOMPONEN:" . PHP_EOL;

// Cek BBB
$bbbCount = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
    ->where('harga_satuan', '>', 0)
    ->count();
$bbbTotal = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
    ->sum('subtotal');
echo "- BBB: {$bbbCount} item, Total: Rp " . number_format($bbbTotal, 2, ',', '.') . PHP_EOL;

// Cek BTKL
$btklCount = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)->count();
$btklTotal = \App\Models\BomJobBTKL::where('bom_job_costing_id', $bomJobCosting->id)
    ->sum('subtotal');
echo "- BTKL: {$btklCount} item, Total: Rp " . number_format($btklTotal, 2, ',', '.') . PHP_EOL;

// Cek BOP
$bopCount = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)->count();
$bopTotal = \App\Models\BomJobBOP::where('bom_job_costing_id', $bomJobCosting->id)
    ->sum('subtotal');
echo "- BOP: {$bopCount} item, Total: Rp " . number_format($bopTotal, 2, ',', '.') . PHP_EOL;

echo PHP_EOL;

// 4. Logic pengecekan status (simulasi seperti di web)
echo "LOGIC PENGECEKAN STATUS:" . PHP_EOL;

$missingComponents = [];
$isComplete = true;

// Cek BomJobCosting
if (!$bomJobCosting) {
    $missingComponents[] = 'BomJobCosting';
    $isComplete = false;
}

// Cek BBB
if ($bbbCount == 0 || $bbbTotal == 0) {
    $missingComponents[] = 'Biaya Bahan';
    $isComplete = false;
}

// Cek BTKL
if ($btklCount == 0 || $btklTotal == 0) {
    $missingComponents[] = 'Biaya Tenaga Kerja Langsung';
    $isComplete = false;
}

// Cek BOP
if ($bopCount == 0 || $bopTotal == 0) {
    $missingComponents[] = 'Biaya Overhead Pabrik';
    $isComplete = false;
}

// Hasil pengecekan
if ($isComplete) {
    echo "✅ Status: Produk Sudah Memiliki Harga Pokok Produksi" . PHP_EOL;
    echo "   Semua komponen lengkap" . PHP_EOL;
} else {
    echo "❌ Status: Harga Pokok Produksi Belum Lengkap" . PHP_EOL;
    echo "   Kolom kosong: " . implode(', ', $missingComponents) . PHP_EOL;
}

echo PHP_EOL;

// 5. Cek apakah ada BBB yang harga_satuan = 0 (deleted)
echo "CEK BBB YANG DIHAPUS:" . PHP_EOL;
$deletedBBB = \App\Models\BomJobBBB::where('bom_job_costing_id', $bomJobCosting->id)
    ->where('harga_satuan', 0)
    ->get();

if ($deletedBBB->count() > 0) {
    echo "Ditemukan {$deletedBBB->count()} BBB yang dihapus:" . PHP_EOL;
    foreach ($deletedBBB as $bbb) {
        echo "- ID: {$bbb->id}, Nama terhapus: " . ($bbb->nama_bahan_terhapus ?? 'NULL') . PHP_EOL;
    }
    echo "Ini mungkin menyebabkan BBB dianggap tidak ada" . PHP_EOL;
} else {
    echo "Tidak ada BBB yang dihapus" . PHP_EOL;
}

// 6. Rekomendasi perbaikan
echo PHP_EOL . "REKOMENDASI:" . PHP_EOL;

if ($bbbCount == 0) {
    echo "❌ BBB kosong - Perlu ditambahkan bahan baku" . PHP_EOL;
} elseif ($bbbTotal == 0) {
    echo "❌ BBB total 0 - Perlu dicek ulang harganya" . PHP_EOL;
} else {
    echo "✅ BBB OK" . PHP_EOL;
}

if ($btklCount == 0) {
    echo "❌ BTKL kosong - Perlu ditambahkan proses BTKL" . PHP_EOL;
} else {
    echo "✅ BTKL OK ({$btklCount} item)" . PHP_EOL;
}

if ($bopCount == 0) {
    echo "❌ BOP kosong - Perlu ditambahkan komponen BOP" . PHP_EOL;
} else {
    echo "✅ BOP OK ({$bopCount} item)" . PHP_EOL;
}
