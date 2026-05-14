<?php
// Test API endpoint directly

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Http\Controllers\PenggajianController;

echo "=== TEST API ENDPOINT ===\n\n";

// Login as User 13
$user = User::find(13);
auth()->login($user);

echo "Logged in as: {$user->name} (ID: {$user->id})\n\n";

// Test API endpoint
$controller = new PenggajianController();

// Test with pegawai ID 3
echo "Testing API endpoint with pegawai ID 3:\n";
$response = $controller->getEmployeeData(3);
$data = json_decode($response->getContent(), true);

echo "Response:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Check if tunjangan values are there
if (isset($data['tunjangan_transport']) && $data['tunjangan_transport'] > 0) {
    echo "✓ Tunjangan Transport: {$data['tunjangan_transport']}\n";
} else {
    echo "✗ Tunjangan Transport is 0 or missing!\n";
}

if (isset($data['tunjangan_konsumsi']) && $data['tunjangan_konsumsi'] > 0) {
    echo "✓ Tunjangan Konsumsi: {$data['tunjangan_konsumsi']}\n";
} else {
    echo "✗ Tunjangan Konsumsi is 0 or missing!\n";
}

echo "\n=== END TEST ===\n";
