<?php

echo "🧪 Simple Time Test...\n\n";

// Get Windows system time
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
$windowsTime = !empty($output[0]) ? $output[0] : 'Unknown';

// Get PHP time
$phpTime = date('Y-m-d H:i:s');

echo "Windows system time: {$windowsTime}\n";
echo "PHP time: {$phpTime}\n";

// Parse times to calculate difference
if ($windowsTime !== 'Unknown') {
    $winTimeFormatted = str_replace('.', ':', $windowsTime);
    $winDateTime = new DateTime($winTimeFormatted);
    $phpDateTime = new DateTime($phpTime);
    
    $diff = $winDateTime->diff($phpDateTime);
    
    echo "Time difference: ";
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
    
    // Calculate corrected time (add the difference)
    $correctedTime = $phpDateTime->add($diff);
    echo "Corrected PHP time: " . $correctedTime->format('Y-m-d H:i:s') . "\n";
    
    // Show the offset needed
    $offsetHours = $diff->h;
    if ($diff->invert) {
        $offsetHours = -$offsetHours;
    }
    echo "Offset needed: +{$offsetHours} hours\n";
}

echo "\n🎉 Test complete!\n";