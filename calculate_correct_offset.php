<?php

echo "🧪 Calculating correct time offset...\n\n";

// Get Windows system time
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
$windowsTime = !empty($output[0]) ? $output[0] : 'Unknown';

// Get PHP time
$phpTime = date('Y-m-d H:i:s');

// Current application time (with +5 hours correction)
$phpDateTime = new DateTime();
$phpDateTime->add(new DateInterval('PT5H'));
$currentAppTime = $phpDateTime->format('Y-m-d H:i:s');

echo "📊 Time Comparison:\n";
echo "Windows system time: {$windowsTime}\n";
echo "PHP time (original): {$phpTime}\n";
echo "App time (current +5h): {$currentAppTime}\n\n";

if ($windowsTime !== 'Unknown') {
    $winTimeFormatted = str_replace('.', ':', $windowsTime);
    $winDateTime = new DateTime($winTimeFormatted);
    $phpOriginalDateTime = new DateTime($phpTime);
    
    // Calculate the actual offset needed
    $diff = $winDateTime->diff($phpOriginalDateTime);
    $offsetHours = $diff->h;
    if ($diff->invert == 0) {
        $offsetHours = -$offsetHours;
    }
    
    echo "🔧 Correct Offset Calculation:\n";
    echo "Difference: {$diff->h} hours {$diff->i} minutes\n";
    echo "Direction: " . ($diff->invert ? "Windows ahead" : "PHP ahead") . "\n";
    echo "Correct offset needed: +{$offsetHours} hours\n\n";
    
    // Test with correct offset
    $correctDateTime = new DateTime($phpTime);
    $correctDateTime->add(new DateInterval("PT{$offsetHours}H"));
    $correctedTime = $correctDateTime->format('Y-m-d H:i:s');
    
    echo "✅ Corrected time: {$correctedTime}\n";
    
    // Compare with Windows time
    $correctedDateTime = new DateTime($correctedTime);
    $finalDiff = $winDateTime->diff($correctedDateTime);
    
    echo "Final difference: {$finalDiff->h}h {$finalDiff->i}m {$finalDiff->s}s\n";
    
    if ($finalDiff->h == 0 && $finalDiff->i < 2) {
        echo "🎉 Offset calculation is ACCURATE!\n";
        echo "📝 Use offset: +{$offsetHours} hours instead of +5 hours\n";
    } else {
        echo "❌ Offset calculation needs adjustment\n";
    }
}

echo "\n🎉 Calculation complete!\n";