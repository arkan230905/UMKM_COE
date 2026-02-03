<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST LAPORAN DENGAN FORMAT TANGGAL ===\n\n";

// Simulasi request dengan format dd/mm/yyyy
$request = new \Illuminate\Http\Request();
$request->merge([
    'tipe' => 'support',
    'dari_tanggal' => '01/02/2026',  // Format dd/mm/yyyy
    'sampai_tanggal' => '05/02/2026'   // Format dd/mm/yyyy
]);

echo "Request data:\n";
echo "- Tipe: " . $request->get('tipe') . "\n";
echo "- Dari Tanggal: " . $request->get('dari_tanggal') . "\n";
echo "- Sampai Tanggal: " . $request->get('sampai_tanggal') . "\n\n";

// Load controller
$controller = new \App\Http\Controllers\PegawaiGudang\LaporanStokController();

try {
    echo "Menjalankan index method...\n";
    $response = $controller->index($request);
    
    // Cek data yang dikembalikan
    $data = $response->getData();
    echo "✅ Index method berhasil\n";
    echo "- Tipe: " . $data['tipe'] . "\n";
    echo "- Dari Tanggal: " . $data['dariTanggal'] . "\n";
    echo "- Sampai Tanggal: " . $data['sampaiTanggal'] . "\n";
    echo "- Jumlah Laporan Stok: " . $data['laporanStok']->count() . "\n\n";
    
    if ($data['laporanStok']->count() > 0) {
        echo "DATA DITEMUKAN:\n";
        foreach ($data['laporanStok'] as $item) {
            echo "- Item: " . $item['nama_item'] . "\n";
            echo "  Masuk Qty: " . $item['masuk_qty'] . "\n";
            echo "  Keluar Qty: " . $item['keluar_qty'] . "\n";
            echo "  Saldo Qty: " . $item['saldo_qty'] . "\n";
        }
    } else {
        echo "❌ Tidak ada data laporan stok\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== SELESAI ===\n";
