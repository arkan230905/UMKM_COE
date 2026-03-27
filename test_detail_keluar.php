<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\LaporanKasBankController;
use Illuminate\Http\Request;

echo "=== TESTING DETAIL KELUAR API ===\n";

// Create controller instance
$controller = new LaporanKasBankController();

// Create a mock request
$request = new Request([
    'start_date' => '2026-03-01',
    'end_date' => '2026-03-31'
]);

// Test with kode_akun "1102" (Kas di Bank)
echo "Testing with coaId = '1102' (Kas di Bank)\n";

try {
    $response = $controller->getDetailKeluar($request, '1102');
    $data = $response->getData();
    
    echo "Response received with " . count($data) . " transactions\n";
    
    if (count($data) > 0) {
        echo "First transaction:\n";
        $first = $data[0];
        echo "- Tanggal: " . $first->tanggal . "\n";
        echo "- No. Transaksi: " . $first->nomor_transaksi . "\n";
        echo "- Jenis: " . $first->jenis . "\n";
        echo "- Keterangan: " . $first->keterangan . "\n";
        echo "- Nominal: Rp " . number_format($first->nominal, 0, ',', '.') . "\n";
    } else {
        echo "No transactions found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}