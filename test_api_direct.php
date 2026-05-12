<?php
// Test API endpoint directly

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Http\Controllers\PenggajianController;

echo "=== TEST API ENDPOINT DIRECTLY ===\n\n";

// Login as User 13
$user = User::find(13);
auth()->login($user);

echo "Logged in as: {$user->name} (ID: {$user->id})\n\n";

// Test API endpoint
$controller = new PenggajianController();

echo "Testing: /transaksi/penggajian/pegawai/3/data\n";
echo "─────────────────────────────────────────\n";

try {
    $response = $controller->getEmployeeData(3);
    $data = json_decode($response->getContent(), true);
    
    echo "Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    // Check values
    echo "Verification:\n";
    if ($data['tunjangan_transport'] == 0) {
        echo "❌ tunjangan_transport is 0!\n";
    } else {
        echo "✓ tunjangan_transport: {$data['tunjangan_transport']}\n";
    }
    
    if ($data['tunjangan_konsumsi'] == 0) {
        echo "❌ tunjangan_konsumsi is 0!\n";
    } else {
        echo "✓ tunjangan_konsumsi: {$data['tunjangan_konsumsi']}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== END TEST ===\n";
