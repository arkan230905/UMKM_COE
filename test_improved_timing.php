<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Testing improved timing system...\n\n";

$produksi = \App\Models\Produksi::find(8);

// Reset all processes to pending for testing
foreach ($produksi->proses as $proses) {
    $proses->update([
        'status' => 'pending',
        'waktu_mulai' => null,
        'waktu_selesai' => null,
        'durasi_menit' => null
    ]);
}

echo "🔄 All processes reset to pending\n\n";

// Test starting first process
$proses1 = $produksi->proses->first();
echo "▶️  Starting process: {$proses1->nama_proses}\n";
echo "Time before: " . now()->format('d/m/Y H:i:s') . "\n";

$proses1->mulaiProses();
$proses1->refresh();

echo "Time after: " . now()->format('d/m/Y H:i:s') . "\n";
echo "Stored time: " . $proses1->waktu_mulai->format('d/m/Y H:i:s') . "\n";
echo "Difference: " . abs(now()->diffInSeconds($proses1->waktu_mulai)) . " seconds\n\n";

// Wait 3 seconds
echo "⏳ Waiting 3 seconds...\n";
sleep(3);

// Test completing process
echo "⏹️  Completing process...\n";
echo "Time before: " . now()->format('d/m/Y H:i:s') . "\n";

$proses1->selesaikanProses();
$proses1->refresh();

echo "Time after: " . now()->format('d/m/Y H:i:s') . "\n";
echo "Stored end time: " . $proses1->waktu_selesai->format('d/m/Y H:i:s') . "\n";
echo "Duration: {$proses1->durasi_menit} minutes\n";

$actualSeconds = $proses1->waktu_mulai->diffInSeconds($proses1->waktu_selesai);
echo "Actual duration: {$actualSeconds} seconds\n\n";

echo "📊 Summary:\n";
echo "✅ Start time accuracy: " . (abs(now()->diffInSeconds($proses1->waktu_selesai)) < 2 ? "GOOD" : "NEEDS IMPROVEMENT") . "\n";
echo "✅ Duration calculation: " . ($actualSeconds >= 3 && $actualSeconds <= 4 ? "ACCURATE" : "INACCURATE") . "\n";
echo "✅ Decimal minutes: " . ($proses1->durasi_menit > 0 ? "WORKING" : "NOT WORKING") . "\n";

echo "\n🎉 Test complete!\n";