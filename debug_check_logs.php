<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: Check Laravel Logs ===" . PHP_EOL;

$logPath = storage_path('logs/laravel.log');

if (file_exists($logPath)) {
    $logs = file_get_contents($logPath);
    
    // Get last 50 lines
    $lines = explode("\n", $logs);
    $lastLines = array_slice($lines, -50);
    
    echo "Last 50 lines from laravel.log:" . PHP_EOL;
    echo str_repeat("=", 80) . PHP_EOL;
    
    foreach ($lastLines as $line) {
        if (strpos($line, 'ExpensePayment') !== false || 
            strpos($line, 'ERROR') !== false || 
            strpos($line, 'Exception') !== false) {
            echo $line . PHP_EOL;
        }
    }
} else {
    echo "Laravel log file not found at: {$logPath}" . PHP_EOL;
}

echo PHP_EOL . "=== Check Recent Log Entries ===" . PHP_EOL;

// Alternative: Check if we can read recent entries
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    
    // Look for today's logs
    $today = date('Y-m-d');
    $pattern = "/\[" . preg_quote($today) . ".*?\]/";
    
    if (preg_match_all($pattern, $content, $matches)) {
        echo "Found " . count($matches[0]) . " log entries for today" . PHP_EOL;
    }
}
