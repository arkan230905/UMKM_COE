<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Dashboard dengan Logs ===\n";

// Simulate complete dashboard index method
$dashboard = new \App\Http\Controllers\DashboardController();

// Call index method
try {
    $result = $dashboard->index();
    
    echo "✅ Dashboard index() executed successfully\n";
    echo "Total Kas & Bank: Rp " . number_format($result['totalKasBank'], 3) . "\n";
    
    if (isset($result['kasBankDetails'])) {
        echo "Kas & Bank Details:\n";
        foreach ($result['kasBankDetails'] as $detail) {
            echo "- {$detail['nama_akun']} ({$detail['kode_akun']}): Rp " . number_format($detail['saldo'], 3) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== Check Laravel Logs ===\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = substr($logs, -2000); // Last 2000 characters
    
    echo "Recent logs:\n";
    echo $recentLogs;
} else {
    echo "Log file not found\n";
}
