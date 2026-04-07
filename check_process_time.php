<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🕐 Checking process time accuracy...\n\n";

$produksi = \App\Models\Produksi::find(8);

if (!$produksi) {
    echo "❌ Production not found\n";
    exit;
}

echo "📋 Production ID: {$produksi->id} - {$produksi->produk->nama_produk}\n\n";

echo "🔍 Current Process Times:\n";
foreach ($produksi->proses as $proses) {
    echo "Process: {$proses->nama_proses}\n";
    echo "Status: {$proses->status}\n";
    
    if ($proses->waktu_mulai) {
        echo "Waktu Mulai (Raw): {$proses->waktu_mulai}\n";
        echo "Waktu Mulai (Formatted): {$proses->waktu_mulai->format('d/m/Y H:i:s')}\n";
        echo "Waktu Mulai (Timezone): {$proses->waktu_mulai->timezone->getName()}\n";
    } else {
        echo "Waktu Mulai: Not set\n";
    }
    
    if ($proses->waktu_selesai) {
        echo "Waktu Selesai (Raw): {$proses->waktu_selesai}\n";
        echo "Waktu Selesai (Formatted): {$proses->waktu_selesai->format('d/m/Y H:i:s')}\n";
        echo "Waktu Selesai (Timezone): {$proses->waktu_selesai->timezone->getName()}\n";
    } else {
        echo "Waktu Selesai: Not set\n";
    }
    
    if ($proses->durasi_menit) {
        echo "Durasi: {$proses->durasi_menit} menit\n";
    }
    
    echo "---\n";
}

echo "\n🕐 Current Server Time:\n";
echo "now(): " . now() . "\n";
echo "now() formatted: " . now()->format('d/m/Y H:i:s') . "\n";
echo "now() timezone: " . now()->timezone->getName() . "\n";

echo "\n🌍 PHP Timezone:\n";
echo "date_default_timezone_get(): " . date_default_timezone_get() . "\n";

echo "\n⚙️ Laravel Config:\n";
echo "config('app.timezone'): " . config('app.timezone') . "\n";

echo "\n🎉 Time check complete!\n";