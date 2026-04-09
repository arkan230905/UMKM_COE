<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Checking Laravel logs for PembelianObserver activity...\n\n";

$logFile = 'storage/logs/laravel.log';

if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    
    // Look for pembelian-related logs
    $lines = explode("\n", $logs);
    $relevantLogs = [];
    
    foreach ($lines as $line) {
        if (stripos($line, 'pembelian') !== false || 
            stripos($line, 'journal') !== false ||
            stripos($line, 'purchase') !== false) {
            $relevantLogs[] = $line;
        }
    }
    
    if (count($relevantLogs) > 0) {
        echo "Found " . count($relevantLogs) . " relevant log entries:\n\n";
        
        // Show last 20 entries
        $recentLogs = array_slice($relevantLogs, -20);
        foreach ($recentLogs as $log) {
            echo $log . "\n";
        }
    } else {
        echo "No pembelian/journal related logs found.\n";
    }
} else {
    echo "Log file not found: $logFile\n";
}

echo "\n\nChecking if PembelianObserver is registered...\n";

$observers = \Illuminate\Support\Facades\Event::getListeners('eloquent.created: App\Models\Pembelian');
echo "Observers for Pembelian created event: " . count($observers) . "\n";

$observersUpdated = \Illuminate\Support\Facades\Event::getListeners('eloquent.updated: App\Models\Pembelian');
echo "Observers for Pembelian updated event: " . count($observersUpdated) . "\n";

$observersDeleted = \Illuminate\Support\Facades\Event::getListeners('eloquent.deleted: App\Models\Pembelian');
echo "Observers for Pembelian deleted event: " . count($observersDeleted) . "\n";

echo "\nChecking if Pembelian model has observers registered...\n";

$pembelianModel = new \App\Models\Pembelian();
$reflection = new ReflectionClass($pembelianModel);
$properties = $reflection->getStaticProperties();

if (isset($properties['observers'])) {
    echo "Observers found in model:\n";
    print_r($properties['observers']);
} else {
    echo "No static observers property found.\n";
}

echo "\nChecking AppServiceProvider for observer registration...\n";

$providerFile = 'app/Providers/AppServiceProvider.php';
if (file_exists($providerFile)) {
    $content = file_get_contents($providerFile);
    if (strpos($content, 'PembelianObserver') !== false) {
        echo "PembelianObserver found in AppServiceProvider\n";
        echo "Content:\n";
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if (strpos($line, 'PembelianObserver') !== false) {
                echo "  " . trim($line) . "\n";
            }
        }
    } else {
        echo "PembelianObserver NOT found in AppServiceProvider\n";
    }
} else {
    echo "AppServiceProvider not found\n";
}

echo "\nDone.\n";
