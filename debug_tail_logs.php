<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: Tail Laravel Logs (Last 100 lines) ===" . PHP_EOL;

$logPath = storage_path('logs/laravel.log');

if (file_exists($logPath)) {
    // Read last 100 lines
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lastLines = array_slice($lines, -100);
    
    foreach ($lastLines as $line) {
        echo $line . PHP_EOL;
    }
} else {
    echo "Laravel log file not found at: {$logPath}" . PHP_EOL;
}
