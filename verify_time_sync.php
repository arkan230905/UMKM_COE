<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Verifying time synchronization...\n\n";

// Get Windows time
exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
$windowsTime = !empty($output[0]) ? $output[0] : 'Unknown';

// Get Laravel time
$laravelTime = now();

echo "📊 Time Comparison:\n";
echo "Windows time: {$windowsTime}\n";
echo "Laravel time: " . $laravelTime->format('Y-m-d H:i:s') . "\n";
echo "Display format: " . $laravelTime->format('d/m/Y H:i:s') . "\n\n";

// Check process times
$produksi = \App\Models\Produksi::find(8);
echo "🔍 Process Times:\n";
foreach ($produksi->proses->sortBy('urutan') as $proses) {
    echo "- {$proses->nama_proses} ({$proses->status}):\n";
    if ($proses->waktu_mulai) {
        echo "  Mulai: " . $proses->waktu_mulai->format('d/m/Y H:i:s') . "\n";
    }
    if ($proses->waktu_selesai) {
        echo "  Selesai: " . $proses->waktu_selesai->format('d/m/Y H:i:s') . "\n";
        echo "  Durasi: {$proses->formatted_duration}\n";
    }
    echo "\n";
}

// Verify synchronization
if ($windowsTime !== 'Unknown') {
    $winTimeFormatted = str_replace('.', ':', $windowsTime);
    $winDateTime = new DateTime($winTimeFormatted);
    $laravelDateTime = new DateTime($laravelTime->format('Y-m-d H:i:s'));
    
    $diff = $winDateTime->diff($laravelDateTime);
    
    echo "⏱️  Synchronization Check:\n";
    echo "Difference: {$diff->h}h {$diff->i}m {$diff->s}s\n";
    
    if ($diff->h == 0 && $diff->i < 2) {
        echo "✅ Time synchronization is PERFECT!\n";
    } else {
        echo "⚠️  Time synchronization needs adjustment\n";
    }
}

echo "\n🎉 Verification complete!\n";