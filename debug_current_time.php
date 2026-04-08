<?php

echo "🔍 Debugging current time issue...\n\n";

// Get Windows system time
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
$windowsTime = !empty($output[0]) ? $output[0] : 'Unknown';

// Get PHP time
$phpTime = date('Y-m-d H:i:s');

// Get corrected time (+5 hours)
$phpDateTime = new DateTime();
$phpDateTime->add(new DateInterval('PT5H'));
$correctedTime = $phpDateTime->format('Y-m-d H:i:s');

echo "📊 Current Time Analysis:\n";
echo "Windows system time: {$windowsTime}\n";
echo "PHP time (original): {$phpTime}\n";
echo "PHP time (+5h correction): {$correctedTime}\n\n";

// Calculate what the correct offset should be
if ($windowsTime !== 'Unknown') {
    $winTimeFormatted = str_replace('.', ':', $windowsTime);
    $winDateTime = new DateTime($winTimeFormatted);
    $phpOriginalDateTime = new DateTime($phpTime);
    
    $diff = $winDateTime->diff($phpOriginalDateTime);
    $actualOffsetNeeded = $diff->h;
    
    echo "🔧 Correct Offset Analysis:\n";
    echo "Difference: {$diff->h} hours {$diff->i} minutes\n";
    echo "Current offset used: +5 hours\n";
    echo "Correct offset needed: +{$actualOffsetNeeded} hours\n\n";
    
    // Test with correct offset
    $correctDateTime = new DateTime($phpTime);
    $correctDateTime->add(new DateInterval("PT{$actualOffsetNeeded}H"));
    $properCorrectedTime = $correctDateTime->format('Y-m-d H:i:s');
    
    echo "✅ Proper corrected time: {$properCorrectedTime}\n";
    
    // Compare with Windows
    $properDateTime = new DateTime($properCorrectedTime);
    $finalDiff = $winDateTime->diff($properDateTime);
    
    echo "Final difference: {$finalDiff->h}h {$finalDiff->i}m {$finalDiff->s}s\n";
    
    if ($finalDiff->h == 0 && $finalDiff->i < 2) {
        echo "🎉 This offset would be ACCURATE!\n";
        echo "📝 Recommendation: Use +{$actualOffsetNeeded} hours instead of +5 hours\n";
    }
}

echo "\n🎉 Debug complete!\n";