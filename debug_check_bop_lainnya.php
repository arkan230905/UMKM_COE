<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BopLainnya;
use App\Models\Coa;

echo "=== DEBUG: Data BOP Lainnya di Database ===" . PHP_EOL;

$bopLainnya = BopLainnya::all();
echo "Total BOP Lainnya: " . $bopLainnya->count() . PHP_EOL . PHP_EOL;

foreach ($bopLainnya as $b) {
    echo "- ID: {$b->id} | Kode: {$b->kode_akun} | Nama: {$b->nama_akun} | Budget: {$b->budget} | Aktual: {$b->aktual}" . PHP_EOL;
}

echo PHP_EOL . "=== DEBUG: Akun Beban (kode 5) di COA ===" . PHP_EOL;

$akunBeban = Coa::where('kode_akun', 'LIKE', '5%')
    ->where('is_akun_header', false)
    ->orderBy('kode_akun')
    ->get();

echo "Total Akun Beban: " . $akunBeban->count() . PHP_EOL . PHP_EOL;

foreach ($akunBeban as $akun) {
    echo "- Kode: {$akun->kode_akun} | Nama: {$akun->nama_akun}" . PHP_EOL;
}

echo PHP_EOL . "=== Cek apakah ada error terbaru ===" . PHP_EOL;

$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = substr($logs, -3000); // Ambil 3000 karakter terakhir
    echo "Recent error logs (last 3000 chars):" . PHP_EOL;
    echo $recentLogs . PHP_EOL;
} else {
    echo "Tidak ada file log ditemukan." . PHP_EOL;
}
