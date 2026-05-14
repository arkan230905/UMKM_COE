<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coa;

echo "🔍 MEMERIKSA COA DENGAN SALDO AWAL TIDAK NOL\n";
echo str_repeat("=", 50) . "\n\n";

// Cek COA bahan baku dan bahan pendukung
$coas = Coa::where('kode_akun', 'LIKE', '1104%')
    ->orWhere('kode_akun', 'LIKE', '113%')
    ->get();

echo "COA Bahan Baku dan Bahan Pendukung:\n";
echo str_repeat("-", 40) . "\n";

$totalSaldoAwal = 0;
$coaWithSaldo = [];

foreach ($coas as $coa) {
    if ($coa->saldo_awal != 0) {
        $coaWithSaldo[] = $coa;
        $totalSaldoAwal += $coa->saldo_awal;
        echo "❌ {$coa->kode_akun} ({$coa->nama_akun}): Rp " . number_format($coa->saldo_awal) . "\n";
    } else {
        echo "✅ {$coa->kode_akun} ({$coa->nama_akun}): Rp 0\n";
    }
}

echo "\nTotal saldo awal yang perlu direset: Rp " . number_format($totalSaldoAwal) . "\n";
echo "Jumlah COA yang perlu direset: " . count($coaWithSaldo) . "\n\n";

if (count($coaWithSaldo) > 0) {
    echo "🔧 MERESET SALDO AWAL KE NOL...\n";
    echo str_repeat("-", 40) . "\n";
    
    foreach ($coaWithSaldo as $coa) {
        $oldSaldo = $coa->saldo_awal;
        $coa->saldo_awal = 0;
        $coa->save();
        
        echo "✅ Reset {$coa->kode_akun}: Rp " . number_format($oldSaldo) . " → Rp 0\n";
    }
    
    echo "\n🎉 SEMUA SALDO AWAL COA BAHAN SUDAH DIRESET KE NOL!\n";
} else {
    echo "✅ SEMUA COA SUDAH MEMILIKI SALDO AWAL NOL\n";
}