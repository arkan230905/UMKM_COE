<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG: Grep ExpensePayment from Logs ===" . PHP_EOL;

$logPath = storage_path('logs/laravel.log');

if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    
    // Look for ExpensePayment entries
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        if (strpos($line, 'ExpensePayment') !== false) {
            echo $line . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Looking for recent errors ===" . PHP_EOL;
    
    // Look for recent errors (last 20 lines with ERROR)
    $allLines = array_reverse($lines);
    $count = 0;
    
    foreach ($allLines as $line) {
        if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
            echo $line . PHP_EOL;
            $count++;
            if ($count >= 20) break;
        }
    }
} else {
    echo "Laravel log file not found at: {$logPath}" . PHP_EOL;
}
