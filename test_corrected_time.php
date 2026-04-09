<?php

// Simple test without Laravel bootstrap
require_once 'app/Helpers/TimeHelper.php';

echo "🧪 Testing Corrected TimeHelper...\n\n";

// Get Windows system time
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
$windowsTime = !empty($output[0]) ? $output[0] : 'Unknown';

// Get PHP time
$phpTime = date('Y-m-d H:i:s');

echo "Windows system time: {$windowsTime}\n";
echo "PHP time: {$phpTime}\n";

// Test manual correction (add 5 hours to PHP time)
$phpDateTime = new DateTime();
$phpDateTime->add(new DateInterval('PT5H'));
$correctedTime = $phpDateTime->format('Y-m-d H:i:s');

echo "Corrected PHP time: {$correctedTime}\n";

// Compare with Windows time
if ($windowsTime !== 'Unknown') {
    $winTimeFormatted = str_replace('.', ':', $windowsTime);
    $winDateTime = new DateTime($winTimeFormatted);
    $correctedDateTime = new DateTime($correctedTime);
    
    $diff = $winDateTime->diff($correctedDateTime);
    
    echo "Difference after correction: ";
    if ($diff->h > 0) {
        echo "{$diff->h} hours ";
    }
    if ($diff->i > 0) {
        echo "{$diff->i} minutes ";
    }
    if ($diff->s > 0) {
        echo "{$diff->s} seconds";
    }
    echo "\n";
    
    if ($diff->h == 0 && $diff->i < 2) {
        echo "✅ Time correction is accurate!\n";
    } else {
        echo "❌ Time correction needs adjustment\n";
    }
}

echo "\n🎉 Test complete!\n";