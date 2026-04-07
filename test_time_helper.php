<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Testing TimeHelper...\n\n";

echo "⏰ Time Comparison:\n";
echo "Windows system time: ";
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
if (!empty($output[0])) {
    echo $output[0] . "\n";
}

echo "Laravel now(): " . now()->format('Y-m-d H:i:s') . "\n";
echo "TimeHelper::now(): " . \App\Helpers\TimeHelper::now()->format('Y-m-d H:i:s') . "\n";
echo "TimeHelper::formatForDisplay(): " . \App\Helpers\TimeHelper::formatForDisplay() . "\n\n";

echo "🔧 Testing process timing with corrected time:\n";

// Test with a sample process
$produksi = \App\Models\Produksi::find(8);
$proses = $produksi->proses->first();

// Reset process
$proses->update([
    'status' => 'pending',
    'waktu_mulai' => null,
    'waktu_selesai' => null,
    'durasi_menit' => null
]);

echo "Starting process with corrected time...\n";
$proses->mulaiProses();
$proses->refresh();

echo "Process started at: " . $proses->waktu_mulai->format('d/m/Y H:i:s') . "\n";
echo "Current corrected time: " . \App\Helpers\TimeHelper::formatForDisplay() . "\n";

$timeDiff = abs(\App\Helpers\TimeHelper::now()->diffInSeconds($proses->waktu_mulai));
echo "Time difference: {$timeDiff} seconds\n";

if ($timeDiff < 5) {
    echo "✅ Time correction is working!\n";
} else {
    echo "❌ Time correction needs adjustment\n";
}

echo "\n🎉 Test complete!\n";