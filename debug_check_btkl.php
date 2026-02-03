<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Btkl;

echo "=== DEBUG: Data BTKL di Database ===" . PHP_EOL;

$btkls = Btkl::orderBy('kode_proses')->get();

echo "Total BTKL: " . $btkls->count() . PHP_EOL . PHP_EOL;

foreach ($btkls as $b) {
    echo "- ID: {$b->id} | Kode: {$b->kode_proses} | Nama: {$b->nama_btkl} | Kapasitas: {$b->kapasitas_per_jam} | Jabatan ID: {$b->jabatan_id}" . PHP_EOL;
}

echo PHP_EOL . "=== Cek apakah ada error saat insert terakhir ===" . PHP_EOL;

// Cek error logs jika ada
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = substr($logs, -2000); // Ambil 2000 karakter terakhir
    echo "Recent error logs (last 2000 chars):" . PHP_EOL;
    echo $recentLogs . PHP_EOL;
} else {
    echo "Tidak ada file log ditemukan." . PHP_EOL;
}
