<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BopLainnya;
use App\Models\ExpensePayment;

echo "=== DEBUG: Test Update BOP Aktual ===" . PHP_EOL;

// Cek BOP Lainnya untuk akun 503
$bopLainnya = BopLainnya::where('kode_akun', '503')
    ->where('is_active', true)
    ->first();

if ($bopLainnya) {
    echo "BOP Lainnya ditemukan:" . PHP_EOL;
    echo "- ID: {$bopLainnya->id}" . PHP_EOL;
    echo "- Kode: {$bopLainnya->kode_akun}" . PHP_EOL;
    echo "- Nama: {$bopLainnya->nama_akun}" . PHP_EOL;
    echo "- Budget: " . number_format($bopLainnya->budget, 0) . PHP_EOL;
    echo "- Aktual: " . number_format($bopLainnya->aktual, 0) . PHP_EOL;
    echo "- Selisih: " . number_format($bopLainnya->budget - $bopLainnya->aktual, 0) . PHP_EOL;
} else {
    echo "BOP Lainnya untuk akun 503 tidak ditemukan!" . PHP_EOL;
}

echo PHP_EOL . "=== Cek Total Pembayaran ===" . PHP_EOL;

$totalAktual = ExpensePayment::where('coa_beban_id', '503')->sum('nominal');
echo "Total pembayaran untuk akun 503: " . number_format($totalAktual, 0) . PHP_EOL;

echo PHP_EOL . "=== Test Update Manual ===" . PHP_EOL;

if ($bopLainnya) {
    $bopLainnya->aktual = $totalAktual;
    $bopLainnya->save();
    
    echo "Update berhasil!" . PHP_EOL;
    echo "- Aktual baru: " . number_format($bopLainnya->fresh()->aktual, 0) . PHP_EOL;
    echo "- Selisih baru: " . number_format($bopLainnya->fresh()->budget - $bopLainnya->fresh()->aktual, 0) . PHP_EOL;
}
