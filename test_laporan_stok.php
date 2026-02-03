<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST LAPORAN STOK CONTROLLER ===\n\n";

// Simulasi request
$request = new \Illuminate\Http\Request();
$request->merge([
    'tipe' => 'support',
    'dari_tanggal' => '2026-02-01',
    'sampai_tanggal' => '2026-02-05'
]);

// Load controller
$controller = new \App\Http\Controllers\PegawaiGudang\LaporanStokController();

try {
    echo "1. MENJALANKAN INDEX METHOD...\n";
    $response = $controller->index($request);
    echo "✅ Index method berhasil dijalankan\n";
    
    // Cek data yang dikembalikan
    $data = $response->getData();
    echo "Tipe: " . $data['tipe'] . "\n";
    echo "Dari Tanggal: " . $data['dariTanggal'] . "\n";
    echo "Sampai Tanggal: " . $data['sampaiTanggal'] . "\n";
    echo "Jumlah Laporan Stok: " . $data['laporanStok']->count() . "\n\n";
    
    if ($data['laporanStok']->count() > 0) {
        echo "DATA LAPORAN STOK:\n";
        foreach ($data['laporanStok'] as $item) {
            echo "- Item: " . $item['nama_item'] . "\n";
            echo "  Tipe: " . $item['item_type'] . "\n";
            echo "  Satuan: " . $item['satuan'] . "\n";
            echo "  Masuk Qty: " . $item['masuk_qty'] . "\n";
            echo "  Keluar Qty: " . $item['keluar_qty'] . "\n";
            echo "  Saldo Qty: " . $item['saldo_qty'] . "\n";
            echo "  Jumlah Movements: " . count($item['movements']) . "\n";
            echo "---\n";
        }
    } else {
        echo "❌ Tidak ada data laporan stok\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== SELESAI ===\n";
