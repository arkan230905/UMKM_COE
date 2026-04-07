<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Checking process data in database...\n\n";

$produksi = \App\Models\Produksi::find(8);
$proses = $produksi->proses()->where('nama_proses', 'Pengemasan')->first();

if (!$proses) {
    echo "❌ Pengemasan process not found\n";
    exit;
}

echo "📋 Pengemasan Process Data:\n";
echo "Status: {$proses->status}\n";
echo "Waktu Mulai (raw): {$proses->waktu_mulai}\n";
echo "Waktu Mulai (formatted): " . $proses->waktu_mulai->format('d/m/Y H:i:s') . "\n";
echo "Timezone: " . $proses->waktu_mulai->timezone->getName() . "\n\n";

// Get current time for comparison
echo "🕐 Current Time Comparison:\n";
echo "Current server time: " . now()->format('Y-m-d H:i:s') . "\n";
echo "Current corrected time: " . now()->addHours(5)->format('Y-m-d H:i:s') . "\n";

// Get Windows time
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
$windowsTime = !empty($output[0]) ? $output[0] : 'Unknown';
echo "Windows time: {$windowsTime}\n\n";

// Check when the process was actually started
echo "🔍 Process Start Analysis:\n";
$processStartTime = $proses->waktu_mulai;
$currentTime = now();

echo "Process started at: " . $processStartTime->format('Y-m-d H:i:s') . "\n";
echo "Current Laravel time: " . $currentTime->format('Y-m-d H:i:s') . "\n";

$diff = $currentTime->diff($processStartTime);
echo "Time difference: ";
if ($diff->invert) {
    echo "Process started {$diff->h}h {$diff->i}m {$diff->s}s ago\n";
} else {
    echo "Process will start in {$diff->h}h {$diff->i}m {$diff->s}s (FUTURE TIME!)\n";
}

echo "\n🎉 Check complete!\n";