<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: Test AJAX POST to store-lainnya ===" . PHP_EOL;

// Simulate AJAX request data
$postData = [
    'kode_akun' => '503',
    'budget' => '5000000',
    'kuantitas_per_jam' => '1',
    'periode' => '2026-02',
    'keterangan' => 'Test AJAX POST'
];

echo "POST data yang akan dikirim:" . PHP_EOL;
foreach ($postData as $key => $value) {
    echo "- {$key}: {$value}" . PHP_EOL;
}

echo PHP_EOL . "=== Cek Route ===" . PHP_EOL;
$route = '/master-data/bop/store-lainnya';
echo "Route: {$route}" . PHP_EOL;

echo PHP_EOL . "=== Test CSRF Token ===" . PHP_EOL;
// Generate CSRF token untuk testing
$token = csrf_token();
echo "CSRF Token: {$token}" . PHP_EOL;

echo PHP_EOL . "=== Manual Test Store Method ===" . PHP_EOL;

// Buat request object manual
$request = new \Illuminate\Http\Request();
$request->merge($postData);
$request->headers->set('X-CSRF-TOKEN', $token);

// Test controller method
try {
    $controller = new \App\Http\Controllers\MasterData\BopController();
    
    echo "Memanggil storeLainnya()..." . PHP_EOL;
    $response = $controller->storeLainnya($request);
    
    echo "✅ Response berhasil:" . PHP_EOL;
    echo "- Status: " . $response->getStatusCode() . PHP_EOL;
    echo "- Content: " . $response->getContent() . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error saat memanggil storeLainnya():" . PHP_EOL;
    echo "- Message: {$e->getMessage()}" . PHP_EOL;
    echo "- File: {$e->getFile()}:{$e->getLine()}" . PHP_EOL;
    echo "- Trace: {$e->getTraceAsString()}" . PHP_EOL;
}
