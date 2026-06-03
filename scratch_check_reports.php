<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\Produk;
use Illuminate\Http\Request;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Auth;

function checkReportForUserAndProduct($userId, $productId) {
    $user = User::find($userId);
    Auth::guard('web')->login($user);
    
    echo "=== USER ID: {$userId} ({$user->name}) | PRODUCT ID: {$productId} ===\n";
    
    $request = new Request([
        'tipe' => 'product',
        'item_id' => $productId
    ]);
    
    $controller = new LaporanController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('stok');
    $method->setAccessible(true);
    
    $response = $method->invoke($controller, $request);
    $viewData = $response->getData();
    
    if (isset($viewData['dailyStock']) && !empty($viewData['dailyStock'])) {
        $dailyStock = $viewData['dailyStock'];
        $lastTransaction = end($dailyStock);
        echo "Last Transaction: " . json_encode($lastTransaction) . "\n";
    } else {
        echo "No stock report data found!\n";
    }
    echo "\n";
}

checkReportForUserAndProduct(1, 1);
checkReportForUserAndProduct(4, 2);
