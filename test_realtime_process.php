<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Testing real-time process timing...\n\n";

// Get the first pending process
$produksi = \App\Models\Produksi::find(8);
$proses = $produksi->proses->where('status', 'pending')->first();

if (!$proses) {
    echo "❌ No pending process found\n";
    exit;
}

echo "📋 Testing process: {$proses->nama_proses}\n";
echo "Current status: {$proses->status}\n\n";

echo "🕐 Before starting process:\n";
echo "Server time: " . now()->format('d/m/Y H:i:s') . "\n";
echo "Timezone: " . now()->timezone->getName() . "\n\n";

echo "▶️  Starting process...\n";

// Simulate starting the process
$proses->update([
    'status' => 'sedang_dikerjakan',
    'waktu_mulai' => now()
]);

echo "✅ Process started!\n\n";

echo "🕐 After starting process:\n";
echo "Server time: " . now()->format('d/m/Y H:i:s') . "\n";
echo "Stored waktu_mulai: " . $proses->fresh()->waktu_mulai->format('d/m/Y H:i:s') . "\n";
echo "Difference: " . now()->diffInSeconds($proses->fresh()->waktu_mulai) . " seconds\n\n";

// Wait 2 seconds
echo "⏳ Waiting 2 seconds...\n";
sleep(2);

echo "⏹️  Stopping process...\n";

// Simulate stopping the process
$waktuMulai = $proses->waktu_mulai;
$waktuSelesai = now();
$durasi = $waktuMulai ? $waktuMulai->diffInMinutes($waktuSelesai) : 0;

$proses->update([
    'status' => 'selesai',
    'waktu_selesai' => $waktuSelesai,
    'durasi_menit' => $durasi
]);

echo "✅ Process completed!\n\n";

echo "📊 Final Results:\n";
$proses = $proses->fresh();
echo "Waktu Mulai: " . $proses->waktu_mulai->format('d/m/Y H:i:s') . "\n";
echo "Waktu Selesai: " . $proses->waktu_selesai->format('d/m/Y H:i:s') . "\n";
echo "Durasi: {$proses->durasi_menit} menit\n";
echo "Actual duration: " . $proses->waktu_mulai->diffInSeconds($proses->waktu_selesai) . " seconds\n\n";

// Reset process for next test
$proses->update([
    'status' => 'pending',
    'waktu_mulai' => null,
    'waktu_selesai' => null,
    'durasi_menit' => null
]);

echo "🔄 Process reset for next test\n";
echo "🎉 Test complete!\n";