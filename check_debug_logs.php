<?php

echo "=== CHECK DEBUG LOGS ===\n\n";

$logFile = 'c:\UMKM_COE\storage\logs\laravel.log';

if (file_exists($logFile)) {
    echo "Reading Laravel log file...\n\n";
    
    // Get last 50 lines of log
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    
    echo "Last 50 lines of Laravel log:\n";
    echo "================================\n";
    
    foreach ($lastLines as $line) {
        echo $line;
    }
    
    echo "\n================================\n";
    echo "END OF LOG\n\n";
    
    // Look for our debug messages
    $debugLines = array_filter($lines, function($line) {
        return strpos($line, '=== BIAYA BAHAN CONTROLLER DEBUG ===') !== false || 
               strpos($line, 'User ID:') !== false ||
               strpos($line, 'Products found:') !== false ||
               strpos($line, 'BBB records for product') !== false ||
               strpos($line, 'Final produkBiaya count:') !== false;
    });
    
    if (count($debugLines) > 0) {
        echo "\nDEBUG MESSAGES FOUND:\n";
        echo "====================\n";
        
        foreach ($debugLines as $line) {
            echo $line;
        }
        
        echo "\n====================\n";
    } else {
        echo "\n❌ No debug messages found in log.\n";
        echo "This means:\n";
        echo "1. Either the page hasn't been visited yet\n";
        echo "2. Or the debug code wasn't inserted correctly\n";
        echo "3. Or there's an error before the debug code runs\n";
    }
    
} else {
    echo "❌ Laravel log file not found at: $logFile\n";
    echo "This means logging might not be enabled or the file path is different.\n";
}

echo "\n=== LOG CHECK COMPLETE ===\n";
