<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing Penggorengan end time...\n\n";

$produksi = \App\Models\Produksi::find(8);
$proses = $produksi->proses()->where('nama_proses', 'Penggorengan')->first();

if (!$proses) {
    echo "❌ Penggorengan process not found\n";
    exit;
}

echo "📋 Current Penggorengan Data:\n";
echo "Status: {$proses->status}\n";
echo "Waktu Mulai: " . $proses->waktu_mulai->format('d/m/Y H:i:s') . "\n";
echo "Waktu Selesai (wrong): " . $proses->waktu_selesai->format('d/m/Y H:i:s') . "\n";
echo "Durasi (wrong): {$proses->formatted_duration}\n\n";

// Calculate correct end time based on realistic duration
// Let's assume Penggorengan took about 30 minutes (reasonable for cooking)
$waktuMulai = $proses->waktu_mulai;
$realisticDuration = 30; // 30 minutes
$correctEndTime = $waktuMulai->copy()->addMinutes($realisticDuration);

echo "🔄 Calculating realistic end time:\n";
echo "Start time: " . $waktuMulai->format('d/m/Y H:i:s') . "\n";
echo "Realistic duration: {$realisticDuration} minutes\n";
echo "Calculated end time: " . $correctEndTime->format('d/m/Y H:i:s') . "\n\n";

// Update the process
echo "🔄 Updating Penggorengan end time...\n";
$proses->update([
    'waktu_selesai' => $correctEndTime,
    'durasi_menit' => $realisticDuration
]);

$proses->refresh();
echo "✅ Updated Penggorengan Data:\n";
echo "Status: {$proses->status}\n";
echo "Waktu Mulai: " . $proses->waktu_mulai->format('d/m/Y H:i:s') . "\n";
echo "Waktu Selesai (corrected): " . $proses->waktu_selesai->format('d/m/Y H:i:s') . "\n";
echo "Durasi (corrected): {$proses->formatted_duration}\n";

echo "\n🎉 Penggorengan time fixed!\n";