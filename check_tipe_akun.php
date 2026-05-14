<?php
/**
 * Script untuk mengecek tipe_akun di database
 * Untuk memastikan format tipe_akun konsisten
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK TIPE AKUN DI DATABASE ===" . PHP_EOL . PHP_EOL;

// Get all unique tipe_akun
$tipeAkuns = DB::table('coas')
    ->select('tipe_akun', DB::raw('COUNT(*) as jumlah'))
    ->where('user_id', 1)
    ->groupBy('tipe_akun')
    ->orderBy('tipe_akun')
    ->get();

echo "Tipe Akun yang ada di database:" . PHP_EOL;
echo str_repeat('-', 50) . PHP_EOL;

foreach ($tipeAkuns as $tipe) {
    $normalized = strtoupper(trim($tipe->tipe_akun));
    $isDebitNormal = in_array($normalized, [
        'ASET', 'ASSET', 'AKTIVA',
        'BEBAN', 'EXPENSE', 'BIAYA'
    ]);
    
    $saldoNormal = $isDebitNormal ? 'DEBIT' : 'KREDIT';
    
    echo sprintf(
        "%-20s | Jumlah: %3d | Saldo Normal: %s",
        $tipe->tipe_akun,
        $tipe->jumlah,
        $saldoNormal
    ) . PHP_EOL;
}

echo PHP_EOL;
echo "=== SAMPLE AKUN PER TIPE ===" . PHP_EOL . PHP_EOL;

foreach ($tipeAkuns as $tipe) {
    echo "Tipe: {$tipe->tipe_akun}" . PHP_EOL;
    
    $samples = DB::table('coas')
        ->select('kode_akun', 'nama_akun', 'tipe_akun')
        ->where('user_id', 1)
        ->where('tipe_akun', $tipe->tipe_akun)
        ->limit(3)
        ->get();
    
    foreach ($samples as $sample) {
        echo "  - {$sample->kode_akun}: {$sample->nama_akun}" . PHP_EOL;
    }
    echo PHP_EOL;
}

echo "=== REKOMENDASI ===" . PHP_EOL;
echo "Jika ada tipe_akun yang tidak standar, sebaiknya diubah ke:" . PHP_EOL;
echo "- ASET (untuk aktiva/asset)" . PHP_EOL;
echo "- KEWAJIBAN (untuk liability/pasiva)" . PHP_EOL;
echo "- EKUITAS (untuk equity/modal)" . PHP_EOL;
echo "- PENDAPATAN (untuk revenue/penjualan)" . PHP_EOL;
echo "- BEBAN (untuk expense/biaya)" . PHP_EOL;
