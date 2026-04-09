<?php

echo "🧪 Testing final time synchronization...\n\n";

// Get Windows system time
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
$windowsTime = !empty($output[0]) ? $output[0] : 'Unknown';

// Get what the server will show (PHP + 5 hours)
$phpDateTime = new DateTime();
$phpDateTime->add(new DateInterval('PT5H'));
$serverTime = $phpDateTime->format('Y-m-d H:i:s');

// Get what JavaScript will show (browser time with Asia/Jakarta timezone)
$jsTime = date('Y-m-d H:i:s'); // This simulates browser time

echo "📊 Final Time Comparison:\n";
echo "Windows system time: {$windowsTime}\n";
echo "Server time (PHP +5h): {$serverTime}\n";
echo "JavaScript time (no correction): {$jsTime}\n\n";

// Format for display like in the application
$displayTime = date('d/m/Y H:i:s', strtotime($serverTime));
echo "🖥️  Application will show:\n";
echo "Server-side time: {$displayTime}\n";

// Simulate JavaScript display (browser local time with Asia/Jakarta)
$jsDisplayTime = date('d/m/Y H:i:s');
echo "Client-side time: {$jsDisplayTime}\n\n";

if ($windowsTime !== 'Unknown') {
    $winTimeFormatted = str_replace('.', ':', $windowsTime);
    $winDateTime = new DateTime($winTimeFormatted);
    $serverDateTime = new DateTime($serverTime);
    
    $diff = $winDateTime->diff($serverDateTime);
    
    echo "⏱️  Accuracy Check:\n";
    echo "Server vs Windows: ";
    if ($diff->h == 0 && $diff->i < 2) {
        echo "< 2 minutes ✅\n";
        echo "🎉 Server time is ACCURATE!\n";
    } else {
        echo "{$diff->h}h {$diff->i}m {$diff->s}s ❌\n";
        echo "⚠️  Server time needs adjustment\n";
    }
}

echo "\n✅ Time synchronization test complete!\n";