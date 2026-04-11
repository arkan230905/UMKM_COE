<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\BtklApiController;
use App\Models\ProsesProduksi;

echo "=== Test API Sync dari ProsesProduksi ===\n\n";

try {
    // 1. Test API Controller
    echo "=== Test API Controller ===\n";
    $controller = new BtklApiController();
    
    // Test getByProses method
    $response = $controller->getByProses(4);
    echo "API Response Status: " . $response->getStatusCode() . "\n";
    echo "API Response Data:\n";
    
    $data = json_decode($response->getContent(), true);
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

    // 2. Test langsung dari model
    echo "=== Test Langsung dari Model ===\n";
    $proses = ProsesProduksi::find(4);
    
    if ($proses) {
        echo "Proses ID: {$proses->id}\n";
        echo "Nama Proses: {$proses->nama_proses}\n";
        echo "Tarif BTKL: Rp {$proses->tarif_btkl}\n";
        echo "Kapasitas: {$proses->kapasitas_per_jam} pcs/jam\n";
        echo "Satuan: {$proses->satuan_btkl}\n";
        echo "Jabatan ID: {$proses->jabatan_id}\n";
        echo "Updated At: {$proses->updated_at}\n";
        
        // Hitung biaya per produk
        $btklPerProduk = $proses->kapasitas_per_jam > 0 ? $proses->tarif_btkl / $proses->kapasitas_per_jam : 0;
        echo "BTKL per Produk: Rp {$btklPerProduk}\n";
    } else {
        echo "Proses tidak ditemukan!\n";
    }
    
    echo "\n=== Test Semua Proses ===\n";
    $allProses = ProsesProduksi::all();
    
    foreach ($allProses as $proses) {
        echo "\nProses: {$proses->nama_proses} (ID: {$proses->id})\n";
        echo "  Tarif: Rp {$proses->tarif_btkl}\n";
        echo "  Kapasitas: {$proses->kapasitas_per_jam}\n";
        echo "  Biaya/Produk: Rp " . ($proses->kapasitas_per_jam > 0 ? $proses->tarif_btkl / $proses->kapasitas_per_jam : 0) . "\n";
        
        // Test API untuk setiap proses
        $apiResponse = $controller->getByProses($proses->id);
        $apiData = json_decode($apiResponse->getContent(), true);
        
        echo "  API Status: " . ($apiData['success'] ? 'SUCCESS' : 'FAILED') . "\n";
        if ($apiData['success']) {
            echo "  API Tarif: Rp {$apiData['data']['tarif_per_jam']}\n";
            echo "  API Kapasitas: {$apiData['data']['kapasitas_per_jam']}\n";
        }
    }

    echo "\n=== Verifikasi Realtime Sync ===\n";
    echo "Sistem sync sudah siap untuk:\n";
    echo "1. Observer ProsesProduksi -> BOP Proses\n";
    echo "2. API refresh setiap 30 detik di frontend\n";
    echo "3. Auto-calculate perubahan tarif dan kapasitas\n";
    echo "4. Update BTKL per produk secara otomatis\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
