<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing Pengemasan process time...\n\n";

$produksi = \App\Models\Produksi::find(8);
$proses = $produksi->proses()->where('nama_proses', 'Pengemasan')->first();

if (!$proses) {
    echo "❌ Pengemasan process not found\n";
    exit;
}

echo "📋 Current Pengemasan Data:\n";
echo "Status: {$proses->status}\n";
echo "Waktu Mulai (wrong): " . $proses->waktu_mulai->format('d/m/Y H:i:s') . "\n\n";

// Get current correct time
$correctTime = now();
echo "🕐 Correct Time:\n";
echo "Current Laravel time: " . $correctTime->format('d/m/Y H:i:s') . "\n";

// Get Windows time for verification
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
$windowsTime = !empty($output[0]) ? $output[0] : 'Unknown';
echo "Windows time: {$windowsTime}\n\n";

// Update the process with correct time
echo "🔄 Updating Pengemasan start time...\n";
$proses->update([
    'waktu_mulai' => $correctTime
]);

$proses->refresh();
echo "✅ Updated Pengemasan Data:\n";
echo "Status: {$proses->status}\n";
echo "Waktu Mulai (corrected): " . $proses->waktu_mulai->format('d/m/Y H:i:s') . "\n";

// Verify the time is reasonable
$timeDiff = $correctTime->diff($proses->waktu_mulai);
if ($timeDiff->h == 0 && $timeDiff->i < 2) {
    echo "✅ Time correction is accurate!\n";
} else {
    echo "⚠️  Time correction may need adjustment\n";
}

echo "\n🎉 Pengemasan time fixed!\n";