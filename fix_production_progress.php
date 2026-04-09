<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing production progress calculation...\n\n";

$produksi = \App\Models\Produksi::with(['proses'])->find(8);

if (!$produksi) {
    echo "❌ Production not found\n";
    exit;
}

echo "📋 Production ID: {$produksi->id} - {$produksi->produk->nama_produk}\n\n";

echo "🔍 Current Status:\n";
echo "Total Proses: {$produksi->total_proses}\n";
echo "Proses Selesai (stored): {$produksi->proses_selesai}\n";

// Hitung proses selesai yang sebenarnya
$actualProsesSelesai = $produksi->proses()->where('status', 'selesai')->count();
echo "Proses Selesai (actual): {$actualProsesSelesai}\n";

echo "\n📊 Process Details:\n";
foreach ($produksi->proses->sortBy('urutan') as $proses) {
    $status = $proses->status;
    $duration = $proses->durasi_menit ? round($proses->durasi_menit, 2) . ' menit' : '-';
    echo "- {$proses->nama_proses}: {$status} (Durasi: {$duration})\n";
}

// Update field proses_selesai dengan nilai yang benar
$produksi->update(['proses_selesai' => $actualProsesSelesai]);

echo "\n✅ Updated Progress:\n";
$produksi->refresh();
echo "Proses Selesai: {$actualProsesSelesai}/{$produksi->total_proses}\n";
echo "Progress: {$produksi->progress_percentage}%\n";

// Test format durasi
echo "\n🕐 Testing Duration Format:\n";
foreach ($produksi->proses->where('status', 'selesai') as $proses) {
    if ($proses->durasi_menit) {
        $totalMinutes = (int) $proses->durasi_menit;
        $hours = intval($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        $formatted = $hours > 0 ? "{$hours} jam {$minutes} menit" : "{$minutes} menit";
        
        echo "- {$proses->nama_proses}: {$proses->durasi_menit} menit → {$formatted}\n";
    }
}

echo "\n🎉 Production progress fixed!\n";