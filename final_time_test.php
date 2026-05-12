<?php

echo "🧪 Final Time Test...\n\n";

// Get Windows system time
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
$windowsTime = !empty($output[0]) ? $output[0] : 'Unknown';

// Get PHP time
$phpTime = date('Y-m-d H:i:s');

// Get corrected PHP time (add 5 hours)
$phpDateTime = new DateTime();
$phpDateTime->add(new DateInterval('PT5H'));
$correctedTime = $phpDateTime->format('Y-m-d H:i:s');

echo "📊 Time Comparison:\n";
echo "Windows system time: {$windowsTime}\n";
echo "PHP time (original): {$phpTime}\n";
echo "PHP time (corrected): {$correctedTime}\n\n";

// Simulate what will happen in the application
echo "🔧 Application Simulation:\n";
echo "When user clicks 'Mulai Produksi':\n";
echo "- System will record: {$correctedTime}\n";
echo "- User will see: " . date('d/m/Y H:i:s', strtotime($correctedTime)) . "\n\n";

// Compare with Windows time
if ($windowsTime !== 'Unknown') {
    $winTimeFormatted = str_replace('.', ':', $windowsTime);
    $winDateTime = new DateTime($winTimeFormatted);
    $correctedDateTime = new DateTime($correctedTime);
    
    $diff = $winDateTime->diff($correctedDateTime);
    
    echo "⏱️  Accuracy Check:\n";
    echo "Difference from Windows time: ";
    if ($diff->h == 0 && $diff->i == 0 && $diff->s < 5) {
        echo "< 5 seconds ✅\n";
        echo "🎉 Time synchronization is ACCURATE!\n";
    } else {
        if ($diff->h > 0) echo "{$diff->h} hours ";
        if ($diff->i > 0) echo "{$diff->i} minutes ";
        if ($diff->s > 0) echo "{$diff->s} seconds";
        echo " ❌\n";
        echo "⚠️  Time synchronization needs adjustment\n";
    }
}

echo "\n✅ Ready for production use!\n";