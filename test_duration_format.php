<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Testing duration format...\n\n";

$produksi = \App\Models\Produksi::with(['proses'])->find(8);

echo "🕐 Testing Duration Format with Accessor:\n";
foreach ($produksi->proses->where('status', 'selesai') as $proses) {
    if ($proses->durasi_menit) {
        echo "- {$proses->nama_proses}:\n";
        echo "  Raw: {$proses->durasi_menit} menit\n";
        echo "  Formatted: {$proses->formatted_duration}\n\n";
    }
}

// Test various duration values
echo "📊 Testing Various Duration Values:\n";
$testDurations = [0.07, 0.5, 1.2, 65.5, 340.61];

foreach ($testDurations as $duration) {
    // Simulate the formatting logic
    if ($duration <= 0) {
        $formatted = '-';
    } else {
        $totalMinutes = (int) $duration;
        
        // Jika kurang dari 1 menit, tampilkan dalam detik
        if ($totalMinutes < 1 && $duration > 0) {
            $seconds = round($duration * 60);
            $formatted = "{$seconds} detik";
        } else {
            $hours = intval($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            
            if ($hours > 0) {
                $formatted = "{$hours} jam {$minutes} menit";
            } else {
                $formatted = "{$minutes} menit";
            }
        }
    }
    
    echo "- {$duration} menit → {$formatted}\n";
}

echo "\n🎉 Duration format test complete!\n";