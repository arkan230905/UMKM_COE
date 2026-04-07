<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🕐 Checking server time configuration...\n\n";

echo "📊 Time Information:\n";
echo "PHP date_default_timezone_get(): " . date_default_timezone_get() . "\n";
echo "Laravel config('app.timezone'): " . config('app.timezone') . "\n";
echo "PHP date('Y-m-d H:i:s'): " . date('Y-m-d H:i:s') . "\n";
echo "Laravel now(): " . now() . "\n";
echo "Laravel now()->format('d/m/Y H:i:s'): " . now()->format('d/m/Y H:i:s') . "\n";
echo "Carbon::now('Asia/Jakarta'): " . \Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s') . "\n\n";

echo "🌍 System Information:\n";
echo "Server OS: " . PHP_OS . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";

// Check if we can get system time
if (function_exists('exec')) {
    echo "\n⏰ System Time Commands:\n";
    
    // Windows commands
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec('date /t', $dateOutput);
        exec('time /t', $timeOutput);
        echo "Windows date: " . implode(' ', $dateOutput) . "\n";
        echo "Windows time: " . implode(' ', $timeOutput) . "\n";
        
        // Get timezone info
        exec('tzutil /g', $timezoneOutput);
        echo "Windows timezone: " . implode(' ', $timezoneOutput) . "\n";
    } else {
        // Linux/Unix commands
        exec('date', $dateOutput);
        exec('timedatectl', $timedatectlOutput);
        echo "System date: " . implode(' ', $dateOutput) . "\n";
        if (!empty($timedatectlOutput)) {
            echo "Timedatectl info:\n" . implode("\n", $timedatectlOutput) . "\n";
        }
    }
} else {
    echo "exec() function not available\n";
}

echo "\n🔧 Recommendations:\n";
$currentHour = (int)now()->format('H');
$expectedHour = 10; // Based on user's report

if ($currentHour !== $expectedHour) {
    $diff = $expectedHour - $currentHour;
    echo "❌ Server time is off by approximately {$diff} hours\n";
    echo "📝 Solutions:\n";
    echo "1. Sync server time with NTP server\n";
    echo "2. Check system timezone configuration\n";
    echo "3. Restart web server after time sync\n";
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        echo "\n🪟 Windows Commands:\n";
        echo "- Sync time: w32tm /resync\n";
        echo "- Set timezone: tzutil /s \"SE Asia Standard Time\"\n";
    } else {
        echo "\n🐧 Linux Commands:\n";
        echo "- Sync time: sudo ntpdate -s time.nist.gov\n";
        echo "- Set timezone: sudo timedatectl set-timezone Asia/Jakarta\n";
    }
} else {
    echo "✅ Server time appears to be correct\n";
}

echo "\n🎉 Time check complete!\n";